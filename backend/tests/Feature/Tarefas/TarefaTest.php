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
        'nome'  => 'Teste Tarefas',
        'email' => 'tarefas@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo' => true,
    ]);

    $lead = Lead::create([
        'nome'       => 'Lead para Tarefas',
        'estagio'    => 'novo',
        'criado_por' => $usuario->id,
    ]);

    $token = $usuario->createToken('api-token')->plainTextToken;

    return [$usuario, $lead, $token];
}

test('cria tarefa vinculada a lead', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $resposta = $this->withToken($token)->postJson('/api/tarefas', [
        'titulo'  => 'Ligar para o cliente',
        'lead_id' => $lead->id,
        'prazo'   => now()->addDays(3)->toISOString(),
    ]);

    $resposta->assertStatus(201)
             ->assertJsonFragment(['titulo' => 'Ligar para o cliente']);
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
        'titulo'         => 'Tarefa para concluir',
        'lead_id'        => $lead->id,
        'responsavel_id' => $usuario->id,
        'concluida'      => false,
    ]);

    $resposta = $this->withToken($token)
                     ->patchJson("/api/tarefas/{$tarefa->id}/concluir");

    $resposta->assertStatus(200)
             ->assertJsonFragment(['concluida' => true]);
});

test('nao pode concluir tarefa ja concluida', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $tarefa = Tarefa::create([
        'titulo'         => 'Já concluída',
        'lead_id'        => $lead->id,
        'responsavel_id' => $usuario->id,
        'concluida'      => true,
        'concluida_em'   => now(),
    ]);

    $resposta = $this->withToken($token)
                     ->patchJson("/api/tarefas/{$tarefa->id}/concluir");

    $resposta->assertStatus(409);
});

test('exclui tarefa com soft delete', function () {
    [$usuario, $lead, $token] = usuarioETarefaSetup();

    $tarefa = Tarefa::create([
        'titulo'         => 'Tarefa para excluir',
        'lead_id'        => $lead->id,
        'responsavel_id' => $usuario->id,
        'concluida'      => false,
    ]);

    $resposta = $this->withToken($token)
                     ->deleteJson("/api/tarefas/{$tarefa->id}");

    $resposta->assertStatus(204);
    $this->assertSoftDeleted('tarefas', ['id' => $tarefa->id]);
});
