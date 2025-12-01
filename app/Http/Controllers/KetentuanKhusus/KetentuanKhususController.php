<?php

namespace App\Http\Controllers\KetentuanKhusus;

use App\Http\Controllers\Controller;
use App\Http\Requests\KetentuanKhususRequest;
use App\Http\Resources\KetentuanKhususResources;
use App\Http\Services\KetentuanKhususService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class KetentuanKhususController extends Controller
{
    use ApiResponse;

    protected $ketentuanKhususService;

    public function __construct(KetentuanKhususService $ketentuanKhususService)
    {
        $this->ketentuanKhususService = $ketentuanKhususService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->ketentuanKhususService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                KetentuanKhususResources::collection($data),
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

    public function store(KetentuanKhususRequest $request)
    {
        try {
            $this->ketentuanKhususService->store($request);

            return $this->successResponse(
                'Berhasil menambah data ketentuan khusus',
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
            $data = $this->ketentuanKhususService->show($id);

            return $this->successResponseWithData(
                KetentuanKhususResources::make($data),
                'Data ketentuan khusus berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(KetentuanKhususRequest $request, $id)
    {
        try {
            $this->ketentuanKhususService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data ketentuan khusus',
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
            $this->ketentuanKhususService->destroy($id);

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

            $this->ketentuanKhususService->multiDestroy($ids);

            return $this->successResponse(
                'Berhasil menghapus data ketentuan khusus',
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
        $data = $this->ketentuanKhususService->showGeoJson($id);

        return $data;
    }
}
