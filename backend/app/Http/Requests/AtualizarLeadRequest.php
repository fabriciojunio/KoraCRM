<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AtualizarLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nome' => ['sometimes', 'string', 'max:150'],
            'email' => ['nullable', 'email:filter', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'empresa' => ['nullable', 'string', 'max:150'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'valor_estimado' => ['nullable', 'numeric', 'min:0'],
            'origem' => [
                'nullable',
                Rule::in(['site', 'indicacao', 'linkedin', 'evento', 'outros']),
            ],
            'observacoes' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:30'],
            'responsavel_id' => ['nullable', 'integer', 'exists:usuarios,id'],
        ];
    }
}
