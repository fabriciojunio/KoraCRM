<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Arquivo extends Model
{
    protected $table = 'arquivos';

    protected $fillable = [
        'lead_id',
        'nome_original',
        'caminho',
        'disco',
        'tamanho',
        'mime_type',
        'enviado_por',
    ];

    protected $casts = [
        'tamanho' => 'integer',
    ];

    public const TIPOS_PERMITIDOS = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public const TAMANHO_MAXIMO = 10 * 1024 * 1024;

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function enviador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'enviado_por');
    }

    public function urlAcesso(): string
    {
        if ($this->disco === 's3') {
            return Storage::disk('s3')->temporaryUrl($this->caminho, now()->addHour());
        }

        // Disco local não é público: entrega via rota autenticada da API.
        return route('leads.arquivos.download', [
            'lead' => $this->lead_id,
            'arquivo' => $this->id,
        ]);
    }

    public function tamanhoFormatado(): string
    {
        $bytes = $this->tamanho;

        if ($bytes < 1024) {
            return "{$bytes} B";
        } elseif ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' KB';
        } else {
            return round($bytes / (1024 * 1024), 1).' MB';
        }
    }
}
