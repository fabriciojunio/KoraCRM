<?php

use App\Models\HistoricoLead;
use App\Models\Lead;
use App\Models\Tarefa;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

function contaComPerfil(string $perfil, string $email): array
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

function leadComDadoPessoal(Usuario $dono): Lead
{
    return Lead::create([
        'nome' => 'Marina Aguiar',
        'email' => 'marina@empresa.com.br',
        'telefone' => '(14) 3456-7890',
        'empresa' => 'Tech Solutions Ltda',
        'cargo' => 'Diretora de TI',
        'estagio' => 'ganho',
        'valor_estimado' => 18500,
        'observacoes' => 'Celular pessoal 14 99999-0000.',
        'criado_por' => $dono->id,
        'responsavel_id' => $dono->id,
    ]);
}

test('o titular consegue ver tudo que o sistema guarda sobre ele', function () {
    [$gerente, $token] = contaComPerfil('gerente', 'gerente.lgpd@koracrm.com.br');
    $lead = leadComDadoPessoal($gerente);

    $this->withToken($token)
        ->getJson("/api/leads/{$lead->id}/dados-pessoais")
        ->assertOk()
        ->assertJsonPath('lead.nome', 'Marina Aguiar')
        ->assertJsonPath('lead.email', 'marina@empresa.com.br')
        ->assertJsonStructure(['lead', 'aberto_em', 'historico', 'tarefas', 'arquivos']);
});

test('anonimizar apaga o dado pessoal e mantém o valor fechado', function () {
    [$gerente, $token] = contaComPerfil('gerente', 'gerente.anon@koracrm.com.br');
    $lead = leadComDadoPessoal($gerente);

    $this->withToken($token)
        ->deleteJson("/api/leads/{$lead->id}/dados-pessoais")
        ->assertOk()
        ->assertJsonPath('mensagem', 'Dados pessoais removidos. O histórico comercial foi mantido.');

    $lead->refresh();

    expect($lead->nome)->toBe('Titular anonimizado')
        ->and($lead->email)->toBeNull()
        ->and($lead->telefone)->toBeNull()
        ->and($lead->empresa)->toBeNull()
        ->and($lead->cargo)->toBeNull()
        ->and($lead->observacoes)->toBeNull()
        ->and($lead->anonimizado_em)->not->toBeNull()
        ->and((float) $lead->valor_estimado)->toBe(18500.0)
        ->and($lead->estagio)->toBe('ganho');
});

test('anonimização limpa histórico e tarefas sem perder o valor comercial', function () {
    [$gerente, $token] = contaComPerfil('gerente', 'gerente.rastro@koracrm.com.br');
    $lead = leadComDadoPessoal($gerente);
    $historico = HistoricoLead::create([
        'lead_id' => $lead->id,
        'usuario_id' => $gerente->id,
        'tipo' => 'comentario',
        'descricao' => 'Ligar para Marina no telefone 14 99999-0000',
        'dados_anteriores' => ['nome' => 'Marina Aguiar', 'estagio' => 'novo'],
        'dados_novos' => ['email' => 'marina@empresa.com.br', 'valor_estimado' => 18500],
        'created_at' => now(),
    ]);
    $tarefa = Tarefa::create([
        'lead_id' => $lead->id,
        'responsavel_id' => $gerente->id,
        'titulo' => 'Telefonar para Marina Aguiar',
        'descricao' => 'E-mail marina@empresa.com.br',
    ]);

    $this->withToken($token)->deleteJson("/api/leads/{$lead->id}/dados-pessoais")->assertOk();

    expect($historico->fresh()->descricao)->not->toContain('Marina')
        ->and($historico->fresh()->dados_anteriores)->toBe(['estagio' => 'novo'])
        ->and($historico->fresh()->dados_novos)->toBe(['valor_estimado' => 18500])
        ->and($tarefa->fresh()->titulo)->not->toContain('Marina')
        ->and($tarefa->fresh()->descricao)->toBeNull()
        ->and((float) $lead->fresh()->valor_estimado)->toBe(18500.0);

    $this->withToken($token)->putJson("/api/leads/{$lead->id}", ['nome' => 'Marina de novo'])
        ->assertForbidden();
    $this->withToken($token)->putJson("/api/tarefas/{$tarefa->id}", ['descricao' => 'Marina de novo'])
        ->assertForbidden();
    $this->withToken($token)->postJson('/api/tarefas', [
        'titulo' => 'Marina de novo', 'lead_id' => $lead->id,
    ])->assertStatus(409);
});

test('a anonimização fica registrada no histórico do lead', function () {
    [$gerente, $token] = contaComPerfil('gerente', 'gerente.hist@koracrm.com.br');
    $lead = leadComDadoPessoal($gerente);

    $this->withToken($token)->deleteJson("/api/leads/{$lead->id}/dados-pessoais");

    expect($lead->historico()->where('tipo', 'anonimizacao')->exists())->toBeTrue();
});

test('anonimizar duas vezes não sobrescreve a data do primeiro pedido', function () {
    [$gerente, $token] = contaComPerfil('gerente', 'gerente.duas@koracrm.com.br');
    $lead = leadComDadoPessoal($gerente);

    $this->withToken($token)->deleteJson("/api/leads/{$lead->id}/dados-pessoais");
    $primeiraData = $lead->fresh()->anonimizado_em;

    $this->travel(2)->minutes();
    $this->withToken($token)->deleteJson("/api/leads/{$lead->id}/dados-pessoais")->assertOk();

    expect($lead->fresh()->anonimizado_em->toISOString())->toBe($primeiraData->toISOString());
});

test('vendedor não anonimiza lead, nem o próprio', function () {
    [$vendedor, $token] = contaComPerfil('vendedor', 'vendedor.lgpd@koracrm.com.br');
    $lead = leadComDadoPessoal($vendedor);

    $this->withToken($token)
        ->deleteJson("/api/leads/{$lead->id}/dados-pessoais")
        ->assertForbidden();

    expect($lead->fresh()->nome)->toBe('Marina Aguiar');
});

test('pedido de dados de lead inexistente devolve 404', function () {
    [, $token] = contaComPerfil('gerente', 'gerente.404@koracrm.com.br');

    $this->withToken($token)
        ->getJson('/api/leads/9999/dados-pessoais')
        ->assertNotFound();
});
