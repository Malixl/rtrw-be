<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Roles
        $adminRole = Role::findOrCreate('admin', 'web');
        $opdRole   = Role::findOrCreate('opd', 'web');

        // 2. Create Permissions
        // Admin Permissions (Full CRUD)
        $adminPermissions = [
            // Map Management
            'manajemen_wilayah',
            'manajemen_polaruang',
            'manajemen_struktur_ruang',
            'manajemen_klasifikasi',
            'manajemen_rtrw',
            'manajemen_periode',
            'manajemen_ketentuan_khusus',
            'manajemen_indikasi_program',
            'manajemen_pkkprl',
            'manajemen_layer_group',
            'manajemen_data_spasial',
            'manajemen_batas_administrasi',

            // Content Management
            'manajemen_berita',

            // User Management
            'manajemen_users',

            // Dashboard Access
            'access_dashboard',

            // Map Access
            'view_map',
            'crud_map',
        ];

        // OPD Permissions (Read Only)
        $opdPermissions = [
            'view_map',
            'access_dashboard', // Read-only dashboard
        ];

        // Create all permissions
        foreach ($adminPermissions as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        // 3. Assign Permissions to Roles
        $adminRole->syncPermissions(Permission::all());
        $opdRole->syncPermissions($opdPermissions);

        // 4. Create Users
        // Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@rtrw.go.id'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin->syncRoles([$adminRole]);

        // OPD User - Dinas PUPR
        $opdPupr = User::firstOrCreate(
            ['email' => 'pupr@rtrw.go.id'],
            [
                'name' => 'Dinas PUPR',
                'password' => bcrypt('password'),
            ]
        );
        $opdPupr->syncRoles([$opdRole]);

        // OPD User - Bappeda
        $opdBappeda = User::firstOrCreate(
            ['email' => 'bappeda@rtrw.go.id'],
            [
                'name' => 'Bappeda',
                'password' => bcrypt('password'),
            ]
        );
        $opdBappeda->syncRoles([$opdRole]);

        // OPD User - DLHK
        $opdDlhk = User::firstOrCreate(
            ['email' => 'dlhk@rtrw.go.id'],
            [
                'name' => 'Dinas LHK',
                'password' => bcrypt('password'),
            ]
        );
        $opdDlhk->syncRoles([$opdRole]);
    }
}
