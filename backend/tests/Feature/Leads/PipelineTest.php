<?php

use App\Models\Lead;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

function conta(string $perfil, string $email): array
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

test('o pipeline devolve os cinco estágios, mesmo os vazios', function () {
    [$gerente, $token] = conta('gerente', 'pipeline.gerente@koracrm.com.br');
    Lead::create(['nome' => 'Só um', 'estagio' => 'contato', 'criado_por' => $gerente->id]);

    $resposta = $this->withToken($token)->getJson('/api/pipeline');

    $resposta->assertOk()
        ->assertJsonStructure(['novo', 'contato', 'proposta', 'ganho', 'perdido']);

    expect($resposta->json('contato'))->toHaveCount(1)
        ->and($resposta->json('novo'))->toBeEmpty();
});

test('o vendedor só vê no pipeline os leads dele', function () {
    [$gerente] = conta('gerente', 'pipeline.dono@koracrm.com.br');
    [$vendedor, $token] = conta('vendedor', 'pipeline.vendedor@koracrm.com.br');

    Lead::create(['nome' => 'Do gerente', 'estagio' => 'novo', 'criado_por' => $gerente->id]);
    Lead::create([
        'nome' => 'Do vendedor',
        'estagio' => 'novo',
        'criado_por' => $gerente->id,
        'responsavel_id' => $vendedor->id,
    ]);

    $resposta = $this->withToken($token)->getJson('/api/pipeline');

    expect($resposta->json('novo'))->toHaveCount(1)
        ->and($resposta->json('novo.0.nome'))->toBe('Do vendedor');
});

test('o pipeline exige autenticação', function () {
    $this->getJson('/api/pipeline')->assertUnauthorized();
});

test('o histórico do lead traz quem fez cada movimento', function () {
    [$gerente, $token] = conta('gerente', 'historico.gerente@koracrm.com.br');

    $lead = $this->withToken($token)
        ->postJson('/api/leads', ['nome' => 'Lead com histórico'])
        ->json('id');

    $this->withToken($token)
        ->patchJson("/api/leads/{$lead}/estagio", ['estagio' => 'contato'])
        ->assertOk();

    $resposta = $this->withToken($token)->getJson("/api/leads/{$lead}/historico");

    $resposta->assertOk();
    expect($resposta->json())->toHaveCount(2)
        ->and($resposta->json('0.usuario.nome'))->toBe('Conta gerente');
});

test('histórico de lead inexistente devolve 404', function () {
    [, $token] = conta('gerente', 'historico.404@koracrm.com.br');

    $this->withToken($token)->getJson('/api/leads/9999/historico')->assertNotFound();
});

test('vendedor não lê o histórico de lead de outro', function () {
    [$gerente] = conta('gerente', 'historico.dono@koracrm.com.br');
    [, $token] = conta('vendedor', 'historico.intruso@koracrm.com.br');

    $lead = Lead::create(['nome' => 'Lead alheio', 'estagio' => 'novo', 'criado_por' => $gerente->id]);

    $this->withToken($token)->getJson("/api/leads/{$lead->id}/historico")->assertForbidden();
});
