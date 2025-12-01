<?php

namespace App\Http\Controllers\IndikasiProgram;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndikasiProgramRequest;
use App\Http\Resources\IndikasiProgramResources;
use App\Http\Services\IndikasiProgramService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class IndikasiProgramController extends Controller
{
    use ApiResponse;

    protected $indikasiProgramService;

    public function __construct(IndikasiProgramService $indikasiProgramService)
    {
        $this->indikasiProgramService = $indikasiProgramService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->indikasiProgramService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                IndikasiProgramResources::collection($data),
                'Data indikasi program berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(IndikasiProgramRequest $request)
    {
        try {
            $this->indikasiProgramService->store($request);

            return $this->successResponse(
                'Berhasil menambah data indikasi program',
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
            $data = $this->indikasiProgramService->show($id);

            return $this->successResponseWithData(
                IndikasiProgramResources::make($data),
                'Data indikasi program berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(IndikasiProgramRequest $request, $id)
    {
        try {
            $this->indikasiProgramService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data indikasi program',
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
            $this->indikasiProgramService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data indikasi program',
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

            $this->indikasiProgramService->multiDestroy($ids);

            return $this->successResponse(
                'Berhasil menghapus data indikasi program',
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
