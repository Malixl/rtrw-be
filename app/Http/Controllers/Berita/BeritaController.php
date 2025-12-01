<?php

namespace App\Http\Controllers\Berita;

use App\Http\Controllers\Controller;
use App\Http\Requests\BeritaRequest;
use App\Http\Resources\BeritaResources;
use App\Http\Services\BeritaService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class BeritaController extends Controller
{
    use ApiResponse;

    protected $beritaService;

    public function __construct(BeritaService $beritaService)
    {
        $this->beritaService = $beritaService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->beritaService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                BeritaResources::collection($data),
                'Data berita berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function landing(Request $request)
    {
        $data = $this->beritaService->landing($request);

        return $this->successResponseWithDataIndex(
            $data,
            BeritaResources::collection($data),
            'Data berita berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function store(BeritaRequest $request)
    {
        try {
            $this->beritaService->store($request);

            return $this->successResponse(
                'Berhasil menambah data berita',
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

    public function detail($slug)
    {
        $data = $this->beritaService->detail($slug);

        return $this->successResponseWithData(
            BeritaResources::make($data),
            'Data berita berhasil diambil',
            Response::HTTP_OK
        );
    }

    public function show($id)
    {
        try {
            $data = $this->beritaService->show($id);

            return $this->successResponseWithData(
                BeritaResources::make($data),
                'Data berita berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(BeritaRequest $request, $id)
    {
        try {
            $this->beritaService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data berita',
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
            $this->beritaService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data berita',
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
            $this->beritaService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data berita',
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
