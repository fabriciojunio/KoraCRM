<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica cabeçalhos de segurança HTTP em todas as respostas da API.
 *
 * A API é servida como JSON, então a política de conteúdo pode ser
 * restritiva. A página do Swagger (documentação) precisa de uma política
 * mais permissiva para carregar seus próprios assets.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('X-XSS-Protection', '0');

        $response->headers->set('Content-Security-Policy', $this->politicaConteudo($request));

        // HSTS apenas sob HTTPS para não travar acesso local via HTTP.
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=63072000; includeSubDomains; preload'
            );
        }

        // Remove cabeçalhos que revelam a stack.
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    private function politicaConteudo(Request $request): string
    {
        // A documentação Swagger renderiza HTML com scripts/estilos próprios.
        if ($request->is('api/documentation', 'api/oauth2-callback', 'docs*')) {
            return "default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; "
                ."script-src 'self' 'unsafe-inline'; font-src 'self' data:; "
                ."frame-ancestors 'none'; base-uri 'self'";
        }

        // Respostas JSON puras: bloqueio máximo.
        return "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none'";
    }
}
