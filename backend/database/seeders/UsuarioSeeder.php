<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Admin padrão
        Usuario::create([
            'nome' => 'Administrador',
            'email' => 'admin@koracrm.com.br',
            'senha' => Hash::make('admin123456'),
            'perfil' => 'admin',
            'ativo' => true,
        ]);

        // Gerente
        Usuario::create([
            'nome' => 'Gerente Vendas',
            'email' => 'gerente@koracrm.com.br',
            'senha' => Hash::make('gerente123456'),
            'perfil' => 'gerente',
            'ativo' => true,
        ]);

        // Vendedores
        $vendedores = [
            ['nome' => 'Carlos Vendedor', 'email' => 'carlos@koracrm.com.br'],
            ['nome' => 'Ana Vendedora', 'email' => 'ana@koracrm.com.br'],
        ];

        foreach ($vendedores as $vendedor) {
            Usuario::create([
                'nome' => $vendedor['nome'],
                'email' => $vendedor['email'],
                'senha' => Hash::make('vendedor123456'),
                'perfil' => 'vendedor',
                'ativo' => true,
            ]);
        }
    }
}
