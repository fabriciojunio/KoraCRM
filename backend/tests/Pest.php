<?php

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Vincula o TestCase do Laravel (boota a aplicação) e aplica RefreshDatabase.
uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class, RefreshDatabase::class)->in('Unit');

// Helper global para criar usuário autenticado nos testes
function usuarioTest(array $atributos = []): Usuario
{
    return Usuario::create(array_merge([
        'nome' => 'Usuário Teste',
        'email' => 'teste@koracrm.com.br',
        'senha' => Hash::make('senha123456'),
        'perfil' => 'vendedor',
        'ativo' => true,
    ], $atributos));
}
