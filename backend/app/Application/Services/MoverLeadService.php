<?php

namespace App\Application\Services;

use App\Domain\Lead\LeadRepositoryInterface;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class MoverLeadService
{
    public function __construct(
        private readonly LeadRepositoryInterface $repositorio,
        private readonly RegistrarHistoricoService $historico,
    ) {}

    public function executar(int $leadId, string $novoEstagio, int $usuarioId): Lead
    {
        $lead = $this->repositorio->buscarPorId($leadId);

        if (! $lead) {
            throw new RuntimeException("Lead {$leadId} não encontrado.");
        }

        $this->validarTransicao($lead, $novoEstagio);

        return DB::transaction(function () use ($lead, $novoEstagio, $usuarioId) {
            $estagioAnterior = $lead->estagio;

            $lead = $this->repositorio->atualizar($lead, [
                'estagio' => $novoEstagio,
                'data_fechamento' => in_array($novoEstagio, Lead::ESTAGIOS_FECHADOS)
                    ? now()
                    : null,
            ]);

            $this->historico->registrar(
                $lead->id,
                $usuarioId,
                'mudanca_estagio',
                "Movido de '{$estagioAnterior}' para '{$novoEstagio}'",
                ['estagio' => $estagioAnterior],
                ['estagio' => $novoEstagio],
            );

            return $lead;
        });
    }

    private function validarTransicao(Lead $lead, string $novoEstagio): void
    {
        if (! in_array($novoEstagio, Lead::ESTAGIOS)) {
            throw new InvalidArgumentException(
                "Estágio '{$novoEstagio}' inválido. "
                . 'Estágios válidos: ' . implode(', ', Lead::ESTAGIOS)
            );
        }

        if ($lead->estaFechado()) {
            throw new RuntimeException(
                "Lead já está fechado no estágio '{$lead->estagio}' "
                . 'e não pode ser movido.'
            );
        }

        if ($lead->estagio === $novoEstagio) {
            throw new InvalidArgumentException(
                "O lead já está no estágio '{$novoEstagio}'."
            );
        }
    }
}
