<?php

use App\Models\Lead;

test('lead recém criado não está fechado', function () {
    $lead = new Lead();
    $lead->estagio = 'novo';

    expect($lead->estaFechado())->toBeFalse();
});

test('lead no estágio ganho está fechado', function () {
    $lead = new Lead();
    $lead->estagio = 'ganho';

    expect($lead->estaFechado())->toBeTrue();
});

test('lead no estágio perdido está fechado', function () {
    $lead = new Lead();
    $lead->estagio = 'perdido';

    expect($lead->estaFechado())->toBeTrue();
});

test('lead no estágio contato não está fechado', function () {
    $lead = new Lead();
    $lead->estagio = 'contato';

    expect($lead->estaFechado())->toBeFalse();
});

test('lead no estágio proposta não está fechado', function () {
    $lead = new Lead();
    $lead->estagio = 'proposta';

    expect($lead->estaFechado())->toBeFalse();
});

test('lead fechado não pode mover para nenhum estágio', function () {
    $lead = new Lead();
    $lead->estagio = 'ganho';

    foreach (Lead::ESTAGIOS as $estagio) {
        expect($lead->podeMovarPara($estagio))->toBeFalse();
    }
});

test('lead aberto não pode mover para estágio inválido', function () {
    $lead = new Lead();
    $lead->estagio = 'novo';

    expect($lead->podeMovarPara('estagio_fake'))->toBeFalse();
});

test('lead aberto pode mover para qualquer estágio válido', function () {
    $lead = new Lead();
    $lead->estagio = 'novo';

    foreach (Lead::ESTAGIOS as $estagio) {
        expect($lead->podeMovarPara($estagio))->toBeTrue();
    }
});

test('constante ESTAGIOS contém todos os estágios esperados', function () {
    expect(Lead::ESTAGIOS)->toBe(['novo', 'contato', 'proposta', 'ganho', 'perdido']);
});

test('constante ESTAGIOS_FECHADOS contém ganho e perdido', function () {
    expect(Lead::ESTAGIOS_FECHADOS)->toContain('ganho');
    expect(Lead::ESTAGIOS_FECHADOS)->toContain('perdido');
    expect(Lead::ESTAGIOS_FECHADOS)->not->toContain('novo');
    expect(Lead::ESTAGIOS_FECHADOS)->not->toContain('contato');
    expect(Lead::ESTAGIOS_FECHADOS)->not->toContain('proposta');
});
