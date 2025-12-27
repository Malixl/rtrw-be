<?php

namespace App\Http\Controllers\LayerGroup;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLayerGroupRequest;
use App\Http\Requests\UpdateLayerGroupRequest;
use App\Http\Resources\KlasifikasiMapResources;
use App\Http\Resources\LayerGroupMapResource;
use App\Http\Resources\LayerGroupResource;
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
            $onlyWithChildren = filter_var($request->query('only_with_children', true), FILTER_VALIDATE_BOOLEAN);
            $format = $request->query('format', 'group');

            if ($format === 'flat') {
                // flat format now returns per-type klasifikasi across all RTRW
                $flat = $this->layerGroupService->getAllWithKlasifikasi($onlyWithChildren, 'flat');

                // transform with resources
                $payload = [
                    'klasifikasi_pola_ruang' => KlasifikasiMapResources::collection($flat['klasifikasi_pola_ruang']),
                    'klasifikasi_struktur_ruang' => KlasifikasiMapResources::collection($flat['klasifikasi_struktur_ruang']),
                    'klasifikasi_ketentuan_khusus' => KlasifikasiMapResources::collection($flat['klasifikasi_ketentuan_khusus']),
                    'klasifikasi_indikasi_program' => KlasifikasiMapResources::collection($flat['klasifikasi_indikasi_program']),
                    'klasifikasi_pkkprl' => KlasifikasiMapResources::collection($flat['klasifikasi_pkkprl']),
                    'klasifikasi_data_spasial' => KlasifikasiMapResources::collection($flat['klasifikasi_data_spasial']),
                ];

                return $this->successResponseWithData($payload, 'Data klasifikasi per type berhasil diambil', Response::HTTP_OK);
            }

            $data = $this->layerGroupService->getAllWithKlasifikasi($onlyWithChildren);

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

    /**
     * Search endpoint for GIS (by klasifikasi id or aset type). This replaces RTRW-based search in map.
     */
    public function search(Request $request)
    {
        try {
            $klasifikasiId = $request->query('klasifikasi_id');
            $tipe = $request->query('tipe');

            if ($klasifikasiId) {
                $k = \App\Models\Klasifikasi::with(['polaRuang', 'strukturRuang', 'ketentuanKhusus', 'indikasiProgram', 'pkkprl', 'dataSpasial', 'layerGroup'])->findOrFail($klasifikasiId);

                return $this->successResponseWithData(
                    KlasifikasiMapResources::make($k),
                    'Klasifikasi ditemukan',
                    Response::HTTP_OK
                );
            }

            $q = \App\Models\Klasifikasi::with(['polaRuang', 'strukturRuang', 'ketentuanKhusus', 'indikasiProgram', 'pkkprl', 'dataSpasial', 'layerGroup']);

            if ($tipe) {
                $q->where('tipe', $tipe);
            }

            $list = $q->get();

            return $this->successResponseWithDataIndex($list, KlasifikasiMapResources::collection($list), 'List klasifikasi berhasil diambil', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
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
