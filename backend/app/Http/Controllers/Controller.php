<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="KoraCRM API",
 *     version="1.0.0",
 *     description="API REST do KoraCRM — gestão de leads, pipeline de vendas e tarefas."
 * )
 *
 * @OA\Server(
 *     url="http://localhost",
 *     description="Ambiente local (Docker)"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Token de acesso emitido pelo endpoint /api/auth/login"
 * )
 */
abstract class Controller
{
}
