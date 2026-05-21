<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('email', 150)->unique();
            $table->string('senha');
            $table->enum('perfil', ['admin', 'gerente', 'vendedor'])->default('vendedor');
            $table->boolean('ativo')->default(true);
            $table->string('avatar')->nullable();
            $table->timestamp('ultimo_acesso')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
