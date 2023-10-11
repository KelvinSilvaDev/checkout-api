<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;


class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Validar os dados de entrada
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Se a validação falhar, retornar uma resposta de erro
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Criar um novo usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Hash da senha
        ]);

        // Gerar um token JWT para o novo usuário
        $token = JWTAuth::fromUser($user);

        // Retornar uma resposta com o token
        return response()->json(['token' => $token], 201);
    }
}
