<?php

namespace App\Http\Services;

use App\Models\LayerGroup;
use Exception;
use Illuminate\Support\Facades\DB;

class LayerGroupService
{
    protected $model;

    public function __construct(LayerGroup $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;

        $data = $this->model->orderBy('urutan_tampil')
            ->orderBy('created_at');

        if ($search = $request->query('search')) {
            $data->where('nama_layer_group', 'like', '%'.$search.'%');
        }

        // eager load klasifikasis and their related data
        $data->with([
            'klasifikasis' => function ($q) {
                $q->orderBy('nama')
                    ->with([
                        'polaRuang',
                        'strukturRuang',
                        'ketentuanKhusus',
                        'indikasiProgram',
                        'pkkprl',
                        'dataSpasial',
                    ]);
            },
        ]);

        if ($request->page) {
            $data = $data->paginate($per_page);
        } else {
            $data = $data->get();
        }

        return $data;
    }

    /**
     * Get all layer groups with nested klasifikasis and geo-data children
     * Optionally filter klasifikasis by rtrw_id and only include klasifikasi that have geo children
     */
    public function getAllWithKlasifikasi($onlyWithChildren = true, $format = 'group')
    {
        // group format (default) — return collection of LayerGroups with nested klasifikasis and geo children
        if ($format === 'group') {
            $groups = $this->model->orderBy('urutan_tampil')
                ->orderBy('created_at')
                ->with(['klasifikasis' => function ($q) {
                    $q->orderBy('nama')
                        ->with([
                            'polaRuang',
                            'strukturRuang',
                            'ketentuanKhusus',
                            'indikasiProgram',
                            'pkkprl',
                            'dataSpasial',
                        ]);
                }])->get();

            if ($onlyWithChildren) {
                // filter klasifikasis that have at least one geo child
                $groups = $groups->map(function ($g) {
                    $filtered = $g->klasifikasis->filter(function ($k) {
                        return
                            ($k->polaRuang && $k->polaRuang->isNotEmpty()) ||
                            ($k->strukturRuang && $k->strukturRuang->isNotEmpty()) ||
                            ($k->ketentuanKhusus && $k->ketentuanKhusus->isNotEmpty()) ||
                            ($k->indikasiProgram && $k->indikasiProgram->isNotEmpty()) ||
                            ($k->pkkprl && $k->pkkprl->isNotEmpty()) ||
                            ($k->dataSpasial && $k->dataSpasial->isNotEmpty());
                    })->values();

                    $g->setRelation('klasifikasis', $filtered);

                    return $g;
                })->filter(function ($g) {
                    return $g->klasifikasis->isNotEmpty();
                })->values();
            }

            return $groups;
        }

        // flat format — build per-type klasifikasi lists across all RTRW (no rtrw dependency)
        if ($format === 'flat') {
            $klasifikasi_pola_ruang = \App\Models\Klasifikasi::whereHas('polaRuang')
                ->with(['polaRuang', 'layerGroup'])
                ->get();

            $klasifikasi_struktur_ruang = \App\Models\Klasifikasi::whereHas('strukturRuang')
                ->with(['strukturRuang', 'layerGroup'])
                ->get();

            $klasifikasi_ketentuan_khusus = \App\Models\Klasifikasi::whereHas('ketentuanKhusus')
                ->with(['ketentuanKhusus', 'layerGroup'])
                ->get();

            $klasifikasi_indikasi_program = \App\Models\Klasifikasi::whereHas('indikasiProgram')
                ->with(['indikasiProgram', 'layerGroup'])
                ->get();

            $klasifikasi_pkkprl = \App\Models\Klasifikasi::whereHas('pkkprl')
                ->with(['pkkprl', 'layerGroup'])
                ->get();

            $klasifikasi_data_spasial = \App\Models\Klasifikasi::whereHas('dataSpasial')
                ->with(['dataSpasial', 'layerGroup'])
                ->get();

            return [
                'klasifikasi_pola_ruang' => $klasifikasi_pola_ruang,
                'klasifikasi_struktur_ruang' => $klasifikasi_struktur_ruang,
                'klasifikasi_ketentuan_khusus' => $klasifikasi_ketentuan_khusus,
                'klasifikasi_indikasi_program' => $klasifikasi_indikasi_program,
                'klasifikasi_pkkprl' => $klasifikasi_pkkprl,
                'klasifikasi_data_spasial' => $klasifikasi_data_spasial,
            ];
        }

        throw new \Exception('Invalid format parameter');
    }

    public function store($request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            $data = $this->model->create($validatedData);

            DB::commit();

            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        return $this->model->with(['klasifikasis'])->findOrFail($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $data = $this->model->findOrFail($id);

            $data->update($validatedData);

            DB::commit();

            return $data;
        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = $this->model->findOrFail($id);

            $data->delete();

            DB::commit();
        } catch (Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public function multiDestroy($ids)
    {
        DB::beginTransaction();
        try {
            $data = $this->model->whereIn('id', explode(',', $ids))->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }
            $this->model->whereIn('id', explode(',', $ids))->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
