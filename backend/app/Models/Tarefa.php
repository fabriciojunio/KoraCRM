<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $titulo
 * @property string|null $descricao
 * @property Carbon|null $prazo
 * @property bool $concluida
 * @property Carbon|null $concluida_em
 * @property string $prioridade
 * @property int $lead_id
 * @property int|null $responsavel_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Tarefa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tarefas';

    protected $fillable = [
        'titulo',
        'descricao',
        'prazo',
        'concluida',
        'concluida_em',
        'prioridade',
        'lead_id',
        'responsavel_id',
    ];

    protected $casts = [
        'concluida' => 'boolean',
        'prazo' => 'datetime',
        'concluida_em' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /** @return BelongsTo<Lead, $this> */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    /** @return BelongsTo<Usuario, $this> */
    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'responsavel_id');
    }

    public function concluir(): void
    {
        $this->concluida = true;
        $this->concluida_em = now();
        $this->save();
    }

    public function estaAtrasada(): bool
    {
        return ! $this->concluida
            && $this->prazo !== null
            && $this->prazo->isPast();
    }
}
