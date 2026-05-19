<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'perfil',
        'ativo',
        'avatar',
    ];

    protected $hidden = [
        'senha',
        'remember_token',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ultimo_acesso' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function leadsResponsavel(): HasMany
    {
        return $this->hasMany(Lead::class, 'responsavel_id');
    }

    public function leadsCriados(): HasMany
    {
        return $this->hasMany(Lead::class, 'criado_por');
    }

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class, 'responsavel_id');
    }

    public function isAdmin(): bool
    {
        return $this->perfil === 'admin';
    }

    public function isGerente(): bool
    {
        return in_array($this->perfil, ['admin', 'gerente']);
    }

    public function isVendedor(): bool
    {
        return $this->perfil === 'vendedor';
    }
}
