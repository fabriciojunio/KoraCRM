<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Vincula o TestCase do Laravel (boota a aplicação) e aplica RefreshDatabase.
uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class, RefreshDatabase::class)->in('Unit');

// Helper global para criar usuário autenticado nos testes
function usuarioTest(array $atributos = []): \App\Models\Usuario
{
    return \App\Models\Usuario::create(array_merge([
        'nome'   => 'Usuário Teste',
        'email'  => 'teste@koracrm.com.br',
        'senha'  => \Illuminate\Support\Facades\Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo'  => true,
    ], $atributos));
}
