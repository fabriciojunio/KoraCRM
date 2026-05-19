<?php

namespace App\Domain\Lead;

use App\Models\Lead;
use Illuminate\Pagination\LengthAwarePaginator;

interface LeadRepositoryInterface
{
    public function buscarPorId(int $id): ?Lead;

    public function listar(array $filtros = [], int $porPagina = 15): LengthAwarePaginator;

    public function criar(array $dados): Lead;

    public function atualizar(Lead $lead, array $dados): Lead;

    public function excluir(Lead $lead): void;

    public function buscarPorEstagio(string $estagio): array;

    public function contagemPorEstagio(): array;

    public function valorTotalPorEstagio(): array;

    public function leadsPorResponsavel(int $usuarioId): array;
}
