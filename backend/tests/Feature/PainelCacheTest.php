<?php

use App\Application\Services\DashboardService;
use App\Models\Lead;
use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

function gerenteComToken(): array
{
    $usuario = Usuario::create([
        'nome' => 'Gerente do Painel',
        'email' => 'painel@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'gerente',
        'ativo' => true,
    ]);

    return [$usuario, $usuario->createToken('api-token')->plainTextToken];
}

test('as métricas ficam em cache entre duas chamadas seguidas', function () {
    [$gerente, $token] = gerenteComToken();
    Lead::create(['nome' => 'Primeiro', 'estagio' => 'novo', 'criado_por' => $gerente->id]);

    $this->withToken($token)->getJson('/api/dashboard/metricas')->assertOk();

    expect(Cache::has(DashboardService::CHAVE_METRICAS))->toBeTrue();
});

test('criar um lead derruba o cache do painel', function () {
    [$gerente, $token] = gerenteComToken();

    $this->withToken($token)->getJson('/api/dashboard/metricas')->assertOk();

    $this->withToken($token)->postJson('/api/leads', ['nome' => 'Lead novo'])->assertCreated();

    expect(Cache::has(DashboardService::CHAVE_METRICAS))->toBeFalse();
});

test('mover um lead aparece no painel na mesma hora', function () {
    [$gerente, $token] = gerenteComToken();
    $lead = Lead::create(['nome' => 'Em movimento', 'estagio' => 'novo', 'criado_por' => $gerente->id]);

    $this->withToken($token)
        ->getJson('/api/dashboard/metricas')
        ->assertJsonPath('contagem_por_estagio.novo', 1)
        ->assertJsonPath('contagem_por_estagio.contato', 0);

    $this->withToken($token)
        ->patchJson("/api/leads/{$lead->id}/estagio", ['estagio' => 'contato'])
        ->assertOk();

    $this->withToken($token)
        ->getJson('/api/dashboard/metricas')
        ->assertJsonPath('contagem_por_estagio.novo', 0)
        ->assertJsonPath('contagem_por_estagio.contato', 1);
});

test('concluir uma tarefa também atualiza o painel', function () {
    [$gerente, $token] = gerenteComToken();
    $lead = Lead::create(['nome' => 'Com tarefa', 'estagio' => 'novo', 'criado_por' => $gerente->id]);

    $tarefa = $this->withToken($token)->postJson('/api/tarefas', [
        'titulo' => 'Ligar para o cliente',
        'lead_id' => $lead->id,
    ])->json('id');

    $this->withToken($token)
        ->getJson('/api/dashboard/metricas')
        ->assertJsonPath('tarefas_pendentes', 1);

    $this->withToken($token)->patchJson("/api/tarefas/{$tarefa}/concluir")->assertOk();

    $this->withToken($token)
        ->getJson('/api/dashboard/metricas')
        ->assertJsonPath('tarefas_pendentes', 0);
});
