<?php

namespace App\Http\Controllers\StrukturRuang;

use App\Http\Controllers\Controller;
use App\Http\Requests\StrukturRuangRequest;
use App\Http\Resources\StrukturRuangResources;
use App\Http\Services\StrukturRuangService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class StrukturRuangController extends Controller
{
    use ApiResponse;

    protected $strukturRuangService;

    public function __construct(StrukturRuangService $strukturRuangService)
    {
        $this->strukturRuangService = $strukturRuangService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->strukturRuangService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                StrukturRuangResources::collection($data),
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

    public function store(StrukturRuangRequest $request)
    {
        try {
            $this->strukturRuangService->store($request);

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
            $data = $this->strukturRuangService->show($id);

            return $this->successResponseWithData(
                StrukturRuangResources::make($data),
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

    public function update(StrukturRuangRequest $request, $id)
    {
        try {
            $this->strukturRuangService->update($request, $id);

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
            $this->strukturRuangService->destroy($id);

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

            $this->strukturRuangService->multiDestroy($ids);

            return $this->successResponse(
                'Berhasil menghapus data struktur ruang',
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
        $data = $this->strukturRuangService->showGeoJson($id);

        return $data;
    }
}
