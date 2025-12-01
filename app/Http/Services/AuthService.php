<?php

namespace App\Http\Services;

use Exception;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login($request)
    {
        $credentials = filter_var($request->email, FILTER_VALIDATE_EMAIL)
            ? ['email' => $request->email, 'password' => $request->password]
            : ['name' => $request->email, 'password' => $request->password];

        if (! Auth::attempt($credentials)) {
            throw new Exception('Email atau password salah');
        }

        $user = Auth::user();

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => $this->formatUserData($user),
        ];
    }

    public function getUser($request)
    {
        return $this->formatUserData($request->user());
    }

    private function formatUserData($user)
    {
        $role = $user->roles->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role ? $role->name : 'guest',
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];
    }

    public function logout($request)
    {
        $request->user()->currentAccessToken()->delete();

        return true;
    }
}
