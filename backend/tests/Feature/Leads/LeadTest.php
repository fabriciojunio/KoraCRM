<?php

use App\Models\Lead;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// Helpers de teste
function usuarioAutenticado(string $perfil = 'vendedor'): array
{
    $usuario = Usuario::create([
        'nome' => ucfirst($perfil) . ' Teste',
        'email' => $perfil . '@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => $perfil,
        'ativo' => true,
    ]);

    $token = $usuario->createToken('api-token')->plainTextToken;

    return [$usuario, $token];
}

function dadosLead(array $extras = []): array
{
    return array_merge([
        'nome' => 'Empresa ABC Ltda',
        'email' => 'contato@abc.com',
        'telefone' => '(11) 99999-0000',
        'empresa' => 'ABC Ltda',
        'valor_estimado' => 15000.00,
        'origem' => 'linkedin',
    ], $extras);
}

// ─── CRIAR LEAD ───────────────────────────────────────────────────────────────

test('cria lead com dados válidos', function () {
    [$usuario, $token] = usuarioAutenticado();

    $resposta = $this->withToken($token)
                     ->postJson('/api/leads', dadosLead());

    $resposta->assertStatus(201)
             ->assertJsonFragment(['nome' => 'Empresa ABC Ltda'])
             ->assertJsonFragment(['estagio' => 'novo']);

    $this->assertDatabaseHas('leads', ['nome' => 'Empresa ABC Ltda']);
});

test('novo lead sempre inicia no estágio novo', function () {
    [$usuario, $token] = usuarioAutenticado();

    $resposta = $this->withToken($token)
                     ->postJson('/api/leads', dadosLead());

    $resposta->assertJsonFragment(['estagio' => 'novo']);
});

test('criar lead requer nome', function () {
    [$usuario, $token] = usuarioAutenticado();

    $resposta = $this->withToken($token)
                     ->postJson('/api/leads', ['email' => 'sem@nome.com']);

    $resposta->assertStatus(422)
             ->assertJsonValidationErrors(['nome']);
});

test('criar lead requer autenticação', function () {
    $resposta = $this->postJson('/api/leads', dadosLead());

    $resposta->assertStatus(401);
});

test('criar lead valida formato de email', function () {
    [$usuario, $token] = usuarioAutenticado();

    $resposta = $this->withToken($token)
                     ->postJson('/api/leads', dadosLead(['email' => 'invalido']));

    $resposta->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
});

test('criar lead valida origem', function () {
    [$usuario, $token] = usuarioAutenticado();

    $resposta = $this->withToken($token)
                     ->postJson('/api/leads', dadosLead(['origem' => 'origem_fake']));

    $resposta->assertStatus(422)
             ->assertJsonValidationErrors(['origem']);
});

// ─── LISTAR LEADS ─────────────────────────────────────────────────────────────

test('lista leads paginados', function () {
    [$usuario, $token] = usuarioAutenticado();

    Lead::factory()->count(5)->create(['criado_por' => $usuario->id]);

    $resposta = $this->withToken($token)
                     ->getJson('/api/leads');

    $resposta->assertStatus(200)
             ->assertJsonCount(5, 'data');
});

test('filtra leads por estágio', function () {
    [$usuario, $token] = usuarioAutenticado();

    Lead::factory()->count(3)->create([
        'estagio' => 'contato',
        'criado_por' => $usuario->id,
    ]);
    Lead::factory()->count(2)->create([
        'estagio' => 'proposta',
        'criado_por' => $usuario->id,
    ]);

    $resposta = $this->withToken($token)
                     ->getJson('/api/leads?estagio=contato');

    $resposta->assertStatus(200)
             ->assertJsonCount(3, 'data');
});

test('busca leads por nome', function () {
    [$usuario, $token] = usuarioAutenticado();

    Lead::factory()->create([
        'nome' => 'TechCorp Sistemas',
        'criado_por' => $usuario->id,
    ]);
    Lead::factory()->create([
        'nome' => 'Outra Empresa',
        'criado_por' => $usuario->id,
    ]);

    $resposta = $this->withToken($token)
                     ->getJson('/api/leads?busca=TechCorp');

    $resposta->assertStatus(200)
             ->assertJsonCount(1, 'data');
});

// ─── VER LEAD ─────────────────────────────────────────────────────────────────

test('retorna detalhes de lead existente', function () {
    [$usuario, $token] = usuarioAutenticado();

    $lead = Lead::factory()->create(['criado_por' => $usuario->id]);

    $resposta = $this->withToken($token)
                     ->getJson("/api/leads/{$lead->id}");

    $resposta->assertStatus(200)
             ->assertJsonFragment(['id' => $lead->id]);
});

test('retorna 404 para lead inexistente', function () {
    [$usuario, $token] = usuarioAutenticado();

    $resposta = $this->withToken($token)
                     ->getJson('/api/leads/99999');

    $resposta->assertStatus(404);
});

// ─── ATUALIZAR LEAD ───────────────────────────────────────────────────────────

test('atualiza dados do lead', function () {
    [$usuario, $token] = usuarioAutenticado();

    $lead = Lead::factory()->create(['criado_por' => $usuario->id]);

    $resposta = $this->withToken($token)
                     ->putJson("/api/leads/{$lead->id}", [
                         'nome' => 'Nome Atualizado',
                         'valor_estimado' => 25000,
                     ]);

    $resposta->assertStatus(200)
             ->assertJsonFragment(['nome' => 'Nome Atualizado']);
});

// ─── EXCLUIR LEAD ─────────────────────────────────────────────────────────────

test('exclui lead com soft delete', function () {
    [$usuario, $token] = usuarioAutenticado('gerente');

    $lead = Lead::factory()->create(['criado_por' => $usuario->id]);

    $resposta = $this->withToken($token)
                     ->deleteJson("/api/leads/{$lead->id}");

    $resposta->assertStatus(204);

    $this->assertSoftDeleted('leads', ['id' => $lead->id]);
});

// ─── MOVER NO PIPELINE ────────────────────────────────────────────────────────

test('move lead para próximo estágio', function () {
    [$usuario, $token] = usuarioAutenticado();

    $lead = Lead::factory()->create([
        'estagio' => 'novo',
        'criado_por' => $usuario->id,
    ]);

    $resposta = $this->withToken($token)
                     ->patchJson("/api/leads/{$lead->id}/estagio", [
                         'estagio' => 'contato',
                     ]);

    $resposta->assertStatus(200)
             ->assertJsonFragment(['estagio' => 'contato']);
});

test('não move lead já fechado', function () {
    [$usuario, $token] = usuarioAutenticado();

    $lead = Lead::factory()->create([
        'estagio' => 'ganho',
        'criado_por' => $usuario->id,
    ]);

    $resposta = $this->withToken($token)
                     ->patchJson("/api/leads/{$lead->id}/estagio", [
                         'estagio' => 'contato',
                     ]);

    $resposta->assertStatus(409);
});

test('não move para estágio inválido', function () {
    [$usuario, $token] = usuarioAutenticado();

    $lead = Lead::factory()->create([
        'estagio' => 'novo',
        'criado_por' => $usuario->id,
    ]);

    $resposta = $this->withToken($token)
                     ->patchJson("/api/leads/{$lead->id}/estagio", [
                         'estagio' => 'fake',
                     ]);

    $resposta->assertStatus(422);
});
