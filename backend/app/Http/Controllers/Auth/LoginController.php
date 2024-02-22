<?php

namespace App\Http\Controllers\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User; // Corrigido para usar o namespace correto
use Illuminate\Support\Str; // Adicionado para usar a função str_random

class LoginController extends Controller
{

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login'); // Redireciona em caso de erro
        }

        // Verifica se o usuário já existe no banco de dados
        $existingUser = User::where('email', $user->email)->first();

        if ($existingUser) {
            // Se o usuário existir, faça login com ele
            Auth::login($existingUser);
        } else {
            // Se o usuário não existir, crie um novo usuário com os dados retornados pelo Google
            $newUser = new User();
            $newUser->name = $user->name;
            $newUser->email = $user->email;
            // Defina uma senha aleatória para evitar erros
            $newUser->password = bcrypt(Str::random(16)); // Corrigido para usar a função Str::random
            $newUser->save();
            // Faça login com o novo usuário
            Auth::login($newUser);
        }

        // Redirecione para a página desejada após o login
        return redirect('/home');
    }

    
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = JWTAuth::fromUser($user);
        return response()->json([
            'user' => $user,
            'token' => $token
        ], 200);
    } else {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
}
