<?php

namespace App\Http\Controllers\Klasifikasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\KlasifikasiRequest;
use App\Http\Resources\KlasifikasiResources;
use App\Http\Services\KlasifikasiService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class klasifikasiController extends Controller
{
    use ApiResponse;

    protected $klasifikasiService;

    public function __construct(KlasifikasiService $klasifikasiService)
    {
        $this->klasifikasiService = $klasifikasiService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->klasifikasiService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                KlasifikasiResources::collection($data),
                'Data klasifikasi berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(KlasifikasiRequest $request)
    {
        try {
            $this->klasifikasiService->store($request);

            return $this->successResponse(
                'Berhasil menambah data klasifikasi',
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
            $data = $this->klasifikasiService->show($id);

            return $this->successResponseWithData(
                KlasifikasiResources::make($data),
                'Data klasifikasi berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(KlasifikasiRequest $request, $id)
    {
        try {
            $this->klasifikasiService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data klasifikasi',
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
            $this->klasifikasiService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data klasifikasi',
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
            $this->klasifikasiService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data klasifikasi',
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
