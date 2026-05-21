<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        // Depende de UsuarioSeeder já ter rodado — a LeadFactory associa
        // o lead ao primeiro usuário existente.
        Lead::factory()->count(20)->novo()->create();
        Lead::factory()->count(8)->state(['estagio' => 'contato'])->create();
        Lead::factory()->count(6)->state(['estagio' => 'proposta'])->create();
        Lead::factory()->count(5)->ganho()->create();
        Lead::factory()->count(3)->perdido()->create();
    }
}
