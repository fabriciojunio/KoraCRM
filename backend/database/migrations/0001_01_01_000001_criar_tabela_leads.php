<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('email', 150)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('empresa', 150)->nullable();
            $table->string('cargo', 100)->nullable();
            $table->enum('estagio', ['novo', 'contato', 'proposta', 'ganho', 'perdido'])
                  ->default('novo');
            $table->decimal('valor_estimado', 15, 2)->nullable();
            $table->string('origem', 50)->nullable()
                  ->comment('site, indicacao, linkedin, evento, outros');
            $table->text('observacoes')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('responsavel_id')->nullable();
            $table->unsignedBigInteger('criado_por');
            $table->timestamp('data_fechamento')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('responsavel_id')
                  ->references('id')
                  ->on('usuarios')
                  ->nullOnDelete();

            $table->foreign('criado_por')
                  ->references('id')
                  ->on('usuarios');

            $table->index('estagio');
            $table->index(['responsavel_id', 'estagio']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
