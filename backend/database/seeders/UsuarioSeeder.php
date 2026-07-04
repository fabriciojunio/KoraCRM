<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        // Senha por perfil: usa a variável de ambiente quando definida,
        // caso contrário gera uma senha forte aleatória (nunca fixa/fraca).
        $senhas = [
            'admin' => $this->resolverSenha('SEED_ADMIN_PASSWORD'),
            'gerente' => $this->resolverSenha('SEED_GERENTE_PASSWORD'),
            'vendedor' => $this->resolverSenha('SEED_VENDEDOR_PASSWORD'),
        ];

        $usuarios = [
            ['nome' => 'Administrador',   'email' => 'admin@koracrm.com.br',   'perfil' => 'admin'],
            ['nome' => 'Gerente Vendas',  'email' => 'gerente@koracrm.com.br', 'perfil' => 'gerente'],
            ['nome' => 'Carlos Vendedor', 'email' => 'carlos@koracrm.com.br',  'perfil' => 'vendedor'],
            ['nome' => 'Ana Vendedora',   'email' => 'ana@koracrm.com.br',     'perfil' => 'vendedor'],
        ];

        foreach ($usuarios as $dados) {
            Usuario::create([
                'nome' => $dados['nome'],
                'email' => $dados['email'],
                'senha' => Hash::make($senhas[$dados['perfil']]['valor']),
                'perfil' => $dados['perfil'],
                'ativo' => true,
            ]);
        }

        $this->exibirSenhasGeradas($senhas);
    }

    /**
     * @return array{valor: string, gerada: bool}
     */
    private function resolverSenha(string $envKey): array
    {
        $doAmbiente = env($envKey);

        if (is_string($doAmbiente) && $doAmbiente !== '') {
            return ['valor' => $doAmbiente, 'gerada' => false];
        }

        return ['valor' => Str::password(20), 'gerada' => true];
    }

    /**
     * @param  array<string, array{valor: string, gerada: bool}>  $senhas
     */
    private function exibirSenhasGeradas(array $senhas): void
    {
        $geradas = array_filter($senhas, fn ($s) => $s['gerada']);

        if (empty($geradas) || ! $this->command) {
            return;
        }

        $this->command->warn('Senhas geradas automaticamente (guarde agora; não serão exibidas de novo):');

        foreach ($geradas as $perfil => $senha) {
            $this->command->line("  perfil {$perfil}  =>  {$senha['valor']}");
        }

        $this->command->warn('Defina SEED_ADMIN_PASSWORD / SEED_GERENTE_PASSWORD / SEED_VENDEDOR_PASSWORD no .env para controlar as senhas.');
    }
}
