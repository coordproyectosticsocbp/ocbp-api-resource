<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //
    protected function guard()
    {
        return Auth::guard();
    }

    public function Login(Request $request)
    {
        $request->validate([
            'userName' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('userName', 'password'))) {
            return response()->json([
                Auth::user()
            ], 200);
        }

        throw ValidationException::withMessages([
            'userName' => ['Credenciales Incorrectas'],
        ]);

    }

    public function Logout()
    {
        Auth::logout();
    }
}
