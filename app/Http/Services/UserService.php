<?php

namespace App\Http\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll($perPage = null)
    {
        $query = User::with('roles')->latest();

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function getById($id)
    {
        return User::with('roles')->findOrFail($id);
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

            if (! empty($data['password'])) {
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

        // Prevent deleting yourself
        /** @var int|null $currentUserId */
        $currentUserId = auth()->id();
        if ($currentUserId === $user->id) {
            throw new Exception('Tidak dapat menghapus akun sendiri');
        }

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

    public function multiDelete(array $ids)
    {
        DB::beginTransaction();
        try {
            /** @var int|null $currentUserId */
            $currentUserId = auth()->id();

            // Check if trying to delete self
            if (in_array($currentUserId, $ids)) {
                throw new Exception('Tidak dapat menghapus akun sendiri');
            }

            // Check admin count
            $adminsToDelete = User::role('admin')->whereIn('id', $ids)->count();
            $totalAdmins = User::role('admin')->count();

            if ($adminsToDelete >= $totalAdmins) {
                throw new Exception('Tidak dapat menghapus semua admin');
            }

            User::whereIn('id', $ids)->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
