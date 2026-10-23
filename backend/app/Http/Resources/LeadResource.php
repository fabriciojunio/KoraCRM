<?php

namespace App\Http\Resources;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LogicException;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lead = $this->resource;

        if (! $lead instanceof Lead) {
            throw new LogicException('LeadResource aceita somente instâncias de Lead.');
        }

        return [
            'id' => $lead->id,
            'nome' => $lead->nome,
            'email' => $lead->email,
            'telefone' => $lead->telefone,
            'empresa' => $lead->empresa,
            'cargo' => $lead->cargo,
            'estagio' => $lead->estagio,
            'valor_estimado' => $lead->valor_estimado
                ? (float) $lead->valor_estimado
                : null,
            'origem' => $lead->origem,
            'observacoes' => $lead->observacoes,
            'tags' => $lead->tags ?? [],
            'esta_fechado' => $lead->estaFechado(),
            'responsavel' => $this->whenLoaded('responsavel', fn () => [
                'id' => $lead->responsavel?->id,
                'nome' => $lead->responsavel?->nome,
                'email' => $lead->responsavel?->email,
            ]),
            'criador' => $this->whenLoaded('criador', fn () => [
                'id' => $lead->criador?->id,
                'nome' => $lead->criador?->nome,
            ]),
            'tarefas_total' => $this->whenLoaded(
                'tarefas',
                fn () => $lead->tarefas->count()
            ),
            'tarefas_pendentes' => $this->whenLoaded(
                'tarefas',
                fn () => $lead->tarefas->where('concluida', false)->count()
            ),
            'data_fechamento' => $lead->data_fechamento?->toISOString(),
            'criado_em' => $lead->created_at->toISOString(),
            'atualizado_em' => $lead->updated_at->toISOString(),
        ];
    }
}
