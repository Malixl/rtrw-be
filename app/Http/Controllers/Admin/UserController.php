<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;

class UserController extends Controller
{
    use ApiResponse;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        try {
            $users = $this->userService->getAll($request);
            return $this->successResponseWithData($users, 'Data user berhasil diambil');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,opd',
        ]);

        try {
            $this->userService->store($request->all());
            return $this->successResponse('User berhasil dibuat');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,opd',
        ]);

        try {
            $this->userService->update($id, $request->all());
            return $this->successResponse('User berhasil diperbarui');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->userService->delete($id);
            return $this->successResponse('User berhasil dihapus');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
