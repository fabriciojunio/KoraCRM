<?php

namespace App\Http\Controllers;

use App\Application\DTOs\AtualizarLeadDTO;
use App\Application\DTOs\CriarLeadDTO;
use App\Application\Services\CriarLeadService;
use App\Application\Services\MoverLeadService;
use App\Application\Services\RegistrarHistoricoService;
use App\Domain\Lead\LeadRepositoryInterface;
use App\Http\Requests\CriarLeadRequest;
use App\Http\Requests\AtualizarLeadRequest;
use App\Http\Requests\MoverLeadRequest;
use App\Http\Resources\LeadResource;
use App\Http\Resources\LeadColecaoResource;
use App\Models\Arquivo;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Leads", description="Gestão de leads")
 */
class LeadController extends Controller
{
    public function __construct(
        private readonly LeadRepositoryInterface $repositorio,
        private readonly CriarLeadService $criarLead,
        private readonly MoverLeadService $moverLead,
        private readonly RegistrarHistoricoService $historico,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/leads",
     *     tags={"Leads"},
     *     summary="Lista leads com filtros e paginação",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="estagio", in="query", schema={"type": "string"}),
     *     @OA\Parameter(name="busca", in="query", schema={"type": "string"}),
     *     @OA\Parameter(name="responsavel_id", in="query", schema={"type": "integer"}),
     *     @OA\Response(response=200, description="Lista de leads")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filtros = $request->only([
            'estagio', 'busca', 'responsavel_id', 'origem',
        ]);

        $leads = $this->repositorio->listar(
            filtros: $filtros,
            porPagina: (int) $request->get('por_pagina', 15),
        );

