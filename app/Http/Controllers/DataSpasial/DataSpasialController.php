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
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
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
            $data = $this->service->show($id);

            return $this->successResponseWithData(
                DataSpasialResources::make($data),
                'Data data spasial berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
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
            $this->service->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data data spasial',
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

            if (is_array($ids)) {
                $ids = implode(',', $ids);
            }

            $this->service->multiDestroy($ids);

            return $this->successResponse(
                'Berhasil menghapus data data spasial',
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
        $data = $this->service->showGeoJson($id);

        return $data;
    }
}
