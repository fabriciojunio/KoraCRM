<?php

namespace App\Application\Services;

use App\Application\DTOs\CriarLeadDTO;
use App\Domain\Lead\LeadRepositoryInterface;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class CriarLeadService
{
    public function __construct(
        private readonly LeadRepositoryInterface $repositorio,
        private readonly RegistrarHistoricoService $historico,
    ) {}

    public function executar(CriarLeadDTO $dto): Lead
    {
        return DB::transaction(function () use ($dto) {
            $lead = $this->repositorio->criar([
                'nome' => $dto->nome,
                'email' => $dto->email,
                'telefone' => $dto->telefone,
                'empresa' => $dto->empresa,
                'cargo' => $dto->cargo,
                'estagio' => 'novo',
                'valor_estimado' => $dto->valorEstimado,
                'origem' => $dto->origem,
                'observacoes' => $dto->observacoes,
                'tags' => $dto->tags,
                'responsavel_id' => $dto->responsavelId,
                'criado_por' => $dto->criadoPor,
            ]);

            $this->historico->registrar(
                $lead->id,
                $dto->criadoPor,
                'criacao',
                "Lead {$lead->nome} criado no estágio 'novo'",
            );

            return $lead;
        });
    }
}
