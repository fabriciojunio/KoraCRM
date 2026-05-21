<?php

namespace App\Application\Services;

use App\Models\HistoricoLead;

class RegistrarHistoricoService
{
    public function registrar(
        int $leadId,
        int $usuarioId,
        string $tipo,
        string $descricao,
        ?array $dadosAnteriores = null,
        ?array $dadosNovos = null,
    ): HistoricoLead {
        return HistoricoLead::create([
            'lead_id' => $leadId,
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'descricao' => $descricao,
            'dados_anteriores' => $dadosAnteriores,
            'dados_novos' => $dadosNovos,
            'created_at' => now(),
        ]);
    }
}
