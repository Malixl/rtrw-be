<?php

namespace App\Http\Services;

use App\Http\Traits\FileUpload;
use App\Models\KetentuanKhusus;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class KetentuanKhususService
{

    use FileUpload;

    protected $path = 'ketentuan_khusus_file';

    protected $model;

    public function __construct(KetentuanKhusus $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->orderBy('created_at');

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
                $extension = ['geojson'];
                $filePath = $this->uploadDocument($request->file('geojson_file'), $extension, $this->path);
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
                $extension = ['geojson'];

                $filePath = $this->uploadDocument($request->file('geojson_file'), $extension, $this->path);

                if ($data->geojson_file) {
                    $this->unlinkFile($data->geojson_file);
                }

                $validatedData['geojson_file'] = $filePath;
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

    public function showGeoJson($id)
    {
        $ketentuan_khusus = $this->model->findOrFail($id);

        // Cek apakah ada file
        if (!empty($ketentuan_khusus->geojson_file)) {

            $filename = $ketentuan_khusus->geojson_file;

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
