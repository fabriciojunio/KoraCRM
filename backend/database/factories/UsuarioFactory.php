<?php

namespace Database\Factories;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    public function definition(): array
    {
        return [
            'nome'   => $this->faker->name(),
            'email'  => $this->faker->unique()->safeEmail(),
            'senha'  => Hash::make('senha123456'),
            'perfil' => 'vendedor',
            'ativo'  => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(['perfil' => 'admin']);
    }

    public function gerente(): static
    {
        return $this->state(['perfil' => 'gerente']);
    }

    public function inativo(): static
    {
        return $this->state(['ativo' => false]);
    }
}
