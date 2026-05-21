<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(name="Autenticação", description="Endpoints de autenticação")
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     tags={"Autenticação"},
     *     summary="Login com email e senha",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","senha"},
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="senha", type="string", minLength=8)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login realizado com sucesso"),
     *     @OA\Response(response=422, description="Credenciais inválidas")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = Usuario::where('email', $request->email)
                          ->where('ativo', true)
                          ->first();

        if (! $usuario || ! Hash::check($request->senha, $usuario->senha)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $usuario->update(['ultimo_acesso' => now()]);

        // mantém apenas um token ativo por usuário
        $usuario->tokens()->delete();

        $token = $usuario->createToken(
            'api-token',
            ['*'],
            now()->addDays(30)
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'usuario' => [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'perfil' => $usuario->perfil,
                'avatar' => $usuario->avatar,
            ],
            'expira_em' => now()->addDays(30)->toISOString(),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     tags={"Autenticação"},
     *     summary="Logout — invalida o token atual",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Logout realizado com sucesso")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensagem' => 'Logout realizado com sucesso.']);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/perfil",
     *     tags={"Autenticação"},
     *     summary="Dados do usuário autenticado",
     *     security={{"sanctum": {}}},
     *     @OA\Response(response=200, description="Dados do usuário")
     * )
     */
    public function perfil(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
