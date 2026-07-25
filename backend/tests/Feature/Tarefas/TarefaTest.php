<?php

use App\Models\Lead;
use App\Models\Tarefa;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function usuarioETarefaSetup(): array
{
    $usuario = Usuario::create([
        'nome' => 'Teste Tarefas',
        'email' => 'tarefas@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo' => true,
    ]);

    $lead = Lead::create([
        'nome' => 'Lead para Tarefas',
        'estagio' => 'novo',
        'criado_por' => $usuario->id,
    ]);

    $token = $usuario->createToken('api-token')->plainTextToken;

    return [$usuario, $lead, $token];
}

test('cria tarefa vinculada a lead', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $resposta = $this->withToken($token)->postJson('/api/tarefas', [
        'titulo' => 'Ligar para o cliente',
        'lead_id' => $lead->id,
        'prazo' => now()->addDays(3)->toISOString(),
    ]);

    $resposta->assertStatus(201)
        ->assertJsonFragment(['titulo' => 'Ligar para o cliente']);
});

test('vendedor não cria tarefa em lead de outro usuário', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $outro = Usuario::create([
        'nome' => 'Outro Vendedor',
        'email' => 'outro@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo' => true,
    ]);

    $leadAlheio = Lead::create([
        'nome' => 'Lead de Outro',
        'estagio' => 'novo',
        'criado_por' => $outro->id,
        'responsavel_id' => $outro->id,
    ]);

    $resposta = $this->withToken($token)->postJson('/api/tarefas', [
        'titulo' => 'Tentativa indevida',
        'lead_id' => $leadAlheio->id,
    ]);

    $resposta->assertStatus(403);
});

test('vendedor não atribui tarefa a outro usuário', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $outro = Usuario::create([
        'nome' => 'Colega',
        'email' => 'colega@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo' => true,
    ]);

    $resposta = $this->withToken($token)->postJson('/api/tarefas', [
        'titulo' => 'Tarefa própria',
        'lead_id' => $lead->id,
        'responsavel_id' => $outro->id,
    ]);

    $resposta->assertStatus(201);
    expect(Tarefa::latest('id')->first()->responsavel_id)->toBe($usuario->id);
});

test('criar tarefa requer titulo', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $resposta = $this->withToken($token)->postJson('/api/tarefas', [
        'lead_id' => $lead->id,
    ]);

    $resposta->assertStatus(422)
        ->assertJsonValidationErrors(['titulo']);
});

test('conclui tarefa com sucesso', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $tarefa = Tarefa::create([
        'titulo' => 'Tarefa para concluir',
        'lead_id' => $lead->id,
        'responsavel_id' => $usuario->id,
        'concluida' => false,
    ]);

    $resposta = $this->withToken($token)
        ->patchJson("/api/tarefas/{$tarefa->id}/concluir");

    $resposta->assertStatus(200)
        ->assertJsonFragment(['concluida' => true]);
});

test('nao pode concluir tarefa ja concluida', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $tarefa = Tarefa::create([
        'titulo' => 'Já concluída',
        'lead_id' => $lead->id,
        'responsavel_id' => $usuario->id,
        'concluida' => true,
        'concluida_em' => now(),
    ]);

    $resposta = $this->withToken($token)
        ->patchJson("/api/tarefas/{$tarefa->id}/concluir");

    $resposta->assertStatus(409);
});

test('exclui tarefa com soft delete', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $tarefa = Tarefa::create([
        'titulo' => 'Tarefa para excluir',
        'lead_id' => $lead->id,
        'responsavel_id' => $usuario->id,
        'concluida' => false,
    ]);

    $resposta = $this->withToken($token)
        ->deleteJson("/api/tarefas/{$tarefa->id}");

    $resposta->assertStatus(204);
    $this->assertSoftDeleted('tarefas', ['id' => $tarefa->id]);
});
