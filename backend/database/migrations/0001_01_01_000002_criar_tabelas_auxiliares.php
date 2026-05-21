<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabela de tarefas
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->timestamp('prazo')->nullable();
            $table->boolean('concluida')->default(false);
            $table->timestamp('concluida_em')->nullable();
            $table->enum('prioridade', ['baixa', 'media', 'alta'])->default('media');
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('responsavel_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
            $table->foreign('responsavel_id')->references('id')->on('usuarios');

            $table->index(['lead_id', 'concluida']);
            $table->index(['responsavel_id', 'prazo']);
        });

        // Histórico de ações no lead
        Schema::create('historico_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('usuario_id');
            $table->enum('tipo', [
                'criacao',
                'atualizacao',
                'mudanca_estagio',
                'comentario',
                'tarefa_criada',
                'arquivo_enviado',
            ]);
            $table->string('descricao', 500);
            $table->json('dados_anteriores')->nullable();
            $table->json('dados_novos')->nullable();
            $table->timestamp('created_at');

            $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->index(['lead_id', 'created_at']);
        });

        // Arquivos anexados a leads
        Schema::create('arquivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->string('nome_original', 255);
            $table->string('caminho', 500);
            $table->string('disco', 20)->default('local')
                  ->comment('local ou s3');
            $table->unsignedInteger('tamanho')->comment('em bytes');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('enviado_por');
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->cascadeOnDelete();
            $table->foreign('enviado_por')->references('id')->on('usuarios');

            $table->index('lead_id');
        });

        // Log de auditoria de todas as ações do sistema
        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('acao', 100);
            $table->string('modelo', 100)->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();
            $table->json('dados')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at');

            $table->foreign('usuario_id')->references('id')->on('usuarios')->nullOnDelete();

            $table->index(['usuario_id', 'created_at']);
            $table->index(['modelo', 'modelo_id']);
            $table->index('acao');
        });

        // Tokens do Sanctum
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_auditoria');
        Schema::dropIfExists('arquivos');
        Schema::dropIfExists('historico_leads');
        Schema::dropIfExists('tarefas');
        Schema::dropIfExists('personal_access_tokens');
    }
};
