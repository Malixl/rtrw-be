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
        $permissions = [
            'manajemen_wilayah',
            'manajemen_polaruang',
            'manajemen_klasifikasi',
            'manajemen_rtrw',
            'view_map',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        // 3. Assign Permissions
        $adminRole->givePermissionTo(Permission::all());
        $opdRole->givePermissionTo(['view_map']);

        // 4. Create Users
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@rtrw.go.id'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // OPD
        $opd = User::firstOrCreate(
            ['email' => 'pupr@rtrw.go.id'],
            [
                'name' => 'Dinas PUPR',
                'password' => bcrypt('password'),
            ]
        );
        $opd->assignRole($opdRole);
    }
}
