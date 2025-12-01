<?php

namespace App\Http\Services;

use Exception;
use App\Models\Berita;
use App\Exceptions\ServiceException;
use App\Http\Traits\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BeritaService
{
    use FileUpload;

    protected $path = 'berita';
    protected $model;

    public function __construct(Berita $model)
    {
        $this->model = $model;
    }

    public function landing($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->where('status', 'publikasi')->latest();

        if ($search = $request->query('search')) {
            $data->where('judul', 'like', '%' . $search . '%');
        }

        if ($status = $request->query('status')) {
            $data->where('status', $status);
        }

        return $data->paginate($per_page);
    }

    public function detail($slug)
    {
        $data = $this->model->where('slug', $slug)->where('status', 'publikasi')->first();

        if (!$data) {
            throw new ServiceException('Berita tidak ditemukan', 404);
        }

        return $data;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->latest();

        if ($search = $request->query('search')) {
            $data->where('judul', 'like', '%' . $search . '%');
        }

        if ($status = $request->query('status')) {
            $data->where('status', $status);
        }

        return $data->paginate($per_page);
    }

    // public function store($request)
    // {
    //     try {
    //         $validatedData = $request->validated();
    //         $validatedData['slug'] = str()->slug($validatedData['judul']);
    //         $validatedData['status'] = 'publikasi';

    //         if ($request->hasFile('thumbnail')) {
    //             $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
    //             $validatedData['thumbnail'] = $thumbnail;
    //         }
    //         $this->model->create([
    //             'judul' => $validatedData['judul'],
    //             'slug' => $validatedData['slug'],
    //             'konten' => $validatedData['konten'],
    //             'thumbnail' => $validatedData['thumbnail'],
    //             'status' => $validatedData['status'],
    //         ]);
    //     } catch (Exception $e) {

    //         throw $e;
    //     }
    // }
    public function store($request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();
            $validatedData['slug'] = str()->slug($validatedData['judul']);

            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumbnail'] = $thumbnail;
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
        $data = $this->model->find($id);

        if (!$data) {
            throw new NotFoundHttpException('Data tidak ditemukan');
        }
        return $data;
    }

    public function update($request, $id)
    {
        try {
            $validatedData = $request->validated();
            $data = $this->show($id);
            $validatedData['slug'] = str()->slug($validatedData['judul']);
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $this->uploadPhotoAndConvertToWebp($request->file('thumbnail'), $this->path);
                $validatedData['thumbnail'] = $thumbnail;
                if ($data->thumbnail != 'default.png') {
                    $this->unlinkPhoto($data->thumbnail);
                }
            }
            $data->update([
                'judul' => $validatedData['judul'],
                'slug' => $validatedData['slug'],
                'konten' => $validatedData['konten'],
                'thumnbail' => $validatedData['thumbnail'] ?? $data->thumnbail,
                'status' => $request->status ? $validatedData['status'] : $data->status
            ]);

            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function destroy($id)
    {
        try {
            $data = $this->show($id);
            if ($data->thumbnail != 'default.png') {
                $this->unlinkPhoto($data->thumbnail);
            }
            $data->delete();
        } catch (Exception $e) {
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
