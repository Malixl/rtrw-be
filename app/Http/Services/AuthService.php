<?php

namespace App\Http\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AuthService
{

    protected $model;
    protected $layananMandiriService;

    protected function resolveRole(User $user): string
    {
        return $user->getRoleNames()->first() ?? 'admin';
    }

    protected function resolvePermissions(User $user): array
    {
        return $user->getAllPermissions()->pluck('name')->toArray();
    }

    protected function formatUserPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $this->resolveRole($user),
            'permissions' => $this->resolvePermissions($user),
        ];
    }

    public function login($request)
    {
        DB::beginTransaction();
        try {
            $validate = $request->validate([
                'email' => 'required',
                'password' => 'required'
            ]);

            $credentials = filter_var($request->email, FILTER_VALIDATE_EMAIL)
                ? ['email' => $request->email, 'password' => $request->password]
                : ['name' => $request->email, 'password' => $request->password];

            if (!Auth::attempt($credentials)) {
                throw new Exception('Email atau password salah');
            }

            $user = User::where('name', $request->email)->orWhere('email', $request->email)->firstOrFail();

            $token = $user->createToken('auth_token')->plainTextToken;

            $data = [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->formatUserPayload($user),
            ];

            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getUser($request)
    {
        try {
            $user = $request->user();

            return $this->formatUserPayload($user);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function logout($request)
    {
        $data = $request->user()->tokens()->delete();

        return $data;
    }
}
