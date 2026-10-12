<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GarantirUsuarioAtivo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->ativo) {
            return response()->json(['mensagem' => 'Conta inativa.'], 401);
        }

        return $next($request);
    }
}
