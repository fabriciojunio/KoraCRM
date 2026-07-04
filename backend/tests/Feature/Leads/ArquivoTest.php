<?php

use App\Models\Arquivo;
use App\Models\Lead;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function usuarioComArquivo(): array
{
    $dono = Usuario::create([
        'nome' => 'Dono do Lead',
        'email' => 'dono@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo' => true,
    ]);

    $lead = Lead::create([
        'nome' => 'Lead com Anexo',
        'estagio' => 'novo',
        'criado_por' => $dono->id,
        'responsavel_id' => $dono->id,
    ]);

    Storage::disk('local')->put("leads/{$lead->id}/doc.pdf", 'conteudo-do-arquivo');

    $arquivo = Arquivo::create([
        'lead_id' => $lead->id,
        'nome_original' => 'doc.pdf',
        'caminho' => "leads/{$lead->id}/doc.pdf",
        'disco' => 'local',
        'tamanho' => 19,
        'mime_type' => 'application/pdf',
        'enviado_por' => $dono->id,
    ]);

    return [$dono, $lead, $arquivo];
}

test('dono do lead baixa o arquivo anexo', function () {
    Storage::fake('local');
    [$dono, $lead, $arquivo] = usuarioComArquivo();
    $token = $dono->createToken('api-token')->plainTextToken;

    $resposta = $this->withToken($token)
        ->get("/api/leads/{$lead->id}/arquivos/{$arquivo->id}");

    $resposta->assertStatus(200);
    expect($resposta->headers->get('content-disposition'))->toContain('attachment');
});

test('vendedor sem acesso ao lead não baixa o arquivo', function () {
    Storage::fake('local');
    [$dono, $lead, $arquivo] = usuarioComArquivo();

    $outro = Usuario::create([
        'nome' => 'Intruso',
        'email' => 'intruso@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo' => true,
    ]);
    $token = $outro->createToken('api-token')->plainTextToken;

    $resposta = $this->withToken($token)
        ->getJson("/api/leads/{$lead->id}/arquivos/{$arquivo->id}");

    $resposta->assertStatus(403);
});

test('download de arquivo exige autenticação', function () {
    Storage::fake('local');
    [$dono, $lead, $arquivo] = usuarioComArquivo();

    $resposta = $this->getJson("/api/leads/{$lead->id}/arquivos/{$arquivo->id}");

    $resposta->assertStatus(401);
});

test('gerente baixa arquivo de lead de outro usuário', function () {
    Storage::fake('local');
    [$dono, $lead, $arquivo] = usuarioComArquivo();

    $gerente = Usuario::create([
        'nome' => 'Gerente',
        'email' => 'gestor@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'gerente',
        'ativo' => true,
    ]);
    $token = $gerente->createToken('api-token')->plainTextToken;

    $resposta = $this->withToken($token)
        ->get("/api/leads/{$lead->id}/arquivos/{$arquivo->id}");

    $resposta->assertStatus(200);
});
