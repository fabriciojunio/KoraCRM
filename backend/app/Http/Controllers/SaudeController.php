<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Sonda de saúde para o balanceador e para o monitoramento.
 *
 * Responde sem autenticação, então não diz versão de biblioteca, host de banco
 * nem contagem de registro: só se cada dependência respondeu.
 */
class SaudeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $dependencias = [
            'banco' => $this->respondeu(fn () => DB::select('select 1')),
            'cache' => $this->respondeu(function () {
                Cache::put('saude', '1', 10);

                return Cache::get('saude') === '1';
            }),
        ];

        $saudavel = ! in_array(false, $dependencias, true);

        return response()->json([
            'status' => $saudavel ? 'no ar' : 'degradado',
            'versao' => config('app.versao'),
            'dependencias' => $dependencias,
        ], $saudavel ? 200 : 503);
    }

    private function respondeu(callable $checagem): bool
    {
        try {
            return $checagem() !== false;
        } catch (Throwable) {
            return false;
        }
    }
}
