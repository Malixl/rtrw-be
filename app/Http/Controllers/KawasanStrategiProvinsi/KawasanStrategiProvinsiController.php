<?php

namespace App\Http\Controllers\KawasanStrategiProvinsi;

use App\Http\Controllers\Controller;
use App\Http\Requests\KawasanStrategiProvinsiRequest;
use App\Http\Resources\KawasanStrategiProvinsiResources;
use App\Http\Services\KawasanStrategiProvinsiService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class KawasanStrategiProvinsiController extends Controller
{
    use ApiResponse;

    protected $kawasanStrategiProvinsiService;

    public function __construct(KawasanStrategiProvinsiService $kawasanStrategiProvinsiService)
    {
        $this->kawasanStrategiProvinsiService = $kawasanStrategiProvinsiService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->kawasanStrategiProvinsiService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                KawasanStrategiProvinsiResources::collection($data),
                'Data Kawasan Strategi Provinsi berhasil diambil',
                Response::HTTP_OK
            );
        }
        catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(KawasanStrategiProvinsiRequest $request)
    {
        try {
            $this->kawasanStrategiProvinsiService->store($request);

            return $this->successResponse(
                'Berhasil menambah data Kawasan Strategi Provinsi',
                Response::HTTP_CREATED
            );
        }
        catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
        catch (ValidationException $e) {
            return $this->errorResponse(
                $e->errors(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function show($id)
    {
        try {
            $data = $this->kawasanStrategiProvinsiService->show($id);

            return $this->successResponseWithData(
                KawasanStrategiProvinsiResources::make($data),
                'Data Kawasan Strategi Provinsi berhasil diambil',
                Response::HTTP_OK
            );
        }
        catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(KawasanStrategiProvinsiRequest $request, $id)
    {
        try {
            $this->kawasanStrategiProvinsiService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data Kawasan Strategi Provinsi',
                Response::HTTP_OK
            );
        }
        catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
        catch (ValidationException $e) {
            return $this->errorResponse(
                $e->errors(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function destroy($id)
    {
        try {
            $this->kawasanStrategiProvinsiService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data Kawasan Strategi Provinsi',
                Response::HTTP_OK
            );
        }
        catch (Exception $e) {
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

            $this->kawasanStrategiProvinsiService->multiDestroy($ids);

            return $this->successResponse(
                'Berhasil menghapus data Kawasan Strategi Provinsi',
                Response::HTTP_OK
            );
        }
        catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function showGeoJson($id)
    {
        $data = $this->kawasanStrategiProvinsiService->showGeoJson($id);

        return $data;
    }
}