        // ->response() preserva o envelope de paginação (data/links/meta)
        return LeadColecaoResource::collection($leads)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/leads",
     *     tags={"Leads"},
     *     summary="Cria um novo lead",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=201, description="Lead criado com sucesso")
     * )
     */
    public function store(CriarLeadRequest $request): JsonResponse
    {
        $dto = CriarLeadDTO::fromArray(
            dados: $request->validated(),
            criadoPor: $request->user()->id,
        );

        $lead = $this->criarLead->executar($dto);

        return response()->json(new LeadResource($lead), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/leads/{id}",
     *     tags={"Leads"},
     *     summary="Detalhes de um lead",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Dados do lead"),
     *     @OA\Response(response=404, description="Lead não encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $lead = $this->repositorio->buscarPorId($id);

        if (! $lead) {
            return response()->json(['mensagem' => 'Lead não encontrado.'], 404);
        }

        return response()->json(new LeadResource($lead));
    }

    /**
     * @OA\Put(
     *     path="/api/leads/{id}",
     *     tags={"Leads"},
     *     summary="Atualiza um lead",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Lead atualizado")
     * )
     */
    public function update(AtualizarLeadRequest $request, int $id): JsonResponse
    {
        $lead = $this->repositorio->buscarPorId($id);

        if (! $lead) {
            return response()->json(['mensagem' => 'Lead não encontrado.'], 404);
        }

        $dadosAntigos = $lead->toArray();
        $dto = AtualizarLeadDTO::fromArray($request->validated());
        $lead = $this->repositorio->atualizar($lead, $dto->toArray());

        $this->historico->registrar(
            leadId: $lead->id,
            usuarioId: $request->user()->id,
            tipo: 'atualizacao',
            descricao: 'Dados do lead atualizados',
            dadosAnteriores: $dadosAntigos,
            dadosNovos: $lead->toArray(),
        );

        return response()->json(new LeadResource($lead));
    }

    /**
     * @OA\Delete(
     *     path="/api/leads/{id}",
     *     tags={"Leads"},
     *     summary="Exclui um lead (soft delete)",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=204, description="Lead excluído")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $lead = $this->repositorio->buscarPorId($id);

        if (! $lead) {
            return response()->json(['mensagem' => 'Lead não encontrado.'], 404);
        }

        $this->repositorio->excluir($lead);

        return response()->json(null, 204);
    }

    /**
     * @OA\Patch(
     *     path="/api/leads/{id}/estagio",
     *     tags={"Leads"},
     *     summary="Move lead para novo estágio no pipeline",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Lead movido com sucesso"),
     *     @OA\Response(response=422, description="Transição inválida")
     * )
     */
    public function moverEstagio(MoverLeadRequest $request, int $id): JsonResponse
    {
        try {
            $lead = $this->moverLead->executar(
                leadId: $id,
                novoEstagio: $request->validated('estagio'),
                usuarioId: $request->user()->id,
            );

            return response()->json(new LeadResource($lead));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['mensagem' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['mensagem' => $e->getMessage()], 409);
        }
    }

    /**
     * Histórico de ações do lead.
     */
    public function historico(int $id): JsonResponse
    {
        $lead = $this->repositorio->buscarPorId($id);

        if (! $lead) {
            return response()->json(['mensagem' => 'Lead não encontrado.'], 404);
        }

        return response()->json(
            $lead->historico()->with('usuario:id,nome')->get()
        );
    }

    /**
     * @OA\Get(
     *     path="/api/pipeline",
     *     tags={"Leads"},
     *     summary="Leads agrupados por estágio para o Kanban",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Leads agrupados por estágio")
     * )
     */
    public function pipeline(): JsonResponse
    {
        $grupos = [];

        $leads = Lead::with([
            'responsavel:id,nome,email',
            'criador:id,nome',
            'tarefas:id,lead_id,concluida',
        ])->orderByDesc('created_at')->get();

        foreach (Lead::ESTAGIOS as $estagio) {
            $grupos[$estagio] = LeadResource::collection(
                $leads->where('estagio', $estagio)->values()
            );
        }

        return response()->json($grupos);
    }

    /**
     * @OA\Post(
     *     path="/api/leads/{id}/arquivos",
     *     tags={"Leads"},
     *     summary="Anexa um arquivo ao lead",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=201, description="Arquivo enviado com sucesso"),
     *     @OA\Response(response=404, description="Lead não encontrado"),
     *     @OA\Response(response=422, description="Arquivo inválido")
     * )
     */
    public function uploadArquivo(Request $request, int $lead): JsonResponse
    {
        $modelo = $this->repositorio->buscarPorId($lead);

        if (! $modelo) {
            return response()->json(['mensagem' => 'Lead não encontrado.'], 404);
        }

        $request->validate([
            'arquivo' => ['required', 'file', 'max:10240'],
        ], [
            'arquivo.required' => 'Selecione um arquivo para enviar.',
            'arquivo.file'     => 'O conteúdo enviado não é um arquivo válido.',
            'arquivo.max'      => 'O arquivo excede o tamanho máximo de 10MB.',
        ]);

        $upload = $request->file('arquivo');

        if (! in_array($upload->getMimeType(), Arquivo::TIPOS_PERMITIDOS, true)) {
            return response()->json([
                'mensagem' => 'Tipo de arquivo não permitido.',
            ], 422);
        }

        $disco = config('filesystems.default') === 's3' ? 's3' : 'local';
        $caminho = $upload->store("leads/{$modelo->id}", $disco);

        $arquivo = Arquivo::create([
            'lead_id'       => $modelo->id,
            'nome_original' => $upload->getClientOriginalName(),
            'caminho'       => $caminho,
            'disco'         => $disco,
            'tamanho'       => $upload->getSize(),
            'mime_type'     => $upload->getMimeType(),
            'enviado_por'   => $request->user()->id,
        ]);

        $this->historico->registrar(
            leadId: $modelo->id,
            usuarioId: $request->user()->id,
            tipo: 'arquivo_enviado',
            descricao: "Arquivo '{$arquivo->nome_original}' anexado ao lead",
        );

        return response()->json([
            'id'                => $arquivo->id,
            'nome_original'     => $arquivo->nome_original,
            'tamanho'           => $arquivo->tamanho,
            'tamanho_formatado' => $arquivo->tamanhoFormatado(),
            'mime_type'         => $arquivo->mime_type,
            'url'               => $arquivo->urlAcesso(),
            'enviado_em'        => $arquivo->created_at->toISOString(),
        ], 201);
    }
}
