<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        $usuario = Usuario::first() ?? Usuario::factory()->create();

        return [
            'nome' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'telefone' => $this->faker->phoneNumber(),
            'empresa' => $this->faker->company(),
            'cargo' => $this->faker->jobTitle(),
            'estagio' => $this->faker->randomElement(Lead::ESTAGIOS),
            'valor_estimado' => $this->faker->randomFloat(2, 1000, 100000),
            'origem' => $this->faker->randomElement([
                'site', 'indicacao', 'linkedin', 'evento', 'outros',
            ]),
            'observacoes' => $this->faker->optional()->paragraph(),
            'tags' => $this->faker->optional()->randomElements(
                ['vip', 'tech', 'urgente', 'follow-up', 'parceiro'],
                rand(0, 3)
            ),
            'responsavel_id' => $usuario->id,
            'criado_por' => $usuario->id,
        ];
    }

    // Estado: lead no estágio novo
    public function novo(): static
    {
        return $this->state(['estagio' => 'novo']);
    }

    // Estado: lead ganho
    public function ganho(): static
    {
        return $this->state([
            'estagio' => 'ganho',
            'data_fechamento' => now(),
        ]);
    }

    // Estado: lead perdido
    public function perdido(): static
    {
        return $this->state([
            'estagio' => 'perdido',
            'data_fechamento' => now(),
        ]);
    }
}
