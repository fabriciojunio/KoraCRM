<?php

use App\Models\Arquivo;
use App\Models\Lead;
use App\Models\Usuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function contaComLead(string $perfil = 'gerente', string $email = 'arquivos@koracrm.com.br'): array
{
    $usuario = Usuario::create([
        'nome' => 'Conta de anexos',
        'email' => $email,
        'senha' => Hash::make('senha123456'),
        'perfil' => $perfil,
        'ativo' => true,
    ]);

    $lead = Lead::create([
        'nome' => 'Lead com anexo',
        'estagio' => 'novo',
        'criado_por' => $usuario->id,
        'responsavel_id' => $usuario->id,
    ]);

    return [$usuario, $lead, $usuario->createToken('api-token')->plainTextToken];
}

test('anexa um PDF ao lead e devolve o tamanho formatado', function () {
    Storage::fake('local');
    [, $lead, $token] = contaComLead();

    $resposta = $this->withToken($token)->postJson("/api/leads/{$lead->id}/arquivos", [
        'arquivo' => UploadedFile::fake()->create('proposta.pdf', 2048, 'application/pdf'),
    ]);

    $resposta->assertCreated()
        ->assertJsonPath('nome_original', 'proposta.pdf')
        ->assertJsonStructure(['id', 'tamanho', 'tamanho_formatado', 'mime_type', 'url', 'enviado_em']);

    expect(Arquivo::where('lead_id', $lead->id)->count())->toBe(1);
});

test('o envio do anexo fica registrado no histórico do lead', function () {
    Storage::fake('local');
    [, $lead, $token] = contaComLead(email: 'arquivo.historico@koracrm.com.br');

    $this->withToken($token)->postJson("/api/leads/{$lead->id}/arquivos", [
        'arquivo' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
    ])->assertCreated();

    expect($lead->historico()->where('tipo', 'arquivo_enviado')->exists())->toBeTrue();
});

test('o nome do arquivo no disco não é o nome que o cliente mandou', function () {
    Storage::fake('local');
    [, $lead, $token] = contaComLead(email: 'arquivo.nome@koracrm.com.br');

    $this->withToken($token)->postJson("/api/leads/{$lead->id}/arquivos", [
        'arquivo' => UploadedFile::fake()->create('../../passwd.pdf', 10, 'application/pdf'),
    ])->assertCreated();

    $caminho = Arquivo::where('lead_id', $lead->id)->value('caminho');

    expect($caminho)->toStartWith("leads/{$lead->id}/")
        ->and($caminho)->not->toContain('..')
        ->and($caminho)->not->toContain('passwd');
});

test('tipo de arquivo fora da lista é recusado', function () {
    Storage::fake('local');
    [, $lead, $token] = contaComLead(email: 'arquivo.tipo@koracrm.com.br');

    $this->withToken($token)
        ->postJson("/api/leads/{$lead->id}/arquivos", [
            'arquivo' => UploadedFile::fake()->create('script.php', 10, 'application/x-httpd-php'),
        ])
        ->assertStatus(422)
        ->assertJsonPath('mensagem', 'Tipo de arquivo não permitido.');

    expect(Arquivo::where('lead_id', $lead->id)->count())->toBe(0);
});

test('enviar sem arquivo é recusado', function () {
    [, $lead, $token] = contaComLead(email: 'arquivo.vazio@koracrm.com.br');

    $this->withToken($token)
        ->postJson("/api/leads/{$lead->id}/arquivos", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('arquivo');
});

test('anexar em lead inexistente devolve 404', function () {
    [, , $token] = contaComLead(email: 'arquivo.404@koracrm.com.br');

    $this->withToken($token)->postJson('/api/leads/9999/arquivos', [
        'arquivo' => UploadedFile::fake()->create('nota.pdf', 10, 'application/pdf'),
    ])->assertNotFound();
});

test('vendedor não anexa arquivo em lead de outro', function () {
    Storage::fake('local');
    [, $lead] = contaComLead(email: 'arquivo.dono@koracrm.com.br');

    $intruso = Usuario::create([
        'nome' => 'Vendedor intruso',
        'email' => 'arquivo.intruso@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo' => true,
    ]);

    $this->withToken($intruso->createToken('api-token')->plainTextToken)
        ->postJson("/api/leads/{$lead->id}/arquivos", [
            'arquivo' => UploadedFile::fake()->create('curioso.pdf', 10, 'application/pdf'),
        ])
        ->assertForbidden();
});

test('o tamanho é formatado na unidade que cabe', function () {
    $emBytes = new Arquivo(['tamanho' => 800]);
    $emKilobytes = new Arquivo(['tamanho' => 2048]);
    $emMegabytes = new Arquivo(['tamanho' => 3 * 1024 * 1024]);

    expect($emBytes->tamanhoFormatado())->toBe('800 B')
        ->and($emKilobytes->tamanhoFormatado())->toBe('2 KB')
        ->and($emMegabytes->tamanhoFormatado())->toBe('3 MB');
});
