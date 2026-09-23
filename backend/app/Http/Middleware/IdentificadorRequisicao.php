<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Carimba cada requisição com um identificador e devolve ele no cabeçalho.
 *
 * É o que permite pegar o código que o cliente leu na tela de erro e achar a
 * linha exata no log, sem procurar por horário.
 */
class IdentificadorRequisicao
{
    public const CABECALHO = 'X-Request-Id';

    public function handle(Request $request, Closure $next): Response
    {
        $identificador = $request->header(self::CABECALHO);

        if (! $identificador || ! preg_match('/^[A-Za-z0-9\-]{8,64}$/', $identificador)) {
            $identificador = (string) Str::uuid();
        }

        $request->headers->set(self::CABECALHO, $identificador);

        Log::shareContext([
            'requisicao' => $identificador,
            'usuario' => $request->user()?->id,
        ]);

        $resposta = $next($request);
        $resposta->headers->set(self::CABECALHO, $identificador);

        return $resposta;
    }
}
