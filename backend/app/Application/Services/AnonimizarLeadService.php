<?php

namespace App\Application\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Atende ao pedido de exclusão de dados pessoais da LGPD sem apagar o
 * histórico comercial.
 *
 * Apagar a linha inteira levaria junto o valor fechado e a contagem do funil,
 * que são dado da empresa, não do titular. Então o que sai é só o que
 * identifica a pessoa: nome, contato, cargo e o texto livre, onde é comum
 * alguém ter anotado um telefone.
 */
class AnonimizarLeadService
{
    public function __construct(
        private readonly RegistrarHistoricoService $historico,
    ) {}

    public function executar(Lead $lead, int $solicitadoPor): Lead
    {
        if ($lead->anonimizado_em !== null) {
            return $lead;
        }

        return DB::transaction(function () use ($lead, $solicitadoPor) {
            $lead->forceFill([
                'nome' => 'Titular anonimizado',
                'email' => null,
                'telefone' => null,
                'cargo' => null,
                'observacoes' => null,
                'tags' => [],
                'anonimizado_em' => now(),
            ])->save();

            foreach ($lead->arquivos()->get() as $arquivo) {
                Storage::disk($arquivo->disco)->delete($arquivo->caminho);
                $arquivo->delete();
            }

            $this->historico->registrar(
                leadId: $lead->id,
                usuarioId: $solicitadoPor,
                tipo: 'anonimizacao',
                descricao: 'Dados pessoais removidos a pedido do titular (LGPD)',
            );

            return $lead->fresh();
        });
    }

    /**
     * O que a empresa guarda sobre o titular, para atender ao pedido de acesso.
     *
     * @return array<string, mixed>
     */
    public function dadosPessoais(Lead $lead): array
    {
        return [
            'lead' => $lead->only([
                'id', 'nome', 'email', 'telefone', 'empresa', 'cargo',
                'origem', 'observacoes', 'tags', 'anonimizado_em',
            ]),
            'aberto_em' => $lead->created_at?->toISOString(),
            'historico' => $lead->historico()->get()->map(fn ($registro) => [
                'tipo' => $registro->tipo,
                'descricao' => $registro->descricao,
                'data' => $registro->created_at->toISOString(),
            ]),
            'arquivos' => $lead->arquivos()->get()->map(fn ($arquivo) => [
                'nome' => $arquivo->nome_original,
                'enviado_em' => $arquivo->created_at->toISOString(),
            ]),
        ];
    }
}
