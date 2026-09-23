<?php

namespace App\Http\Controllers;

use App\Application\Services\AnonimizarLeadService;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Dados pessoais", description="Atendimento aos pedidos da LGPD")
 */
class DadosPessoaisController extends Controller
{
    public function __construct(
        private readonly AnonimizarLeadService $anonimizar,
    ) {}

    /**
     * @OA\Get(path="/api/leads/{id}/dados-pessoais", tags={"Dados pessoais"},
     *     security={{"sanctum":{}}},
     *     summary="Tudo que o sistema guarda sobre o titular",
     *
     *     @OA\Parameter(name="id", in="path", required=true),
     *
     *     @OA\Response(response=200, description="Dados do titular"),
     *     @OA\Response(response=404, description="Lead não encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $lead = Lead::find($id);

        if (! $lead) {
            return response()->json(['mensagem' => 'Lead não encontrado.'], 404);
        }

        $this->authorize('view', $lead);

        return response()->json($this->anonimizar->dadosPessoais($lead));
    }

    /**
     * @OA\Delete(path="/api/leads/{id}/dados-pessoais", tags={"Dados pessoais"},
     *     security={{"sanctum":{}}},
     *     summary="Remove os dados pessoais e mantém o histórico comercial",
     *
     *     @OA\Parameter(name="id", in="path", required=true),
     *
     *     @OA\Response(response=200, description="Titular anonimizado"),
     *     @OA\Response(response=403, description="Somente gerente ou administrador")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $lead = Lead::find($id);

        if (! $lead) {
            return response()->json(['mensagem' => 'Lead não encontrado.'], 404);
        }

        $this->authorize('anonimizar', $lead);

        $lead = $this->anonimizar->executar($lead, $request->user()->id);

        return response()->json([
            'mensagem' => 'Dados pessoais removidos. O histórico comercial foi mantido.',
            'anonimizado_em' => $lead->anonimizado_em?->toISOString(),
        ]);
    }
}
