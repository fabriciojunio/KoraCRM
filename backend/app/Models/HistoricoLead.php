<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $lead_id
 * @property int|null $usuario_id
 * @property string $tipo
 * @property string $descricao
 * @property array<string, mixed>|null $dados_anteriores
 * @property array<string, mixed>|null $dados_novos
 * @property Carbon $created_at
 */
class HistoricoLead extends Model
{
    public const TIPOS = [
        'criacao',
        'atualizacao',
        'mudanca_estagio',
        'comentario',
        'tarefa_criada',
        'arquivo_enviado',
        'anonimizacao',
    ];

    public $timestamps = false;

    protected $table = 'historico_leads';

    protected $fillable = [
        'lead_id',
        'usuario_id',
        'tipo',
        'descricao',
        'dados_anteriores',
        'dados_novos',
        'created_at',
    ];

    protected $casts = [
        'dados_anteriores' => 'array',
        'dados_novos' => 'array',
        'created_at' => 'datetime',
    ];

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
