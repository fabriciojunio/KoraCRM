<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsuarioSeeder::class,
            LeadSeeder::class,
        ]);
    }
}
