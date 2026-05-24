<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CriarLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'empresa' => ['nullable', 'string', 'max:150'],
            'cargo' => ['nullable', 'string', 'max:100'],
            'valor_estimado' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'origem' => [
                'nullable',
                Rule::in(['site', 'indicacao', 'linkedin', 'evento', 'outros']),
            ],
            'observacoes' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:30'],
            'responsavel_id' => ['nullable', 'integer', 'exists:usuarios,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do lead é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'valor_estimado.numeric' => 'O valor deve ser um número.',
            'valor_estimado.min' => 'O valor não pode ser negativo.',
            'responsavel_id.exists' => 'Usuário responsável não encontrado.',
        ];
    }
}
