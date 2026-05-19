<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leads';

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'empresa',
        'cargo',
        'estagio',
        'valor_estimado',
        'origem',
        'observacoes',
        'tags',
        'responsavel_id',
        'criado_por',
        'data_fechamento',
    ];

    protected $casts = [
        'tags' => 'array',
        'valor_estimado' => 'decimal:2',
        'data_fechamento' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const ESTAGIOS = ['novo', 'contato', 'proposta', 'ganho', 'perdido'];

    public const ESTAGIOS_FECHADOS = ['ganho', 'perdido'];

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_id');
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'criado_por');
    }

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class, 'lead_id');
    }

    public function historico(): HasMany
    {
        return $this->hasMany(HistoricoLead::class, 'lead_id')
                    ->orderByDesc('created_at');
    }

    public function arquivos(): HasMany
    {
        return $this->hasMany(Arquivo::class, 'lead_id');
    }

    public function podeMovarPara(string $novoEstagio): bool
    {
        if (! in_array($novoEstagio, self::ESTAGIOS)) {
            return false;
        }

        if ($this->estaFechado()) {
            return false;
        }

        return true;
    }

    public function estaFechado(): bool
    {
        return in_array($this->estagio, self::ESTAGIOS_FECHADOS);
    }

    public function estagio(): string
    {
        return $this->estagio;
    }

    public function scopePorEstagio($query, string $estagio)
    {
        return $query->where('estagio', $estagio);
    }

    public function scopePorResponsavel($query, int $usuarioId)
    {
        return $query->where('responsavel_id', $usuarioId);
    }

    public function scopeAtivos($query)
    {
        return $query->whereNotIn('estagio', self::ESTAGIOS_FECHADOS);
    }
}
