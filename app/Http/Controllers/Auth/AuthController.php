<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Services\AuthService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        try {
            $data = $this->authService->login($request);

            return $this->successResponseWithData(
                $data,
                'Login berhasil',
                Response::HTTP_OK
            );
        } catch (ValidationException $e) {
            return $this->errorResponseWithData($e->errors(), 'Login gagal', Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUser(Request $request)
    {
        $data = $this->authService->getUser($request);

        return $this->successResponseWithData(
            $data,
            'Data pengguna berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function logout(Request $request)
    {
        try {

            $this->authService->logout($request);

            return $this->successResponse(
                'Berhasil keluar',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
