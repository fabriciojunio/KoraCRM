<?php

use App\Application\Services\DashboardService;
use App\Domain\Lead\LeadRepositoryInterface;
use App\Models\Lead;

beforeEach(function () {
    $this->repositorio = Mockery::mock(LeadRepositoryInterface::class);
    $this->servico = new DashboardService($this->repositorio);
});

afterEach(fn () => Mockery::close());

test('metricas calcula taxa de conversao corretamente', function () {
    $this->repositorio->shouldReceive('contagemPorEstagio')->andReturn([
        'novo'     => 10,
        'contato'  => 5,
        'proposta' => 3,
        'ganho'    => 2,
        'perdido'  => 1,
    ]);

    $this->repositorio->shouldReceive('valorTotalPorEstagio')->andReturn([
        'novo'     => 0,
        'contato'  => 5000,
        'proposta' => 15000,
        'ganho'    => 30000,
        'perdido'  => 0,
    ]);

    // Força execução sem cache
    \Illuminate\Support\Facades\Cache::shouldReceive('remember')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $resultado = $this->servico->metricas();

    $totalLeads = 10 + 5 + 3 + 2 + 1; // 21
    $taxaEsperada = round((2 / 21) * 100, 1);

    expect($resultado['total_leads'])->toBe(21);
    expect($resultado['leads_ganhos'])->toBe(2);
    expect($resultado['taxa_conversao'])->toBe($taxaEsperada);
});

test('metricas retorna zero quando nao ha leads', function () {
    $this->repositorio->shouldReceive('contagemPorEstagio')->andReturn([
        'novo' => 0, 'contato' => 0, 'proposta' => 0, 'ganho' => 0, 'perdido' => 0,
    ]);
    $this->repositorio->shouldReceive('valorTotalPorEstagio')->andReturn([
        'novo' => 0, 'contato' => 0, 'proposta' => 0, 'ganho' => 0, 'perdido' => 0,
    ]);

    \Illuminate\Support\Facades\Cache::shouldReceive('remember')
        ->andReturnUsing(fn ($key, $ttl, $callback) => $callback());

    $resultado = $this->servico->metricas();

    expect($resultado['taxa_conversao'])->toBe(0.0);
    expect($resultado['total_leads'])->toBe(0);
});

test('funil retorna todos os estagios', function () {
    $this->repositorio->shouldReceive('contagemPorEstagio')->andReturn([
        'novo' => 5, 'contato' => 3, 'proposta' => 2, 'ganho' => 1, 'perdido' => 1,
    ]);

    $resultado = $this->servico->funil();

    expect($resultado)->toHaveCount(5);

    $estagios = array_column($resultado, 'estagio');
    expect($estagios)->toContain('novo');
    expect($estagios)->toContain('contato');
    expect($estagios)->toContain('proposta');
    expect($estagios)->toContain('ganho');
    expect($estagios)->toContain('perdido');
});
