<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(name="Usuários", description="Gestão de usuários (apenas admin/gerente)")
 */
class UsuarioController extends Controller
{
    /**
     * @OA\Get(path="/api/usuarios", tags={"Usuários"}, security={{"sanctum":{}}},
     *     summary="Lista todos os usuários",
     *     @OA\Response(response=200, description="Lista de usuários")
     * )
     */
    public function index(): JsonResponse
    {
        $usuarios = Usuario::select('id', 'nome', 'email', 'perfil', 'ativo', 'created_at')
            ->orderBy('nome')
            ->get();

        return response()->json($usuarios);
    }

    /**
     * @OA\Get(path="/api/usuarios/{id}", tags={"Usuários"}, security={{"sanctum":{}}},
     *     summary="Detalhes de um usuário",
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Dados do usuário"),
     *     @OA\Response(response=404, description="Usuário não encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $usuario = Usuario::select('id', 'nome', 'email', 'perfil', 'ativo', 'ultimo_acesso', 'created_at')
            ->find($id);

        if (! $usuario) {
            return response()->json(['mensagem' => 'Usuário não encontrado.'], 404);
        }

        return response()->json($usuario);
    }

    /**
     * @OA\Post(path="/api/usuarios", tags={"Usuários"}, security={{"sanctum":{}}},
     *     summary="Cria novo usuário",
     *     @OA\Response(response=201, description="Usuário criado")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'nome'   => ['required', 'string', 'max:100'],
            'email'  => ['required', 'email', 'max:150', 'unique:usuarios,email'],
            'senha'  => ['required', 'string', 'min:8', 'confirmed'],
            'perfil' => ['required', Rule::in(['admin', 'gerente', 'vendedor'])],
        ], [
            'email.unique'      => 'Este e-mail já está cadastrado.',
            'senha.min'         => 'A senha deve ter pelo menos 8 caracteres.',
            'senha.confirmed'   => 'A confirmação de senha não confere.',
            'perfil.in'         => 'Perfil inválido.',
        ]);

        $usuario = Usuario::create([
            'nome'  => $dados['nome'],
            'email' => $dados['email'],
            'senha' => Hash::make($dados['senha']),
            'perfil' => $dados['perfil'],
            'ativo' => true,
        ]);

        return response()->json($usuario->only(['id', 'nome', 'email', 'perfil', 'ativo']), 201);
    }

    /**
     * @OA\Put(path="/api/usuarios/{id}", tags={"Usuários"}, security={{"sanctum":{}}},
     *     summary="Atualiza usuário",
     *     @OA\Parameter(name="id", in="path", required=true),
     *     @OA\Response(response=200, description="Usuário atualizado")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $usuario = Usuario::find($id);

        if (! $usuario) {
            return response()->json(['mensagem' => 'Usuário não encontrado.'], 404);
        }

        $dados = $request->validate([
            'nome'   => ['sometimes', 'string', 'max:100'],
            'email'  => ['sometimes', 'email', 'max:150', Rule::unique('usuarios')->ignore($id)],
            'perfil' => ['sometimes', Rule::in(['admin', 'gerente', 'vendedor'])],
            'ativo'  => ['sometimes', 'boolean'],
            'senha'  => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (! empty($dados['senha'])) {
            $dados['senha'] = Hash::make($dados['senha']);
        } else {
            unset($dados['senha']);
        }

        $usuario->update($dados);

        return response()->json($usuario->fresh()->only(['id', 'nome', 'email', 'perfil', 'ativo']));
    }

    /**
     * @OA\Delete(path="/api/usuarios/{id}", tags={"Usuários"}, security={{"sanctum":{}}},
     *     summary="Desativa usuário",
     *     @OA\Response(response=200, description="Usuário desativado")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        // Impede que o admin se desative
        if ($request->user()->id === $id) {
            return response()->json(['mensagem' => 'Você não pode desativar sua própria conta.'], 403);
        }

        $usuario = Usuario::find($id);

        if (! $usuario) {
            return response()->json(['mensagem' => 'Usuário não encontrado.'], 404);
        }

        $usuario->update(['ativo' => false]);

        return response()->json(['mensagem' => 'Usuário desativado com sucesso.']);
    }
}
