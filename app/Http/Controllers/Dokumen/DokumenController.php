<?php

namespace App\Http\Controllers\Dokumen;

use App\Http\Controllers\Controller;
use App\Http\Requests\DokumenRequest;
use App\Http\Resources\DokumenResources;
use App\Http\Services\DokumenService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DokumenController extends Controller
{
    use ApiResponse;

    protected $dokumenService;

    public function __construct(DokumenService $dokumenService)
    {
        $this->dokumenService = $dokumenService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->dokumenService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                DokumenResources::collection($data),
                'Data dokumen berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(DokumenRequest $request)
    {
        try {
            $this->dokumenService->store($request);

            return $this->successResponse(
                'Berhasil menambah data dokumen',
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
            $data = $this->dokumenService->show($id);

            return $this->successResponseWithData(
                DokumenResources::make($data),
                'Data dokumen berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(DokumenRequest $request, $id)
    {
        try {
            $this->dokumenService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data dokumen',
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
            $this->dokumenService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data dokumen',
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

            $this->dokumenService->multiDestroy($ids);

            return $this->successResponse(
                'Berhasil menghapus data dokumen',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
