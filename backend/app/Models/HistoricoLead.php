<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoLead extends Model
{
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

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
