<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Services\UserService;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResources;
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

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page');
            $users = $this->userService->getAll($perPage);

            if ($perPage) {
                return $this->successResponseWithData(
                    UserResources::collection($users)->response()->getData(true),
                    'Data user berhasil diambil'
                );
            }

            return $this->successResponseWithData(
                UserResources::collection($users),
                'Data user berhasil diambil'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $request)
    {
        try {
            $user = $this->userService->store($request->validated());
            return $this->successResponseWithData(
                new UserResources($user->load('roles')),
                'User berhasil dibuat',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        try {
            $user = $this->userService->getById($id);
            return $this->successResponseWithData(
                new UserResources($user),
                'Data user berhasil diambil'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(UserRequest $request, $id)
    {
        try {
            $user = $this->userService->update($id, $request->validated());
            return $this->successResponseWithData(
                new UserResources($user->load('roles')),
                'User berhasil diperbarui'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy($id)
    {
        try {
            $this->userService->delete($id);
            return $this->successResponse('User berhasil dihapus');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Remove multiple users.
     */
    public function multiDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:users,id'
        ]);

        try {
            $this->userService->multiDelete($request->ids);
            return $this->successResponse('User berhasil dihapus');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
