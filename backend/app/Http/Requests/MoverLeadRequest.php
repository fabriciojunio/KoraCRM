<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoverLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estagio' => ['required', Rule::in(Lead::ESTAGIOS)],
        ];
    }

    public function messages(): array
    {
        return [
            'estagio.required' => 'O estágio de destino é obrigatório.',
            'estagio.in' => 'Estágio inválido. Use: ' . implode(', ', Lead::ESTAGIOS),
        ];
    }
}
