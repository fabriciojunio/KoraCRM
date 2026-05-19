<?php

namespace App\Application\DTOs;

final class AtualizarLeadDTO
{
    public function __construct(
        public readonly ?string $nome = null,
        public readonly ?string $email = null,
        public readonly ?string $telefone = null,
        public readonly ?string $empresa = null,
        public readonly ?string $cargo = null,
        public readonly ?float $valorEstimado = null,
        public readonly ?string $origem = null,
        public readonly ?string $observacoes = null,
        public readonly ?array $tags = null,
        public readonly ?int $responsavelId = null,
    ) {}

    public static function fromArray(array $dados): self
    {
        return new self(
            nome: $dados['nome'] ?? null,
            email: $dados['email'] ?? null,
            telefone: $dados['telefone'] ?? null,
            empresa: $dados['empresa'] ?? null,
            cargo: $dados['cargo'] ?? null,
            valorEstimado: isset($dados['valor_estimado'])
                ? (float) $dados['valor_estimado']
                : null,
            origem: $dados['origem'] ?? null,
            observacoes: $dados['observacoes'] ?? null,
            tags: $dados['tags'] ?? null,
            responsavelId: $dados['responsavel_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'nome' => $this->nome,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'empresa' => $this->empresa,
            'cargo' => $this->cargo,
            'valor_estimado' => $this->valorEstimado,
            'origem' => $this->origem,
            'observacoes' => $this->observacoes,
            'tags' => $this->tags,
            'responsavel_id' => $this->responsavelId,
        ], fn ($valor) => $valor !== null);
    }
}
