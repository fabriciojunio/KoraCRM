<?php

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// Cria usuário de teste reutilizável
function criarUsuario(array $atributos = []): Usuario
{
    return Usuario::create(array_merge([
        'nome' => 'Usuário Teste',
        'email' => 'teste@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo' => true,
    ], $atributos));
}

test('usuário realiza login com credenciais válidas', function () {
    criarUsuario();

    $resposta = $this->postJson('/api/auth/login', [
        'email' => 'teste@koracrm.com.br',
        'senha' => 'senha123456',
    ]);

    $resposta->assertStatus(200)
        ->assertJsonStructure([
            'token',
            'usuario' => ['id', 'nome', 'email', 'perfil'],
            'expira_em',
        ]);
});

test('login falha com senha incorreta', function () {
    criarUsuario();

    $resposta = $this->postJson('/api/auth/login', [
        'email' => 'teste@koracrm.com.br',
        'senha' => 'senha_errada',
    ]);

    $resposta->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login falha com email inexistente', function () {
    $resposta = $this->postJson('/api/auth/login', [
        'email' => 'naoexiste@koracrm.com.br',
        'senha' => 'senha123456',
    ]);

    $resposta->assertStatus(422);
});

test('login falha com usuário inativo', function () {
    criarUsuario(['ativo' => false]);

    $resposta = $this->postJson('/api/auth/login', [
        'email' => 'teste@koracrm.com.br',
        'senha' => 'senha123456',
    ]);

    $resposta->assertStatus(422);
});

test('login valida formato do email', function () {
    $resposta = $this->postJson('/api/auth/login', [
        'email' => 'email_invalido',
        'senha' => 'senha123456',
    ]);

    $resposta->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('login requer senha mínima de 8 caracteres', function () {
    $resposta = $this->postJson('/api/auth/login', [
        'email' => 'teste@koracrm.com.br',
        'senha' => '123',
    ]);

    $resposta->assertStatus(422)
        ->assertJsonValidationErrors(['senha']);
});

test('login rejeita email com injeção de CRLF', function () {
    $resposta = $this->postJson('/api/auth/login', [
        'email' => "teste@koracrm.com.br\r\nBcc: vitima@exemplo.com",
        'senha' => 'senha123456',
    ]);

    $resposta->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('usuário realiza logout com sucesso', function () {
    $usuario = criarUsuario();
    $token = $usuario->createToken('api-token')->plainTextToken;

    $resposta = $this->withToken($token)
        ->postJson('/api/auth/logout');

    $resposta->assertStatus(200)
        ->assertJsonFragment(['mensagem' => 'Logout realizado com sucesso.']);
});

test('rota de logout requer autenticação', function () {
    $resposta = $this->postJson('/api/auth/logout');

    $resposta->assertStatus(401);
});

test('perfil retorna dados do usuário autenticado', function () {
    $usuario = criarUsuario();
    $token = $usuario->createToken('api-token')->plainTextToken;

    $resposta = $this->withToken($token)
        ->getJson('/api/auth/perfil');

    $resposta->assertStatus(200)
        ->assertJsonFragment(['email' => 'teste@koracrm.com.br']);
});

test('senha não é retornada no perfil', function () {
    $usuario = criarUsuario();
    $token = $usuario->createToken('api-token')->plainTextToken;

    $resposta = $this->withToken($token)
        ->getJson('/api/auth/perfil');

    $resposta->assertJsonMissing(['senha']);
});
