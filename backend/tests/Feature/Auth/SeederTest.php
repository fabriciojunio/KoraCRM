<?php

use App\Models\Usuario;
use Database\Seeders\UsuarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('seeder não define senha fraca fixa para o admin', function () {
    $this->seed(UsuarioSeeder::class);

    $admin = Usuario::where('email', 'admin@koracrm.com.br')->first();

    expect($admin)->not->toBeNull();
    expect(Hash::check('admin123456', $admin->senha))->toBeFalse();
});

test('seeder usa a senha da variável de ambiente quando definida', function () {
    putenv('SEED_ADMIN_PASSWORD=Sup3rF0rte!Env#2026');
    $_ENV['SEED_ADMIN_PASSWORD'] = 'Sup3rF0rte!Env#2026';

    $this->seed(UsuarioSeeder::class);

    $admin = Usuario::where('email', 'admin@koracrm.com.br')->first();

    expect(Hash::check('Sup3rF0rte!Env#2026', $admin->senha))->toBeTrue();

    putenv('SEED_ADMIN_PASSWORD');
    unset($_ENV['SEED_ADMIN_PASSWORD']);
});

test('seeder cria os quatro usuários de exemplo', function () {
    $this->seed(UsuarioSeeder::class);

    expect(Usuario::count())->toBe(4);
});
