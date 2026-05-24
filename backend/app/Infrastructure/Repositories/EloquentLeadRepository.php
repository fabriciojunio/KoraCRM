<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Lead\LeadRepositoryInterface;
use App\Models\Lead;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentLeadRepository implements LeadRepositoryInterface
{
    public function buscarPorId(int $id): ?Lead
    {
        return Lead::with(['responsavel:id,nome,email', 'criador:id,nome'])
                   ->find($id);
    }

    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator
    {
        $query = Lead::with(['responsavel:id,nome', 'criador:id,nome'])
                     ->orderByDesc('created_at');

        if (! empty($filtros['estagio'])) {
            $query->where('estagio', $filtros['estagio']);
        }

        if (! empty($filtros['responsavel_id'])) {
            $query->where('responsavel_id', $filtros['responsavel_id']);
        }

        if (! empty($filtros['busca'])) {
            $termo = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $filtros['busca']);
            $busca = "%{$termo}%";
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', $busca)
                  ->orWhere('email', 'like', $busca)
                  ->orWhere('empresa', 'like', $busca);
            });
        }

        if (! empty($filtros['origem'])) {
            $query->where('origem', $filtros['origem']);
        }

        return $query->paginate($porPagina);
    }

    public function criar(array $dados): Lead
    {
        return Lead::create($dados);
    }

    public function atualizar(Lead $lead, array $dados): Lead
    {
        $lead->update($dados);

        return $lead->fresh();
    }

    public function excluir(Lead $lead): void
    {
        $lead->delete();
    }

    public function buscarPorEstagio(string $estagio): array
    {
        return Lead::with(['responsavel:id,nome', 'tarefas'])
                   ->where('estagio', $estagio)
                   ->orderByDesc('created_at')
                   ->get()
                   ->toArray();
    }

    public function contagemPorEstagio(): array
    {
        $resultado = Lead::selectRaw('estagio, COUNT(*) as total')
                         ->groupBy('estagio')
                         ->pluck('total', 'estagio')
                         ->toArray();

        $completo = [];
        foreach (Lead::ESTAGIOS as $estagio) {
            $completo[$estagio] = $resultado[$estagio] ?? 0;
        }

        return $completo;
    }

    public function valorTotalPorEstagio(): array
    {
        $resultado = Lead::selectRaw('estagio, SUM(valor_estimado) as total')
                         ->whereNotNull('valor_estimado')
                         ->groupBy('estagio')
                         ->pluck('total', 'estagio')
                         ->toArray();

        $completo = [];
        foreach (Lead::ESTAGIOS as $estagio) {
            $completo[$estagio] = (float) ($resultado[$estagio] ?? 0);
        }

        return $completo;
    }

    public function leadsPorResponsavel(int $usuarioId): array
    {
        return Lead::where('responsavel_id', $usuarioId)
                   ->whereNotIn('estagio', Lead::ESTAGIOS_FECHADOS)
                   ->get()
                   ->toArray();
    }
}
