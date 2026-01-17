<?php

namespace App\Http\Services;

use App\Http\Traits\FileUpload;
use App\Http\Traits\GeoJsonOptimizer;
use App\Models\BatasAdministrasi;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BatasAdministrasiService
{
    use FileUpload, GeoJsonOptimizer;

    protected $path = 'batas_administrasi_file';

    protected $model;

    public function __construct(BatasAdministrasi $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['klasifikasi.layerGroup'])->orderBy('created_at', 'desc');

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%');
        }

        if ($klasifikasi_id = $request->query('klasifikasi_id')) {
            $data->where('klasifikasi_id', $klasifikasi_id);
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

            if ($request->hasFile('geojson_file')) {
                $filePath = $this->optimizeAndStore($request->file('geojson_file'), $this->path);
                $validatedData['geojson_file'] = $filePath;
            }

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
        return $this->model->findOrFail($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $data = $this->model->findOrFail($id);

            if ($request->hasFile('geojson_file')) {
                $filePath = $this->optimizeAndStore($request->file('geojson_file'), $this->path);

                if ($data->geojson_file) {
                    $this->unlinkFile($data->geojson_file);
                }

                $validatedData['geojson_file'] = $filePath;
            }

            // Explicitly update attributes to ensure 'warna' is saved
            $data->fill($validatedData);
            $data->save();

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

            if ($data->geojson_file) {
                $this->unlinkFile($data->geojson_file);
            }

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
            $idsArray = explode(',', $ids);
            $data = $this->model->whereIn('id', $idsArray)->get();

            if ($data->isEmpty()) {
                DB::rollBack();
                throw new Exception('Data tidak ditemukan');
            }

            foreach ($data as $item) {
                if ($item->geojson_file) {
                    $this->unlinkFile($item->geojson_file);
                }
            }

            $this->model->whereIn('id', $idsArray)->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function showGeoJson($id)
    {
        $data = $this->model->findOrFail($id);

        // Cek apakah ada file
        if (! empty($data->geojson_file)) {

            $filename = $data->geojson_file;

            if (! Storage::disk('public')->exists($filename)) {
                return response()->json(['error' => 'File not found on disk'], 404);
            }

            // ambil full path
            $path = Storage::disk('public')->path($filename);

            return response()->file($path, [
                'Content-Type' => 'application/geo+json',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }

        return response()->json(['error' => 'No GeoJSON file found for this entry'], 404);
    }
}
