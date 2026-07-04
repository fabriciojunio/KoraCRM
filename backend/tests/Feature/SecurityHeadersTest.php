<?php

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('respostas da API incluem cabeçalhos de segurança', function () {
    Usuario::create([
        'nome'   => 'Headers Teste',
        'email'  => 'headers@koracrm.com.br',
        'senha'  => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo'  => true,
    ]);

    $resposta = $this->postJson('/api/auth/login', [
        'email' => 'headers@koracrm.com.br',
        'senha' => 'senha123456',
    ]);

    $resposta->assertStatus(200)
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'DENY')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('X-XSS-Protection', '0')
        ->assertHeader('Content-Security-Policy');

    expect($resposta->headers->get('Permissions-Policy'))->toContain('geolocation=()');
    expect($resposta->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'none'");
});

test('cabeçalho X-Powered-By não é exposto', function () {
    $resposta = $this->getJson('/api/auth/perfil');

    expect($resposta->headers->has('X-Powered-By'))->toBeFalse();
});
