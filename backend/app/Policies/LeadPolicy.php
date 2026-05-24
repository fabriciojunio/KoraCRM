<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\Usuario;

class LeadPolicy
{
    public function view(Usuario $usuario, Lead $lead): bool
    {
        if ($usuario->isGerente()) return true;
        return $lead->responsavel_id === $usuario->id || $lead->criado_por === $usuario->id;
    }

    public function create(Usuario $usuario): bool
    {
        return true;
    }

    public function update(Usuario $usuario, Lead $lead): bool
    {
        if ($usuario->isGerente()) return true;
        return $lead->responsavel_id === $usuario->id || $lead->criado_por === $usuario->id;
    }

    public function delete(Usuario $usuario, Lead $lead): bool
    {
        return $usuario->isGerente();
    }

    public function upload(Usuario $usuario, Lead $lead): bool
    {
        if ($usuario->isGerente()) return true;
        return $lead->responsavel_id === $usuario->id || $lead->criado_por === $usuario->id;
    }
}
