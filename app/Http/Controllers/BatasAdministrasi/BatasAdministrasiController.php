<?php

namespace App\Http\Controllers\BatasAdministrasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BatasAdministrasiRequest;
use App\Http\Resources\BatasAdministrasiResource;
use App\Http\Services\BatasAdministrasiService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class BatasAdministrasiController extends Controller
{
    use ApiResponse;

    protected $batasAdministrasiService;

    public function __construct(BatasAdministrasiService $batasAdministrasiService)
    {
        $this->batasAdministrasiService = $batasAdministrasiService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->batasAdministrasiService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                BatasAdministrasiResource::collection($data),
                'Data batas administrasi berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(BatasAdministrasiRequest $request)
    {
        try {
            $this->batasAdministrasiService->store($request);

            return $this->successResponse(
                'Berhasil menambah data batas administrasi',
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
            $data = $this->batasAdministrasiService->show($id);

            return $this->successResponseWithData(
                BatasAdministrasiResource::make($data),
                'Data batas administrasi berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(BatasAdministrasiRequest $request, $id)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Update BatasAdministrasi Request Data:', $request->all());
            $this->batasAdministrasiService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data batas administrasi',
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
            $this->batasAdministrasiService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data batas administrasi',
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
            $this->batasAdministrasiService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data batas administrasi',
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
        $data = $this->batasAdministrasiService->showGeoJson($id);

        return $data;
    }
}
