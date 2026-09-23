<?php

use function Pest\Laravel\getJson;

test('a sonda de saúde responde sem autenticação', function () {
    getJson('/api/saude')
        ->assertOk()
        ->assertJsonPath('status', 'no ar')
        ->assertJsonPath('dependencias.banco', true)
        ->assertJsonPath('dependencias.cache', true);
});

test('a sonda de saúde não expõe detalhe de infraestrutura', function () {
    $resposta = getJson('/api/saude')->json();

    expect(array_keys($resposta))->toEqualCanonicalizing(['status', 'versao', 'dependencias']);
    expect(array_keys($resposta['dependencias']))->toEqualCanonicalizing(['banco', 'cache']);
});

test('toda resposta leva um identificador de requisição', function () {
    $resposta = getJson('/api/saude');

    expect($resposta->headers->get('X-Request-Id'))->not->toBeEmpty();
});

test('o identificador enviado pelo cliente é preservado', function () {
    $resposta = getJson('/api/saude', ['X-Request-Id' => 'chamado-48210']);

    expect($resposta->headers->get('X-Request-Id'))->toBe('chamado-48210');
});

test('identificador com formato estranho é trocado por um novo', function () {
    $resposta = getJson('/api/saude', ['X-Request-Id' => '<script>alert(1)</script>']);

    expect($resposta->headers->get('X-Request-Id'))
        ->not->toContain('<script>')
        ->toMatch('/^[a-f0-9-]{36}$/');
});
