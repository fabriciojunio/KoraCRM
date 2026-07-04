<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Tarefa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Tarefas", description="Gestão de tarefas vinculadas a leads")
 */
class TarefaController extends Controller
{
    /**
     * @OA\Get(path="/api/tarefas", tags={"Tarefas"}, security={{"sanctum":{}}},
     *     summary="Lista tarefas do usuário",
     *     @OA\Response(response=200, description="Lista de tarefas")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tarefa::with(['lead:id,nome', 'responsavel:id,nome'])
            ->orderBy('prazo');

        // Gerentes veem todas; vendedores só as suas
        if ($request->user()->isVendedor()) {
            $query->where('responsavel_id', $request->user()->id);
        }

        $tarefas = $query->get()->map(fn ($t) => [
            'id'           => $t->id,
            'titulo'       => $t->titulo,
            'descricao'    => $t->descricao,
            'prazo'        => $t->prazo?->toISOString(),
            'concluida'    => $t->concluida,
            'concluida_em' => $t->concluida_em?->toISOString(),
            'prioridade'   => $t->prioridade,
            'atrasada'     => $t->estaAtrasada(),
            'lead'         => $t->lead ? ['id' => $t->lead->id, 'nome' => $t->lead->nome] : null,
            'responsavel'  => $t->responsavel ? ['id' => $t->responsavel->id, 'nome' => $t->responsavel->nome] : null,
            'criado_em'    => $t->created_at->toISOString(),
        ]);

        return response()->json($tarefas);
    }

    /**
     * @OA\Get(path="/api/tarefas/{id}", tags={"Tarefas"}, security={{"sanctum":{}}},
     *     summary="Detalhes de uma tarefa",
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Dados da tarefa"),
     *     @OA\Response(response=404, description="Tarefa não encontrada")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $tarefa = Tarefa::with(['lead:id,nome', 'responsavel:id,nome'])->find($id);

        if (! $tarefa) {
            return response()->json(['mensagem' => 'Tarefa não encontrada.'], 404);
        }

        $this->authorize('view', $tarefa);

        return response()->json($tarefa);
    }

    /**
     * @OA\Post(path="/api/tarefas", tags={"Tarefas"}, security={{"sanctum":{}}},
     *     summary="Cria nova tarefa",
     *     @OA\Response(response=201, description="Tarefa criada")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'titulo'         => ['required', 'string', 'max:200'],
            'descricao'      => ['nullable', 'string', 'max:2000'],
            'prazo'          => ['nullable', 'date', 'after:now'],
            'prioridade'     => ['nullable', 'in:baixa,media,alta'],
            'lead_id'        => ['required', 'integer', 'exists:leads,id'],
            'responsavel_id' => ['nullable', 'integer', 'exists:usuarios,id'],
        ], [
            'titulo.required'   => 'O título da tarefa é obrigatório.',
            'prazo.after'       => 'O prazo deve ser uma data futura.',
            'lead_id.required'  => 'A tarefa deve estar vinculada a um lead.',
            'lead_id.exists'    => 'Lead não encontrado.',
        ]);

        // Impede vincular tarefa a lead que o usuário não pode acessar (IDOR).
        $lead = Lead::findOrFail($dados['lead_id']);
        $this->authorize('view', $lead);

        // Vendedores só podem atribuir tarefas a si mesmos.
        $responsavelId = $dados['responsavel_id'] ?? $request->user()->id;
        if ($request->user()->isVendedor()) {
            $responsavelId = $request->user()->id;
        }

        $tarefa = Tarefa::create([
            'titulo'         => $dados['titulo'],
            'descricao'      => $dados['descricao'] ?? null,
            'prazo'          => $dados['prazo'] ?? null,
            'prioridade'     => $dados['prioridade'] ?? 'media',
            'lead_id'        => $dados['lead_id'],
            'responsavel_id' => $responsavelId,
            'concluida'      => false,
        ]);

        return response()->json($tarefa->load(['lead:id,nome', 'responsavel:id,nome']), 201);
    }

    /**
     * @OA\Put(path="/api/tarefas/{id}", tags={"Tarefas"}, security={{"sanctum":{}}},
     *     summary="Atualiza tarefa",
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Tarefa atualizada")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tarefa = Tarefa::find($id);

        if (! $tarefa) {
            return response()->json(['mensagem' => 'Tarefa não encontrada.'], 404);
        }

        $this->authorize('update', $tarefa);

        $dados = $request->validate([
            'titulo'         => ['sometimes', 'string', 'max:200'],
            'descricao'      => ['nullable', 'string', 'max:2000'],
            'prazo'          => ['nullable', 'date'],
            'prioridade'     => ['nullable', 'in:baixa,media,alta'],
            'responsavel_id' => ['nullable', 'integer', 'exists:usuarios,id'],
        ]);

        $tarefa->update($dados);

        return response()->json($tarefa->fresh(['lead:id,nome', 'responsavel:id,nome']));
    }

    /**
     * @OA\Delete(path="/api/tarefas/{id}", tags={"Tarefas"}, security={{"sanctum":{}}},
     *     summary="Exclui tarefa",
     *     @OA\Response(response=204, description="Tarefa excluída")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        $tarefa = Tarefa::find($id);

        if (! $tarefa) {
            return response()->json(['mensagem' => 'Tarefa não encontrada.'], 404);
        }

        $this->authorize('delete', $tarefa);

        $tarefa->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Patch(path="/api/tarefas/{id}/concluir", tags={"Tarefas"}, security={{"sanctum":{}}},
     *     summary="Marca tarefa como concluída",
     *     @OA\Response(response=200, description="Tarefa concluída")
     * )
     */
    public function concluir(int $id): JsonResponse
    {
        $tarefa = Tarefa::find($id);

        if (! $tarefa) {
            return response()->json(['mensagem' => 'Tarefa não encontrada.'], 404);
        }

        $this->authorize('concluir', $tarefa);

        if ($tarefa->concluida) {
            return response()->json(['mensagem' => 'Tarefa já está concluída.'], 409);
        }

        $tarefa->concluir();

        return response()->json($tarefa->fresh());
    }
}
