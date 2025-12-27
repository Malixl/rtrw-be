<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller untuk manajemen role dan permissions
 */
class RoleController extends Controller
{
    use ApiResponse;

    /**
     * Role capabilities mapping
     */
    private const ROLE_CAPABILITIES = [
        'admin' => [
            'can_access_dashboard' => true,
            'can_access_map' => true,
            'can_crud_map' => true,
            'can_manage_users' => true,
            'redirect_options' => ['dashboard', 'map'],
            'navbar_icon' => '👤',
            'navbar_label' => 'Admin',
        ],
        'opd' => [
            'can_access_dashboard' => false,
            'can_access_map' => true,
            'can_crud_map' => false,
            'can_manage_users' => false,
            'redirect_options' => ['map'],
            'navbar_icon' => '👤',
            'navbar_label' => 'OPD',
        ],
        'guest' => [
            'can_access_dashboard' => false,
            'can_access_map' => false,
            'can_crud_map' => false,
            'can_manage_users' => false,
            'redirect_options' => [],
            'navbar_icon' => '🔒',
            'navbar_label' => 'Login',
            'show_blur_map' => true,
            'login_message' => 'Silakan login untuk melihat peta interaktif',
        ],
    ];

    /**
     * Cek status role user saat ini
     * Endpoint ini bisa diakses tanpa login untuk mendapatkan guest capabilities
     */
    public function checkRole(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return $this->successResponseWithData(
                [
                    'is_authenticated' => false,
                    'role' => 'guest',
                    'capabilities' => self::ROLE_CAPABILITIES['guest'],
                ],
                'Status guest',
                Response::HTTP_OK
            );
        }

        $role = $this->getUserRole($user);
        $capabilities = self::ROLE_CAPABILITIES[$role] ?? self::ROLE_CAPABILITIES['guest'];

        return $this->successResponseWithData(
            [
                'is_authenticated' => true,
                'role' => $role,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'capabilities' => $capabilities,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
            'Status role berhasil diambil',
            Response::HTTP_OK
        );
    }

    /**
     * Dapatkan capabilities untuk guest (tanpa auth)
     */
    public function guestCapabilities()
    {
        return $this->successResponseWithData(
            [
                'role' => 'guest',
                'capabilities' => self::ROLE_CAPABILITIES['guest'],
            ],
            'Guest capabilities',
            Response::HTTP_OK
        );
    }

    /**
     * Dapatkan semua role capabilities (untuk referensi frontend)
     */
    public function allCapabilities(Request $request)
    {
        $user = $request->user();

        // Hanya admin yang bisa melihat semua capabilities
        if (! $user || ! $user->hasRole('admin')) {
            return $this->errorResponse(
                'Hanya admin yang dapat mengakses endpoint ini',
                Response::HTTP_FORBIDDEN
            );
        }

        return $this->successResponseWithData(
            self::ROLE_CAPABILITIES,
            'All role capabilities',
            Response::HTTP_OK
        );
    }

    /**
     * Get user role dengan fail-safe
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

        $knownRoles = ['admin', 'opd'];

        return in_array($role->name, $knownRoles) ? $role->name : 'guest';
    }
}
