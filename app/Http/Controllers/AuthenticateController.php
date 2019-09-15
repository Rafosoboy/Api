<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\User;
use App\PasswordReset;

class AuthenticateController extends Controller
{
    public function init() 
    {
        $user = Auth::user();
        return response()->json(['user' => $user], 200);
    }

    public function login(Request $request) 
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            $user = Auth::user();
            return response()->json(['user' => $user], 200);
        }
        return response()->json([
            'error' => "Login e/ou password incorretos"
        ], 401);
    }

    public function register(Request $request) 
    {

        $user = User::where('email', $request->email)->first();

        if(isset($user->id)) {
            return response()->json([
                'error' => 'Usuário existente no sistema'
            ], 401);
        }

        $user = new User;

        $user->email = $request->email;
        $user->name = $request->name;
        $user->password = bcrypt($request->password);
        $user->save();

        Auth::login($user);

        return response()->json($user, 200);
    }

    public function logout() 
    {
        Auth::logout();
    }

    public function sendResetLink(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if(!isset($user)) {
            return response()->json([
                'error' => 'Erro ao enviar o e-mail'
            ], 401);
        }

        $reset = new PasswordReset;
        $reset->email = $user->email;
        $reset->token = $user->password;
        $reset->created_at = date('Y-m-d H:m:i');
        $reset->save();

        $code = md5("{$user->email}{$reset->created_at}");

        Mail::send('mail',
                   ['name' => $user->name,
                    'body' => 'Segue o link para resetar sua conta:',
                    'link' => '127.0.0.1:8080/resetPassword/' . $code
                ], function($messege) use ($user) {
            $messege->to($user->email, '')
                    ->subject('[Redefinição de Senha]');
            $messege->from('send@developer.com', '');
        });

        return response()->json(true, 200);
    }

    public function resetPassword(Request $request)
    {
        $passwordReset = PasswordReset::where(DB::raw("MD5(email||TO_CHAR(created_at, 'YYYY-MM-DD HH24:MM:SS'))"), $request->credential)->first();

        if(!isset($passwordReset)) {
            return response()->json([
                'error' => 'Credencial Inválida'
            ], 401);
        }

        $user = User::where('email', $passwordReset->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();

        return response()->json($user, 200);
    }
}
