<?php

namespace App\Http\Services;

use App\Http\Traits\FileUpload;
use App\Http\Traits\GeoJsonOptimizer;
use App\Models\Polaruang;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PolaruangService
{
    use FileUpload, GeoJsonOptimizer;

    protected $path = 'polaruang_file';

    protected $model;

    public function __construct(Polaruang $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with(['klasifikasi.layerGroup'])->orderBy('created_at');

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
        $validatedData = $request->validated();

        if ($request->has('geojson_file_path') && $request->input('geojson_file_path')) {
            $validatedData['geojson_file'] = $request->input('geojson_file_path');
            $validatedData['processing_status'] = 'completed';
            unset($validatedData['geojson_file_path']);
        } elseif ($request->hasFile('geojson_file')) {
            // Upload file FIRST, outside the transaction to prevent DB timeout
            $validatedData['geojson_file'] = $this->optimizeAndStore($request->file('geojson_file'), $this->path);
            $validatedData['processing_status'] = 'completed';
        }

        DB::beginTransaction();
        try {
            $data = $this->model->create($validatedData);
            DB::commit();
            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            // Optional: cleanup uploaded file here if DB fails
            if (isset($validatedData['geojson_file']) && Storage::disk('public')->exists($validatedData['geojson_file'])) {
                $this->unlinkFile($validatedData['geojson_file']);
            }
            throw $e;
        }
    }

    public function show($id)
    {
        return $this->model->with(['klasifikasi.layerGroup'])->findOrFail($id);
    }

    public function update($request, $id)
    {
        $validatedData = $request->validated();
        $data = $this->model->findOrFail($id);

        $oldFile = null;
        if ($request->has('geojson_file_path') && $request->input('geojson_file_path')) {
            $oldFile = $data->geojson_file;
            $validatedData['geojson_file'] = $request->input('geojson_file_path');
            unset($validatedData['geojson_file_path']);
        } elseif ($request->hasFile('geojson_file')) {
            // Upload new file FIRST, outside the transaction
            $filePath = $this->optimizeAndStore($request->file('geojson_file'), $this->path);
            $oldFile = $data->geojson_file;
            $validatedData['geojson_file'] = $filePath;
        }

        DB::beginTransaction();
        try {
            $data->update($validatedData);
            DB::commit();

            // Delete old file only after DB commits successfully
            if ($oldFile) {
                $this->unlinkFile($oldFile);
            }

            return $data; // tetap object model
        } catch (Exception $e) {
            DB::rollBack();
            // Cleanup newly uploaded file if DB update fails
            if (isset($validatedData['geojson_file']) && Storage::disk('public')->exists($validatedData['geojson_file'])) {
                $this->unlinkFile($validatedData['geojson_file']);
            }
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

    public function showGeoJson($id)
    {
        $polaruang = $this->model->findOrFail($id);

        // Cek apakah ada file
        if (!empty($polaruang->geojson_file)) {

            $filename = $polaruang->geojson_file;

            if (!Storage::disk('public')->exists($filename)) {
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
