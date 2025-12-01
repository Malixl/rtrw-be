<?php

namespace App\Http\Controllers\Periode;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodeRequest;
use App\Http\Resources\PeriodeResources;
use App\Http\Services\PeriodeService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PeriodeController extends Controller
{
    use ApiResponse;

    protected $periodeService;

    public function __construct(PeriodeService $periodeService)
    {
        $this->periodeService = $periodeService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->periodeService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                PeriodeResources::collection($data),
                'Data periode berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function store(PeriodeRequest $request)
    {
        try {
            $this->periodeService->store($request);

            return $this->successResponse(
                'Berhasil menambah data periode',
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
            $data = $this->periodeService->show($id);

            return $this->successResponseWithData(
                PeriodeResources::make($data),
                'Data periode berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(PeriodeRequest $request, $id)
    {
        try {
            $this->periodeService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data periode',
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
            $this->periodeService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data periode',
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
            $this->periodeService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data periode',
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
