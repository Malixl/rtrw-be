<?php

namespace App\Http\Services;

use App\Http\Traits\FileUpload;
use App\Http\Traits\GeoJsonOptimizer;
use App\Http\Traits\QueueableGeoJson;
use App\Models\DataSpasial;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataSpasialService
{
    use FileUpload, GeoJsonOptimizer, QueueableGeoJson;

    protected $path = 'data_spasial_service';

    protected $model;

    public function __construct(DataSpasial $model)
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
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            // Create the model first to get ID for queue job
            if ($request->hasFile('geojson_file')) {
                $file = $request->file('geojson_file');
                
                if ($this->shouldQueueFile($file)) {
                    // Large file: will be processed via queue
                    $validatedData['processing_status'] = 'pending';
                    $validatedData['geojson_file'] = null; // Will be set by job
                } else {
                    // Small file: process synchronously
                    $validatedData['geojson_file'] = $this->optimizeAndStore($file, $this->path);
                    $validatedData['processing_status'] = 'completed';
                }
            }

            if ($request->hasFile('icon_titik')) {
                $icon_titik = $this->uploadPhotoAndConvertToWebp($request->file('icon_titik'), $this->path);
                $validatedData['icon_titik'] = $icon_titik;
            }

            $data = $this->model->create($validatedData);

            // If large file, dispatch queue job after model is created
            if ($request->hasFile('geojson_file') && $this->shouldQueueFile($request->file('geojson_file'))) {
                $result = $this->storeAndOptimizeGeoJson(
                    $request->file('geojson_file'),
                    $this->path,
                    DataSpasial::class,
                    $data->id
                );
            }

            DB::commit();

            return $data;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        return $this->model->with(['klasifikasi.layerGroup'])->findOrFail($id);
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

            if ($request->hasFile('icon_titik')) {
                $icon_titik = $this->uploadPhotoAndConvertToWebp($request->file('icon_titik'), $this->path);
                $validatedData['icon_titik'] = $icon_titik;
                if ($data->icon_titik != 'default.png') {
                    $this->unlinkPhoto($data->icon_titik);
                }
            }

            $data->update($validatedData);

            DB::commit();

            return $data; // tetap object model
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

    public function showGeoJson($id)
    {
        $model = $this->model->findOrFail($id);

        // Cek apakah ada file
        if (! empty($model->geojson_file)) {

            $filename = $model->geojson_file;

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
