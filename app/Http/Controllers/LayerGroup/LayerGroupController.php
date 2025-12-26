<?php

namespace App\Http\Controllers\LayerGroup;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLayerGroupRequest;
use App\Http\Requests\UpdateLayerGroupRequest;
use App\Http\Resources\LayerGroupResource;
use App\Http\Resources\LayerGroupMapResource;
use App\Http\Services\LayerGroupService;
use App\Http\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class LayerGroupController extends Controller
{
    use ApiResponse;

    protected $layerGroupService;

    public function __construct(LayerGroupService $layerGroupService)
    {
        $this->layerGroupService = $layerGroupService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->layerGroupService->getAll($request);

            return $this->successResponseWithDataIndex(
                $data,
                LayerGroupResource::collection($data),
                'Data layer group berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function store(StoreLayerGroupRequest $request)
    {
        try {
            $this->layerGroupService->store($request);

            return $this->successResponse(
                'Berhasil menambah data layer group',
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

    public function withKlasifikasi(Request $request)
    {
        try {
            $rtrwId = $request->query('rtrw_id');
            $onlyWithChildren = filter_var($request->query('only_with_children', true), FILTER_VALIDATE_BOOLEAN);

            $data = $this->layerGroupService->getAllWithKlasifikasi($rtrwId, $onlyWithChildren);

            return $this->successResponseWithDataIndex(
                $data,
                LayerGroupMapResource::collection($data),
                'Data layer group dengan klasifikasi berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function show($id)
    {
        try {
            $data = $this->layerGroupService->show($id);

            return $this->successResponseWithData(
                LayerGroupResource::make($data),
                'Data layer group berhasil diambil',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function update(UpdateLayerGroupRequest $request, $id)
    {
        try {
            $this->layerGroupService->update($request, $id);

            return $this->successResponse(
                'Berhasil mengubah data layer group',
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
            $this->layerGroupService->destroy($id);

            return $this->successResponse(
                'Berhasil menghapus data layer group',
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
            $this->layerGroupService->multiDestroy($request->ids);

            return $this->successResponse(
                'Berhasil menghapus data layer group',
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
