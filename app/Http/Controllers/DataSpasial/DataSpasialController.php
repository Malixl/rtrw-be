<?php

namespace App\Http\Controllers\DataSpasial;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataSpasialRequest;
use App\Http\Resources\DataSpasialResources;
use App\Http\Services\DataSpasialService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DataSpasialController extends Controller
{
    use ApiResponse;

    protected $service;

    public function __construct(DataSpasialService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->service->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                DataSpasialResources::collection($data),
                'Data data spasial berhasil diambil',
                Response::HTTP_OK
            );
        }
        catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengambil data spasial: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem saat mengambil data.',
                'error_detail' => env('APP_DEBUG') ? $e->getMessage() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(DataSpasialRequest $request)
    {
        try {
            $this->service->store($request);

            return $this->successResponse(
                'Berhasil menambah data data spasial',
                Response::HTTP_CREATED
            );
        }
        catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menambah data spasial: ' . $e->getMessage());
            return $this->errorResponse(
                'Terjadi kesalahan sistem saat memproses data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
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
            $data = $this->service->show($id);

            return $this->successResponseWithData(
                DataSpasialResources::make($data),
                'Data data spasial berhasil diambil',
                Response::HTTP_OK
            );
        }
        catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengambil detail data spasial: ' . $e->getMessage());
            return $this->errorResponse(
                'Terjadi kesalahan sistem saat mengambil data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function update(DataSpasialRequest $request, $id)
    {
        try {
            $this->service->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data data spasial',
                Response::HTTP_OK
            );
        }
        catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mengubah data spasial: ' . $e->getMessage());
            return $this->errorResponse(
                'Terjadi kesalahan sistem saat memproses data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
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
            $this->service->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data data spasial',
                Response::HTTP_OK
            );
        }
        catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menghapus data spasial: ' . $e->getMessage());
            return $this->errorResponse(
                'Terjadi kesalahan sistem saat menghapus data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function multiDestroy(Request $request)
    {
        try {
            $ids = $request->input('ids') ?? $request->query('ids');

            if (is_array($ids)) {
                $ids = implode(',', $ids);
            }

            $this->service->multiDestroy($ids);

            return $this->successResponse(
                'Berhasil menghapus data data spasial',
                Response::HTTP_OK
            );
        }
        catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Gagal menghapus banyak data spasial: ' . $e->getMessage());
            return $this->errorResponse(
                'Terjadi kesalahan sistem saat menghapus data.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function showGeoJson($id)
    {
        $data = $this->service->showGeoJson($id);

        return $data;
    }
}
