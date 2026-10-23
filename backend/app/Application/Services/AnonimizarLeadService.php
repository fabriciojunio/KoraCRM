<?php

namespace App\Application\Services;

use App\Models\Lead;
use App\Models\Tarefa;
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
        return DB::transaction(function () use ($lead, $solicitadoPor) {
            $lead = Lead::query()->lockForUpdate()->findOrFail($lead->id);
            $primeiroPedido = $lead->anonimizado_em === null;

            $lead->forceFill([
                'nome' => 'Titular anonimizado',
                'email' => null,
                'telefone' => null,
                'empresa' => null,
                'cargo' => null,
                'observacoes' => null,
                'tags' => [],
                'anonimizado_em' => $lead->anonimizado_em ?? now(),
            ])->save();

            // Um pedido repetido também limpa registros de versões anteriores.
            foreach ($lead->historico()->get() as $registro) {
                $registro->forceFill([
                    'descricao' => 'Registro comercial preservado após anonimização',
                    'dados_anteriores' => $this->dadosComerciais($registro->dados_anteriores),
                    'dados_novos' => $this->dadosComerciais($registro->dados_novos),
                ])->save();
            }

            foreach (Tarefa::withTrashed()->where('lead_id', $lead->id)->get() as $tarefa) {
                $tarefa->forceFill([
                    'titulo' => 'Tarefa de lead anonimizado',
                    'descricao' => null,
                ])->save();
            }

            foreach ($lead->arquivos()->get() as $arquivo) {
                Storage::disk($arquivo->disco)->delete($arquivo->caminho);
                $arquivo->delete();
            }

            if ($primeiroPedido) {
                $this->historico->registrar(
                    leadId: $lead->id,
                    usuarioId: $solicitadoPor,
                    tipo: 'anonimizacao',
                    descricao: 'Dados pessoais removidos a pedido do titular (LGPD)',
                );
            }

            return $lead->fresh();
        });
    }

    private function dadosComerciais(?array $dados): ?array
    {
        if ($dados === null) {
            return null;
        }

        return array_intersect_key($dados, array_flip(['estagio', 'valor_estimado', 'data_fechamento']));
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
            'aberto_em' => $lead->created_at->toISOString(),
            'historico' => $lead->historico()->get()->map(fn ($registro) => [
                'tipo' => $registro->tipo,
                'descricao' => $registro->descricao,
                'data' => $registro->created_at->toISOString(),
            ]),
            'tarefas' => $lead->tarefas()->get()->map(fn ($tarefa) => [
                'titulo' => $tarefa->titulo,
                'descricao' => $tarefa->descricao,
                'data' => $tarefa->created_at->toISOString(),
            ]),
            'arquivos' => $lead->arquivos()->get()->map(fn ($arquivo) => [
                'nome' => $arquivo->nome_original,
                'enviado_em' => $arquivo->created_at->toISOString(),
            ]),
        ];
    }
}
