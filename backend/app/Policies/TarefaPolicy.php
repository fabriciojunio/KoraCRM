<?php

namespace App\Policies;

use App\Models\Tarefa;
use App\Models\Usuario;

class TarefaPolicy
{
    public function view(Usuario $usuario, Tarefa $tarefa): bool
    {
        if ($usuario->isGerente()) return true;
        return $tarefa->responsavel_id === $usuario->id;
    }

    public function update(Usuario $usuario, Tarefa $tarefa): bool
    {
        if ($usuario->isGerente()) return true;
        return $tarefa->responsavel_id === $usuario->id;
    }

    public function delete(Usuario $usuario, Tarefa $tarefa): bool
    {
        if ($usuario->isGerente()) return true;
        return $tarefa->responsavel_id === $usuario->id;
    }

    public function concluir(Usuario $usuario, Tarefa $tarefa): bool
    {
        if ($usuario->isGerente()) return true;
        return $tarefa->responsavel_id === $usuario->id;
    }
}
