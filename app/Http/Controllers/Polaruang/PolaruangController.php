<?php

namespace App\Http\Controllers\Polaruang;

use App\Http\Controllers\Controller;
use App\Http\Requests\PolaruangRequest;
use App\Http\Resources\PolaruangResources;
use App\Http\Services\PolaruangService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PolaruangController extends Controller
{
    use ApiResponse;

    protected $polaruangService;

    public function __construct(PolaruangService $polaruangService)
    {
        $this->polaruangService = $polaruangService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->polaruangService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                PolaruangResources::collection($data),
                'Data polruang berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(PolaruangRequest $request)
    {
        try {
            $this->polaruangService->store($request);

            return $this->successResponse(
                'Berhasil menambah data polrauang',
                Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                $e->errors(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function show($id)
    {
        try {
            $data = $this->polaruangService->show($id);

            return $this->successResponseWithData(
                PolaruangResources::make($data),
                'Data polaruang berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(PolaruangRequest $request, $id)
    {
        try {
            $this->polaruangService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data polaruang',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        } catch (ValidationException $e) {
            return $this->errorResponse(
                $e->errors(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function destroy($id)
    {
        try {
            $this->polaruangService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data polaruanbg',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function multiDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids') ?? $request->query('ids');

            // Jika ids adalah array, convert ke string comma-separated
            if (is_array($ids)) {
                $ids = implode(',', $ids);
            }

            $this->polaruangService->multiDestroy($ids);

            return $this->successResponse(
                'Berhasil menghapus data polaruang',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function showGeoJson($id)
    {
        $data = $this->polaruangService->showGeoJson($id);

        return $data;
    }
}
