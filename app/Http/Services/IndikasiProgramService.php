<?php

namespace App\Http\Services;

use App\Http\Traits\FileUpload;
use App\Models\IndikasiProgram;
use Exception;
use Illuminate\Support\Facades\DB;

class IndikasiProgramService
{

    use FileUpload;

    protected $path = 'indikasi_program_file';

    protected $model;

    public function __construct(IndikasiProgram $model)
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

            if ($request->hasFile('file_dokumen')) {
                $extension = ['pdf'];
                $filePath = $this->uploadDocument($request->file('file_dokumen'), $extension, $this->path);
                $validatedData['file_dokumen'] = $filePath;
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

            if ($request->hasFile('file_dokumen')) {
                $extension = ['pdf'];

                $filePath = $this->uploadDocument($request->file('file_dokumen'), $extension, $this->path);

                if ($data->file_dokumen) {
                    $this->unlinkFile($data->file_dokumen);
                }

                $validatedData['file_dokumen'] = $filePath;
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
}
