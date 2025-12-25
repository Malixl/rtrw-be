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
            $data->where('nama_layer_group', 'like', '%' . $search . '%');
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
            }
        ]);

        if ($request->page) {
            $data = $data->paginate($per_page);
        } else {
            $data = $data->get();
        }

        return $data;
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
            $data = $this->model->whereIn('id', explode(",", $ids))->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }
            $this->model->whereIn('id', explode(",", $ids))->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
