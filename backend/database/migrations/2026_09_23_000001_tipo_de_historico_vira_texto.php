<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * O tipo do histórico era enum no banco. Cada evento novo (agora a
 * anonimização da LGPD) virava alteração de estrutura numa tabela que só
 * cresce. Passa a ser texto, e a lista válida fica em HistoricoLead::TIPOS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historico_leads', function (Blueprint $table) {
            $table->string('tipo', 40)->change();
        });
    }

    public function down(): void
    {
        Schema::table('historico_leads', function (Blueprint $table) {
            $table->enum('tipo', [
                'criacao',
                'atualizacao',
                'mudanca_estagio',
                'comentario',
                'tarefa_criada',
                'arquivo_enviado',
            ])->change();
        });
    }
};
