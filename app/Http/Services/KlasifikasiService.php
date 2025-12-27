<?php

namespace App\Http\Services;

use App\Models\Klasifikasi;
use Exception;
use Illuminate\Support\Facades\DB;

class KlasifikasiService
{
    protected $model;

    public function __construct(Klasifikasi $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['layerGroup'])->orderBy('created_at');

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%');
        }

        if ($tipe = $request->query('tipe')) {
            $data->where('tipe', $tipe);
        }

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

            // Resolve layer group name -> id if necessary
            if (! empty($validatedData['layer_group_id'])) {
                $validatedData['layer_group_id'] = $validatedData['layer_group_id'];
            } elseif (! empty($validatedData['layer_group'])) {
                $lg = \App\Models\LayerGroup::where('nama_layer_group', $validatedData['layer_group'])->first();
                if (! $lg) {
                    DB::rollBack();
                    throw new Exception('Layer group yang dipilih tidak ditemukan.');
                }
                $validatedData['layer_group_id'] = $lg->id;
            } else {
                $validatedData['layer_group_id'] = null;
            }

            // Remove any layer_group key (we store by id)
            unset($validatedData['layer_group']);

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
        return $this->model->with(['layerGroup'])->findOrFail($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            // Resolve layer group if provided
            if (! empty($validatedData['layer_group_id'])) {
                $validatedData['layer_group_id'] = $validatedData['layer_group_id'];
            } elseif (! empty($validatedData['layer_group'])) {
                $lg = \App\Models\LayerGroup::where('nama_layer_group', $validatedData['layer_group'])->first();
                if (! $lg) {
                    DB::rollBack();
                    throw new Exception('Layer group yang dipilih tidak ditemukan.');
                }
                $validatedData['layer_group_id'] = $lg->id;
            }

            unset($validatedData['layer_group']);

            $data = $this->model->findOrFail($id)->update($validatedData);

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
