<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * Handle a login request to the application.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */


     public function login(Request $request)
     {
         $credentials = $request->only('email', 'password');
     
         if (Auth::attempt($credentials)) {
             $user = Auth::user();
             $token = JWTAuth::fromUser($user); // Gerar token JWT para o usuário autenticado
     
             return response()->json(['token' => $token]);
         }
     
         // Autenticação falhou
         return response()->json(['error' => 'Unauthorized'], 401);
     }
     


    /**
     * Handle a logout request from the application.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
