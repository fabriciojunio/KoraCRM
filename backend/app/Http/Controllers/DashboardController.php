<?php

namespace App\Http\Controllers;

use App\Application\Services\DashboardService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Dashboard", description="Métricas e dados do painel principal")
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    /**
     * @OA\Get(path="/api/dashboard/metricas", tags={"Dashboard"}, security={{"sanctum":{}}},
     *     summary="KPIs e métricas gerais do CRM",
     *     @OA\Response(response=200, description="Métricas do dashboard")
     * )
     */
    public function metricas(): JsonResponse
    {
        return response()->json($this->dashboard->metricas());
    }

    /**
     * @OA\Get(path="/api/dashboard/atividades", tags={"Dashboard"}, security={{"sanctum":{}}},
     *     summary="Atividades recentes do sistema",
     *     @OA\Response(response=200, description="Lista de atividades recentes")
     * )
     */
    public function atividades(): JsonResponse
    {
        return response()->json($this->dashboard->atividadesRecentes());
    }

    /**
     * @OA\Get(path="/api/dashboard/funil", tags={"Dashboard"}, security={{"sanctum":{}}},
     *     summary="Dados do funil de conversão para gráfico",
     *     @OA\Response(response=200, description="Dados do funil")
     * )
     */
    public function funil(): JsonResponse
    {
        return response()->json($this->dashboard->funil());
    }
}
