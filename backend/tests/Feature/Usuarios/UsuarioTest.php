<?php

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

function contaDePerfil(string $perfil, string $email): array
{
    $usuario = Usuario::create([
        'nome' => "Conta {$perfil}",
        'email' => $email,
        'senha' => Hash::make('senha123456'),
        'perfil' => $perfil,
        'ativo' => true,
    ]);

    return [$usuario, $usuario->createToken('api-token')->plainTextToken];
}

test('gerente lista a equipe sem ver senha', function () {
    [, $token] = contaDePerfil('gerente', 'gerente.lista@koracrm.com.br');
    contaDePerfil('vendedor', 'vendedor.lista@koracrm.com.br');

    $resposta = $this->withToken($token)->getJson('/api/usuarios');

    $resposta->assertOk()->assertJsonCount(2);
    expect($resposta->json()[0])->not->toHaveKey('senha');
});

test('vendedor não acessa a lista da equipe', function () {
    [, $token] = contaDePerfil('vendedor', 'vendedor.sem.acesso@koracrm.com.br');

    $this->withToken($token)->getJson('/api/usuarios')->assertForbidden();
});

test('listar a equipe exige autenticação', function () {
    $this->getJson('/api/usuarios')->assertUnauthorized();
});

test('gerente vê os detalhes de um usuário', function () {
    [, $token] = contaDePerfil('gerente', 'gerente.detalhe@koracrm.com.br');
    [$vendedor] = contaDePerfil('vendedor', 'vendedor.detalhe@koracrm.com.br');

    $this->withToken($token)
        ->getJson("/api/usuarios/{$vendedor->id}")
        ->assertOk()
        ->assertJsonPath('email', 'vendedor.detalhe@koracrm.com.br');
});

test('usuário inexistente devolve 404', function () {
    [, $token] = contaDePerfil('gerente', 'gerente.404@koracrm.com.br');

    $this->withToken($token)->getJson('/api/usuarios/9999')->assertNotFound();
});

test('admin cria usuário novo', function () {
    [, $token] = contaDePerfil('admin', 'admin.cria@koracrm.com.br');

    $resposta = $this->withToken($token)->postJson('/api/usuarios', [
        'nome' => 'Vendedora Nova',
        'email' => 'nova@koracrm.com.br',
        'senha' => 'SenhaForte123',
        'senha_confirmation' => 'SenhaForte123',
        'perfil' => 'vendedor',
    ]);

    $resposta->assertCreated()->assertJsonPath('perfil', 'vendedor');
    expect(Usuario::where('email', 'nova@koracrm.com.br')->exists())->toBeTrue();
});

test('a senha do usuário criado fica com hash', function () {
    [, $token] = contaDePerfil('admin', 'admin.hash@koracrm.com.br');

    $this->withToken($token)->postJson('/api/usuarios', [
        'nome' => 'Vendedor Hash',
        'email' => 'hash@koracrm.com.br',
        'senha' => 'SenhaForte123',
        'senha_confirmation' => 'SenhaForte123',
        'perfil' => 'vendedor',
    ])->assertCreated();

    $senhaGravada = Usuario::where('email', 'hash@koracrm.com.br')->value('senha');

    expect($senhaGravada)->not->toBe('SenhaForte123')
        ->and(Hash::check('SenhaForte123', $senhaGravada))->toBeTrue();
});

test('não cria usuário com e-mail repetido', function () {
    [, $token] = contaDePerfil('admin', 'admin.repetido@koracrm.com.br');

    $this->withToken($token)->postJson('/api/usuarios', [
        'nome' => 'Repetido',
        'email' => 'admin.repetido@koracrm.com.br',
        'senha' => 'SenhaForte123',
        'senha_confirmation' => 'SenhaForte123',
        'perfil' => 'vendedor',
    ])->assertStatus(422)->assertJsonValidationErrors('email');
});

test('senha fraca é recusada na criação', function () {
    [, $token] = contaDePerfil('admin', 'admin.fraca@koracrm.com.br');

    $this->withToken($token)->postJson('/api/usuarios', [
        'nome' => 'Senha Fraca',
        'email' => 'fraca@koracrm.com.br',
        'senha' => '12345678',
        'senha_confirmation' => '12345678',
        'perfil' => 'vendedor',
    ])->assertStatus(422)->assertJsonValidationErrors('senha');
});

