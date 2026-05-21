<?php

namespace App\Application\Services;

use App\Domain\Lead\LeadRepositoryInterface;
use App\Models\Lead;
use App\Models\Tarefa;
use App\Models\HistoricoLead;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(
        private readonly LeadRepositoryInterface $repositorio,
    ) {}

    public function metricas(): array
    {
        return Cache::remember('dashboard.metricas', 300, function () {
            $contagemPorEstagio = $this->repositorio->contagemPorEstagio();
            $valorPorEstagio = $this->repositorio->valorTotalPorEstagio();

            $totalLeads = array_sum($contagemPorEstagio);
            $leadsGanhos = $contagemPorEstagio['ganho'] ?? 0;
            $leadsPerdidos = $contagemPorEstagio['perdido'] ?? 0;
            $leadsAtivos = $totalLeads - $leadsGanhos - $leadsPerdidos;

            $taxaConversao = ($totalLeads > 0)
                ? round(($leadsGanhos / $totalLeads) * 100, 1)
                : 0.0;

            return [
                'total_leads' => $totalLeads,
                'leads_ativos' => $leadsAtivos,
                'leads_ganhos' => $leadsGanhos,
                'leads_perdidos' => $leadsPerdidos,
                'taxa_conversao' => $taxaConversao,
                'valor_total_pipeline' => array_sum($valorPorEstagio),
                'valor_ganho' => $valorPorEstagio['ganho'] ?? 0,
                'contagem_por_estagio' => $contagemPorEstagio,
                'valor_por_estagio' => $valorPorEstagio,
                'tarefas_pendentes' => Tarefa::where('concluida', false)->count(),
                'tarefas_atrasadas' => Tarefa::where('concluida', false)
                    ->whereNotNull('prazo')
                    ->where('prazo', '<', now())
                    ->count(),
            ];
        });
    }

    public function atividadesRecentes(int $limite = 10): array
    {
        return HistoricoLead::with(['lead:id,nome', 'usuario:id,nome'])
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get()
            ->map(fn ($h) => [
                'id' => $h->id,
                'tipo' => $h->tipo,
                'descricao' => $h->descricao,
                'lead' => $h->lead?->nome,
                'usuario' => $h->usuario?->nome,
                'data' => $h->created_at->toISOString(),
            ])
            ->toArray();
    }

    public function funil(): array
    {
        $contagem = $this->repositorio->contagemPorEstagio();

        return array_map(fn ($estagio) => [
            'estagio' => $estagio,
            'quantidade' => $contagem[$estagio] ?? 0,
        ], Lead::ESTAGIOS);
    }
}
