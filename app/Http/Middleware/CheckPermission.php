<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk validasi permission pengguna
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$permissions  Permissions yang diperlukan
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        // Fail-safe: Jika tidak login
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Silakan login untuk mengakses fitur ini',
                'code' => 'UNAUTHORIZED',
                'require_login' => true,
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Jika tidak ada permission yang ditentukan, lanjutkan
        if (empty($permissions)) {
            return $next($request);
        }

        // Cek apakah user memiliki salah satu permission
        foreach ($permissions as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return $next($request);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Anda tidak memiliki izin untuk mengakses fitur ini',
            'code' => 'FORBIDDEN',
        ], Response::HTTP_FORBIDDEN);
    }
}
