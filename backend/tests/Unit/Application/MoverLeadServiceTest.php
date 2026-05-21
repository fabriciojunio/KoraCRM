<?php

use App\Application\Services\MoverLeadService;
use App\Application\Services\RegistrarHistoricoService;
use App\Domain\Lead\LeadRepositoryInterface;
use App\Models\Lead;
use Mockery\MockInterface;

// Configura mocks reutilizáveis
beforeEach(function () {
    $this->repositorio = Mockery::mock(LeadRepositoryInterface::class);
    $this->historico = Mockery::mock(RegistrarHistoricoService::class);
    $this->servico = new MoverLeadService($this->repositorio, $this->historico);
});

afterEach(function () {
    Mockery::close();
});

// Fábrica de lead para testes
function criarLeadMock(string $estagio = 'novo'): Lead
{
    $lead = new Lead();
    $lead->id = 1;
    $lead->nome = 'Lead Teste';
    $lead->estagio = $estagio;
    return $lead;
}

test('move lead de novo para contato com sucesso', function () {
    $lead = criarLeadMock('novo');

    $this->repositorio
        ->shouldReceive('buscarPorId')
        ->with(1)
        ->once()
        ->andReturn($lead);

    $this->repositorio
        ->shouldReceive('atualizar')
        ->once()
        ->andReturnUsing(fn ($l, $dados) => tap($l, fn ($x) => $x->estagio = $dados['estagio']));

    $this->historico
        ->shouldReceive('registrar')
        ->once();

    $resultado = $this->servico->executar(1, 'contato', 99);

    expect($resultado->estagio)->toBe('contato');
});

test('move lead para proposta corretamente', function () {
    $lead = criarLeadMock('contato');

    $this->repositorio->shouldReceive('buscarPorId')->andReturn($lead);
    $this->repositorio->shouldReceive('atualizar')->once()
        ->andReturnUsing(fn ($l, $dados) => tap($l, fn ($x) => $x->estagio = $dados['estagio']));
    $this->historico->shouldReceive('registrar')->once();

    $resultado = $this->servico->executar(1, 'proposta', 99);

    expect($resultado->estagio)->toBe('proposta');
});

test('lança exceção para estágio inválido', function () {
    $lead = criarLeadMock('novo');
    $this->repositorio->shouldReceive('buscarPorId')->andReturn($lead);

    $this->servico->executar(1, 'estagio_invalido', 99);
})->throws(\InvalidArgumentException::class);

test('lança exceção quando lead já está fechado como ganho', function () {
    $lead = criarLeadMock('ganho');
    $this->repositorio->shouldReceive('buscarPorId')->andReturn($lead);

    $this->servico->executar(1, 'contato', 99);
})->throws(\RuntimeException::class, 'Lead já está fechado');

test('lança exceção quando lead já está fechado como perdido', function () {
    $lead = criarLeadMock('perdido');
    $this->repositorio->shouldReceive('buscarPorId')->andReturn($lead);

    $this->servico->executar(1, 'contato', 99);
})->throws(\RuntimeException::class);

test('lança exceção quando lead não é encontrado', function () {
    $this->repositorio->shouldReceive('buscarPorId')->andReturn(null);

    $this->servico->executar(999, 'contato', 99);
})->throws(\RuntimeException::class, 'não encontrado');

test('lança exceção quando tenta mover para o mesmo estágio', function () {
    $lead = criarLeadMock('contato');
    $this->repositorio->shouldReceive('buscarPorId')->andReturn($lead);

    $this->servico->executar(1, 'contato', 99);
})->throws(\InvalidArgumentException::class, "já está no estágio");

test('registra histórico ao mover lead', function () {
    $lead = criarLeadMock('novo');

    $this->repositorio->shouldReceive('buscarPorId')->andReturn($lead);
    $this->repositorio->shouldReceive('atualizar')
        ->andReturnUsing(fn ($l, $dados) => tap($l, fn ($x) => $x->estagio = $dados['estagio']));

    $this->historico
        ->shouldReceive('registrar')
        ->once()
        ->with(
            1,
            99,
            'mudanca_estagio',
            Mockery::type('string'),
            ['estagio' => 'novo'],
            ['estagio' => 'ganho'],
        );

    $this->servico->executar(1, 'ganho', 99);
});

test('pode mover lead de qualquer estágio aberto para ganho', function () {
    $estagiosAbertos = ['novo', 'contato', 'proposta'];

    foreach ($estagiosAbertos as $estagio) {
        $lead = criarLeadMock($estagio);
        $repositorio = Mockery::mock(LeadRepositoryInterface::class);
        $historico = Mockery::mock(RegistrarHistoricoService::class);
        $servico = new MoverLeadService($repositorio, $historico);

        $repositorio->shouldReceive('buscarPorId')->andReturn($lead);
        $repositorio->shouldReceive('atualizar')
            ->andReturnUsing(fn ($l, $dados) => tap($l, fn ($x) => $x->estagio = $dados['estagio']));
        $historico->shouldReceive('registrar');

        $resultado = $servico->executar(1, 'ganho', 99);
        expect($resultado->estagio)->toBe('ganho');
    }
});
