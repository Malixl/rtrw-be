<?php

namespace App\Http\Services;

use App\Http\Traits\FileUpload;
use App\Http\Traits\GeoJsonOptimizer;
use App\Models\KawasanStrategiProvinsi;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KawasanStrategiProvinsiService
{
    use FileUpload, GeoJsonOptimizer;

    protected $path = 'kawasan_strategi_provinsi_file';

    protected $model;

    public function __construct(KawasanStrategiProvinsi $model)
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

        $uploadedFiles = [];
        try {
            if ($request->has('geojson_file_path') && $request->input('geojson_file_path')) {
                $validatedData['geojson_file'] = $request->input('geojson_file_path');
                $validatedData['processing_status'] = 'completed';
                unset($validatedData['geojson_file_path']);
            } elseif ($request->hasFile('geojson_file')) {
                $validatedData['geojson_file'] = $this->optimizeAndStore($request->file('geojson_file'), $this->path);
                $validatedData['processing_status'] = 'completed';
                $uploadedFiles[] = $validatedData['geojson_file'];
            }

            if ($request->hasFile('icon_titik')) {
                $validatedData['icon_titik'] = $this->uploadPhotoAndConvertToWebp($request->file('icon_titik'), $this->path);
                $uploadedFiles[] = $validatedData['icon_titik'];
            }

            DB::beginTransaction();
            $data = $this->model->create($validatedData);
            DB::commit();

            return $data;
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            // Cleanup files if DB fails
            foreach ($uploadedFiles as $file) {
                if (Storage::disk('public')->exists($file)) {
                    $this->unlinkFile($file);
                }
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

        $uploadedFiles = [];
        $filesToDelete = [];

        try {
            if ($request->has('geojson_file_path') && $request->input('geojson_file_path')) {
                $validatedData['geojson_file'] = $request->input('geojson_file_path');
                unset($validatedData['geojson_file_path']);
                if ($data->geojson_file) {
                    $filesToDelete[] = $data->geojson_file;
                }
            } elseif ($request->hasFile('geojson_file')) {
                $validatedData['geojson_file'] = $this->optimizeAndStore($request->file('geojson_file'), $this->path);
                $uploadedFiles[] = $validatedData['geojson_file'];
                if ($data->geojson_file) {
                    $filesToDelete[] = $data->geojson_file;
                }
            }

            if ($request->hasFile('icon_titik')) {
                $validatedData['icon_titik'] = $this->uploadPhotoAndConvertToWebp($request->file('icon_titik'), $this->path);
                $uploadedFiles[] = $validatedData['icon_titik'];
                if ($data->icon_titik != 'default.png') {
                    $filesToDelete[] = $data->icon_titik;
                }
            }

            DB::beginTransaction();
            $data->update($validatedData);
            DB::commit();

            // Only delete old files if DB commit succeeds
            foreach ($filesToDelete as $file) {
                if (Storage::disk('public')->exists($file)) {
                    $this->unlinkFile($file);
                }
            }

            return $data; // tetap object model
        } catch (Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            // Cleanup newly uploaded files if DB fails
            foreach ($uploadedFiles as $file) {
                if (Storage::disk('public')->exists($file)) {
                    $this->unlinkFile($file);
                }
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
        $kawasanStrategiProvinsi = $this->model->findOrFail($id);

        // Cek apakah ada file
        if (!empty($kawasanStrategiProvinsi->geojson_file)) {

            $filename = $kawasanStrategiProvinsi->geojson_file;

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
