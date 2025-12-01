<?php

namespace App\Http\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;

class UserService
{
    public function getAll()
    {
        return User::with('roles')->latest()->get();
    }

    public function store($data)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole($data['role']);

            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, $data)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);
            $user->syncRoles([$data['role']]);

            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting the last admin user
        if ($user->hasRole('admin')) {
            $adminCount = User::role('admin')->count();
            if ($adminCount <= 1) {
                throw new Exception('Tidak dapat menghapus admin terakhir');
            }
        }

        $user->delete();
        return true;
    }
}
