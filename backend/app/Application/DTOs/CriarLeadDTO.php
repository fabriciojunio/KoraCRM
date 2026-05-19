<?php

namespace App\Application\DTOs;

final class CriarLeadDTO
{
    public function __construct(
        public readonly string $nome,
        public readonly int $criadoPor,
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

    public static function fromArray(array $dados, int $criadoPor): self
    {
        return new self(
            nome: $dados['nome'],
            criadoPor: $criadoPor,
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
            responsavelId: $dados['responsavel_id'] ?? $criadoPor,
        );
    }
}
