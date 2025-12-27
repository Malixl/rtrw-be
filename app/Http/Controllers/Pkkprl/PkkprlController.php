<?php

namespace App\Http\Controllers\Pkkprl;

use App\Http\Controllers\Controller;
use App\Http\Requests\PkkprlRequest;
use App\Http\Resources\PkkprlResources;
use App\Http\Services\PkkprlService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PkkprlController extends Controller
{
    use ApiResponse;

    protected $pkkprlService;

    public function __construct(PkkprlService $pkkprlService)
    {
        $this->pkkprlService = $pkkprlService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->pkkprlService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                PkkprlResources::collection($data),
                'Data polruang berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(PkkprlRequest $request)
    {
        try {
            $this->pkkprlService->store($request);

            return $this->successResponse(
                'Berhasil menambah data pkkprl',
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
            $data = $this->pkkprlService->show($id);

            return $this->successResponseWithData(
                PkkprlResources::make($data),
                'Data pkkprl berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(PkkprlRequest $request, $id)
    {
        try {
            $this->pkkprlService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data pkkprl',
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
            $this->pkkprlService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data pkkprl',
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

            $this->pkkprlService->multiDestroy($ids);

            return $this->successResponse(
                'Berhasil menghapus data pkkprl',
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
        $data = $this->pkkprlService->showGeoJson($id);

        return $data;
    }
}
