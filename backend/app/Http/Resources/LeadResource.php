<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'empresa' => $this->empresa,
            'cargo' => $this->cargo,
            'estagio' => $this->estagio,
            'valor_estimado' => $this->valor_estimado
                ? (float) $this->valor_estimado
                : null,
            'origem' => $this->origem,
            'observacoes' => $this->observacoes,
            'tags' => $this->tags ?? [],
            'esta_fechado' => $this->estaFechado(),
            'responsavel' => $this->whenLoaded('responsavel', fn () => [
                'id' => $this->responsavel->id,
                'nome' => $this->responsavel->nome,
                'email' => $this->responsavel->email,
            ]),
            'criador' => $this->whenLoaded('criador', fn () => [
                'id' => $this->criador->id,
                'nome' => $this->criador->nome,
            ]),
            'tarefas_total' => $this->whenLoaded(
                'tarefas',
                fn () => $this->tarefas->count()
            ),
            'tarefas_pendentes' => $this->whenLoaded(
                'tarefas',
                fn () => $this->tarefas->where('concluida', false)->count()
            ),
            'data_fechamento' => $this->data_fechamento?->toISOString(),
            'criado_em' => $this->created_at->toISOString(),
            'atualizado_em' => $this->updated_at->toISOString(),
        ];
    }
}
