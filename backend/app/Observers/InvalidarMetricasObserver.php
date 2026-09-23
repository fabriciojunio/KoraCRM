<?php

namespace App\Observers;

use App\Application\Services\DashboardService;
use Illuminate\Support\Facades\Cache;

/**
 * As métricas do painel ficam cinco minutos em cache. Sem isto, mover um lead
 * não aparecia no painel até o cache vencer, e o vendedor jurava que o sistema
 * tinha perdido a alteração.
 */
class InvalidarMetricasObserver
{
    public function saved(): void
    {
        Cache::forget(DashboardService::CHAVE_METRICAS);
    }

    public function deleted(): void
    {
        Cache::forget(DashboardService::CHAVE_METRICAS);
    }

    public function restored(): void
    {
        Cache::forget(DashboardService::CHAVE_METRICAS);
    }
}
