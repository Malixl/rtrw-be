<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk validasi role pengguna (admin, opd, guest)
 * dengan kondisi fail-safe default (guest jika tidak dikenali).
 */
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  string  ...$roles  Roles yang diizinkan (admin, opd)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Fail-safe: Jika tidak login, anggap sebagai guest
        if (! $user) {
            return $this->unauthorizedResponse('Silakan login untuk mengakses fitur ini');
        }

        // Ambil role user
        $userRole = $this->getUserRole($user);

        // Jika roles kosong, berarti hanya perlu authenticated (admin atau opd)
        if (empty($roles)) {
            if (in_array($userRole, ['admin', 'opd'])) {
                return $next($request);
            }

            return $this->forbiddenResponse('Anda tidak memiliki akses ke fitur ini');
        }

        // Cek apakah user memiliki salah satu role yang diizinkan
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        return $this->forbiddenResponse('Anda tidak memiliki akses ke fitur ini');
    }

    /**
     * Ambil role user dengan fail-safe default ke 'guest'
     */
    private function getUserRole($user): string
    {
        if (! $user) {
            return 'guest';
        }

        $role = $user->roles->first();

        if (! $role) {
            return 'guest';
        }

        // Validasi hanya role yang dikenal
        $knownRoles = ['admin', 'opd'];

        return in_array($role->name, $knownRoles) ? $role->name : 'guest';
    }

    /**
     * Response untuk unauthorized (belum login)
     */
    private function unauthorizedResponse(string $message): Response
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'code' => 'UNAUTHORIZED',
            'require_login' => true,
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Response untuk forbidden (tidak punya akses)
     */
    private function forbiddenResponse(string $message): Response
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'code' => 'FORBIDDEN',
        ], Response::HTTP_FORBIDDEN);
    }
}