test('confirmação de senha diferente é recusada', function () {
    [, $token] = contaDePerfil('admin', 'admin.confirma@koracrm.com.br');

    $this->withToken($token)->postJson('/api/usuarios', [
        'nome' => 'Confirmação Errada',
        'email' => 'confirma@koracrm.com.br',
        'senha' => 'SenhaForte123',
        'senha_confirmation' => 'OutraCoisa123',
        'perfil' => 'vendedor',
    ])->assertStatus(422)->assertJsonValidationErrors('senha');
});

test('perfil inválido é recusado', function () {
    [, $token] = contaDePerfil('admin', 'admin.perfil@koracrm.com.br');

    $this->withToken($token)->postJson('/api/usuarios', [
        'nome' => 'Perfil Errado',
        'email' => 'perfil@koracrm.com.br',
        'senha' => 'SenhaForte123',
        'senha_confirmation' => 'SenhaForte123',
        'perfil' => 'diretor',
    ])->assertStatus(422)->assertJsonValidationErrors('perfil');
});

test('gerente atualiza o nome e o perfil', function () {
    [, $token] = contaDePerfil('gerente', 'gerente.atualiza@koracrm.com.br');
    [$vendedor] = contaDePerfil('vendedor', 'vendedor.atualiza@koracrm.com.br');

    $this->withToken($token)
        ->putJson("/api/usuarios/{$vendedor->id}", ['nome' => 'Nome Trocado', 'perfil' => 'gerente'])
        ->assertOk()
        ->assertJsonPath('nome', 'Nome Trocado')
        ->assertJsonPath('perfil', 'gerente');
});

test('atualizar sem mandar senha não apaga a senha existente', function () {
    [, $token] = contaDePerfil('gerente', 'gerente.semsenha@koracrm.com.br');
    [$vendedor] = contaDePerfil('vendedor', 'vendedor.semsenha@koracrm.com.br');
    $senhaAntes = $vendedor->senha;

    $this->withToken($token)
        ->putJson("/api/usuarios/{$vendedor->id}", ['nome' => 'Só o nome'])
        ->assertOk();

    expect($vendedor->fresh()->senha)->toBe($senhaAntes);
});

test('atualizar com senha nova troca o hash', function () {
    [, $token] = contaDePerfil('gerente', 'gerente.trocasenha@koracrm.com.br');
    [$vendedor] = contaDePerfil('vendedor', 'vendedor.trocasenha@koracrm.com.br');

    $this->withToken($token)->putJson("/api/usuarios/{$vendedor->id}", [
        'senha' => 'OutraSenha123',
        'senha_confirmation' => 'OutraSenha123',
    ])->assertOk();

    expect(Hash::check('OutraSenha123', $vendedor->fresh()->senha))->toBeTrue();
});

test('atualizar usuário inexistente devolve 404', function () {
    [, $token] = contaDePerfil('gerente', 'gerente.atualiza404@koracrm.com.br');

    $this->withToken($token)->putJson('/api/usuarios/9999', ['nome' => 'Ninguém'])->assertNotFound();
});

test('desativar usuário mantém o registro e marca como inativo', function () {
    [, $token] = contaDePerfil('admin', 'admin.desativa@koracrm.com.br');
    [$vendedor] = contaDePerfil('vendedor', 'vendedor.desativa@koracrm.com.br');

    $this->withToken($token)
        ->deleteJson("/api/usuarios/{$vendedor->id}")
        ->assertOk()
        ->assertJsonPath('mensagem', 'Usuário desativado com sucesso.');

    expect($vendedor->fresh()->ativo)->toBeFalse();
});

test('ninguém desativa a própria conta', function () {
    [$admin, $token] = contaDePerfil('admin', 'admin.suicidio@koracrm.com.br');

    $this->withToken($token)
        ->deleteJson("/api/usuarios/{$admin->id}")
        ->assertForbidden()
        ->assertJsonPath('mensagem', 'Você não pode desativar sua própria conta.');

    expect($admin->fresh()->ativo)->toBeTrue();
});

test('desativar usuário inexistente devolve 404', function () {
    [, $token] = contaDePerfil('admin', 'admin.desativa404@koracrm.com.br');

    $this->withToken($token)->deleteJson('/api/usuarios/9999')->assertNotFound();
});
