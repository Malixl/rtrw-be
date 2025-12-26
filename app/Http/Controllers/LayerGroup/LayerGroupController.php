<?php

namespace App\Http\Controllers\LayerGroup;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLayerGroupRequest;
use App\Http\Requests\UpdateLayerGroupRequest;
use App\Http\Resources\LayerGroupResource;
use App\Http\Resources\LayerGroupMapResource;
use App\Http\Resources\KlasifikasiMapResources;
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
            $format = $request->query('format', 'group');

            if ($format === 'flat') {
                // flat format requires rtrw_id
                if (!$rtrwId) {
                    return $this->errorResponse('rtrw_id is required for flat format', Response::HTTP_BAD_REQUEST);
                }

                $flat = $this->layerGroupService->getAllWithKlasifikasi($rtrwId, $onlyWithChildren, 'flat');

                // transform with resources
                $payload = [
                    'rtrw' => $flat['rtrw'],
                    'klasifikasi_pola_ruang' => KlasifikasiMapResources::collection($flat['klasifikasi_pola_ruang']),
                    'klasifikasi_struktur_ruang' => KlasifikasiMapResources::collection($flat['klasifikasi_struktur_ruang']),
                    'klasifikasi_ketentuan_khusus' => KlasifikasiMapResources::collection($flat['klasifikasi_ketentuan_khusus']),
                    'klasifikasi_indikasi_program' => KlasifikasiMapResources::collection($flat['klasifikasi_indikasi_program']),
                    'klasifikasi_pkkprl' => KlasifikasiMapResources::collection($flat['klasifikasi_pkkprl']),
                    'klasifikasi_data_spasial' => KlasifikasiMapResources::collection($flat['klasifikasi_data_spasial']),
                ];

                return $this->successResponseWithData($payload, 'Data klasifikasi per type berhasil diambil', Response::HTTP_OK);
            }

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
