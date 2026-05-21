<?php

use App\Application\DTOs\CriarLeadDTO;
use App\Application\Services\CriarLeadService;
use App\Application\Services\RegistrarHistoricoService;
use App\Domain\Lead\LeadRepositoryInterface;
use App\Models\Lead;

beforeEach(function () {
    $this->repositorio = Mockery::mock(LeadRepositoryInterface::class);
    $this->historico = Mockery::mock(RegistrarHistoricoService::class);
    $this->servico = new CriarLeadService($this->repositorio, $this->historico);
});

afterEach(fn () => Mockery::close());

test('cria lead com dados mínimos obrigatórios', function () {
    $dto = new CriarLeadDTO(
        nome: 'João Silva',
        criadoPor: 1,
    );

    $leadEsperado = new Lead();
    $leadEsperado->id = 1;
    $leadEsperado->nome = 'João Silva';
    $leadEsperado->estagio = 'novo';

    $this->repositorio
        ->shouldReceive('criar')
        ->once()
        ->with(Mockery::on(fn ($dados) => $dados['nome'] === 'João Silva'
            && $dados['estagio'] === 'novo'
            && $dados['criado_por'] === 1
        ))
        ->andReturn($leadEsperado);

    $this->historico->shouldReceive('registrar')->once();

    $resultado = $this->servico->executar($dto);

    expect($resultado->nome)->toBe('João Silva');
    expect($resultado->estagio)->toBe('novo');
});

test('lead sempre é criado no estágio novo independente de outros dados', function () {
    $dto = new CriarLeadDTO(
        nome: 'Maria Santos',
        criadoPor: 2,
        email: 'maria@empresa.com',
        valorEstimado: 5000.0,
    );

    $lead = new Lead();
    $lead->id = 2;
    $lead->nome = 'Maria Santos';
    $lead->estagio = 'novo';

    $this->repositorio
        ->shouldReceive('criar')
        ->with(Mockery::on(fn ($dados) => $dados['estagio'] === 'novo'))
        ->andReturn($lead);

    $this->historico->shouldReceive('registrar');

    $resultado = $this->servico->executar($dto);

    expect($resultado->estagio)->toBe('novo');
});

test('registra histórico de criação', function () {
    $dto = new CriarLeadDTO(nome: 'Lead Novo', criadoPor: 5);

    $lead = new Lead();
    $lead->id = 3;
    $lead->nome = 'Lead Novo';
    $lead->estagio = 'novo';

    $this->repositorio->shouldReceive('criar')->andReturn($lead);

    $this->historico
        ->shouldReceive('registrar')
        ->once()
        ->with(
            3,
            5,
            'criacao',
            Mockery::type('string'),
        );

    $this->servico->executar($dto);
});

test('dto preserva dados opcionais corretamente', function () {
    $dto = CriarLeadDTO::fromArray([
        'nome' => 'Carlos Ferreira',
        'email' => 'carlos@empresa.com',
        'telefone' => '(11) 99999-0000',
        'empresa' => 'TechCorp',
        'valor_estimado' => '15000.50',
        'origem' => 'linkedin',
        'tags' => ['vip', 'tech'],
        'responsavel_id' => 7,
    ], criadoPor: 1);

    expect($dto->nome)->toBe('Carlos Ferreira');
    expect($dto->email)->toBe('carlos@empresa.com');
    expect($dto->valorEstimado)->toBe(15000.50);
    expect($dto->origem)->toBe('linkedin');
    expect($dto->tags)->toBe(['vip', 'tech']);
    expect($dto->responsavelId)->toBe(7);
    expect($dto->criadoPor)->toBe(1);
});

test('dto usa criado_por como responsavel quando responsavel_id não informado', function () {
    $dto = CriarLeadDTO::fromArray(
        ['nome' => 'Lead Sem Responsável'],
        criadoPor: 10
    );

    expect($dto->responsavelId)->toBe(10);
});
