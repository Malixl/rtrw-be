<?php

namespace App\Http\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class AuthService
{
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
        ],
    ];

    public function login($request)
    {
        $credentials = filter_var($request->email, FILTER_VALIDATE_EMAIL)
            ? ['email' => $request->email, 'password' => $request->password]
            : ['name' => $request->email, 'password' => $request->password];

        if (! Auth::attempt($credentials)) {
            throw new Exception('Email atau password salah');
        }

        $user = User::where('email', $request->email)
            ->orWhere('name', $request->email)
            ->firstOrFail();

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

    /**
     * Format user data dengan role capabilities
     */
    private function formatUserData($user)
    {
        $role = $this->getUserRole($user);
        $capabilities = $this->getRoleCapabilities($role);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'capabilities' => $capabilities,
        ];
    }

    /**
     * Get user role dengan fail-safe default ke 'guest'
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
     * Get role capabilities
     */
    private function getRoleCapabilities(string $role): array
    {
        return self::ROLE_CAPABILITIES[$role] ?? self::ROLE_CAPABILITIES['guest'];
    }

    /**
     * Get guest capabilities untuk public access
     */
    public static function getGuestCapabilities(): array
    {
        return self::ROLE_CAPABILITIES['guest'];
    }

    public function logout($request)
    {
        $request->user()->currentAccessToken()->delete();

        return true;
    }
}
