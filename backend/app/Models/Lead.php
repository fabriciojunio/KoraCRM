<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nome
 * @property string|null $email
 * @property string|null $telefone
 * @property string|null $empresa
 * @property string|null $cargo
 * @property string $estagio
 * @property numeric-string|float $valor_estimado
 * @property string|null $origem
 * @property string|null $observacoes
 * @property array<int, string>|null $tags
 * @property int|null $responsavel_id
 * @property int|null $criado_por
 * @property Carbon|null $data_fechamento
 * @property Carbon|null $anonimizado_em
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
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
        'anonimizado_em' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const ESTAGIOS = ['novo', 'contato', 'proposta', 'ganho', 'perdido'];

    public const ESTAGIOS_FECHADOS = ['ganho', 'perdido'];

    /** @return BelongsTo<Usuario, $this> */
    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function criador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'criado_por');
    }

    /** @return HasMany<Tarefa, $this> */
    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class, 'lead_id');
    }

    /** @return HasMany<HistoricoLead, $this> */
    public function historico(): HasMany
    {
        return $this->hasMany(HistoricoLead::class, 'lead_id')
            ->orderByDesc('created_at');
    }

    /** @return HasMany<Arquivo, $this> */
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
