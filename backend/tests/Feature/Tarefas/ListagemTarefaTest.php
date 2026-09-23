<?php

use App\Models\Lead;
use App\Models\Tarefa;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

function contaTarefa(string $perfil, string $email): array
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

function tarefaDe(Usuario $responsavel, Usuario $criador, array $atributos = []): Tarefa
{
    $lead = Lead::create([
        'nome' => 'Lead da tarefa',
        'estagio' => 'novo',
        'criado_por' => $criador->id,
        'responsavel_id' => $responsavel->id,
    ]);

    return Tarefa::create(array_merge([
        'titulo' => 'Ligar para o cliente',
        'lead_id' => $lead->id,
        'responsavel_id' => $responsavel->id,
        'prioridade' => 'media',
        'concluida' => false,
    ], $atributos));
}

test('a listagem traz o lead e o responsável de cada tarefa', function () {
    [$gerente, $token] = contaTarefa('gerente', 'tarefa.lista@koracrm.com.br');
    tarefaDe($gerente, $gerente);

    $resposta = $this->withToken($token)->getJson('/api/tarefas');

    $resposta->assertOk()->assertJsonCount(1);
    expect($resposta->json('0.lead.nome'))->toBe('Lead da tarefa')
        ->and($resposta->json('0.responsavel.nome'))->toBe('Conta gerente')
        ->and($resposta->json('0'))->toHaveKey('atrasada');
});

test('o vendedor só vê as próprias tarefas', function () {
    [$gerente] = contaTarefa('gerente', 'tarefa.gerente@koracrm.com.br');
    [$vendedor, $token] = contaTarefa('vendedor', 'tarefa.vendedor@koracrm.com.br');

    tarefaDe($gerente, $gerente, ['titulo' => 'Do gerente']);
    tarefaDe($vendedor, $gerente, ['titulo' => 'Do vendedor']);

    $resposta = $this->withToken($token)->getJson('/api/tarefas');

    $resposta->assertOk()->assertJsonCount(1);
    expect($resposta->json('0.titulo'))->toBe('Do vendedor');
});

test('a listagem exige autenticação', function () {
    $this->getJson('/api/tarefas')->assertUnauthorized();
});

test('detalhe de tarefa inexistente devolve 404', function () {
    [, $token] = contaTarefa('gerente', 'tarefa.404@koracrm.com.br');

    $this->withToken($token)->getJson('/api/tarefas/9999')->assertNotFound();
});

test('vendedor não abre tarefa de outro', function () {
    [$gerente] = contaTarefa('gerente', 'tarefa.dono@koracrm.com.br');
    [, $token] = contaTarefa('vendedor', 'tarefa.intruso@koracrm.com.br');

    $tarefa = tarefaDe($gerente, $gerente);

    $this->withToken($token)->getJson("/api/tarefas/{$tarefa->id}")->assertForbidden();
});

test('atualiza título e prioridade da tarefa', function () {
    [$gerente, $token] = contaTarefa('gerente', 'tarefa.atualiza@koracrm.com.br');
    $tarefa = tarefaDe($gerente, $gerente);

    $this->withToken($token)
        ->putJson("/api/tarefas/{$tarefa->id}", ['titulo' => 'Enviar proposta', 'prioridade' => 'alta'])
        ->assertOk()
        ->assertJsonPath('titulo', 'Enviar proposta')
        ->assertJsonPath('prioridade', 'alta');
});

test('prioridade fora da lista é recusada', function () {
    [$gerente, $token] = contaTarefa('gerente', 'tarefa.prioridade@koracrm.com.br');
    $tarefa = tarefaDe($gerente, $gerente);

    $this->withToken($token)
        ->putJson("/api/tarefas/{$tarefa->id}", ['prioridade' => 'urgentissima'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('prioridade');
});

test('atualizar tarefa inexistente devolve 404', function () {
    [, $token] = contaTarefa('gerente', 'tarefa.atualiza404@koracrm.com.br');

    $this->withToken($token)->putJson('/api/tarefas/9999', ['titulo' => 'Nada'])->assertNotFound();
});

test('excluir tarefa inexistente devolve 404', function () {
    [, $token] = contaTarefa('gerente', 'tarefa.exclui404@koracrm.com.br');

    $this->withToken($token)->deleteJson('/api/tarefas/9999')->assertNotFound();
});

test('concluir tarefa inexistente devolve 404', function () {
    [, $token] = contaTarefa('gerente', 'tarefa.conclui404@koracrm.com.br');

    $this->withToken($token)->patchJson('/api/tarefas/9999/concluir')->assertNotFound();
});

test('a tarefa vencida e aberta aparece como atrasada', function () {
    [$gerente, $token] = contaTarefa('gerente', 'tarefa.atrasada@koracrm.com.br');
    tarefaDe($gerente, $gerente, ['prazo' => now()->subDays(2)]);

    $resposta = $this->withToken($token)->getJson('/api/tarefas');

    expect($resposta->json('0.atrasada'))->toBeTrue();
});
