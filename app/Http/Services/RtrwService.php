<?php

namespace App\Http\Services;

use App\Models\Rtrw;
use Exception;
use Illuminate\Support\Facades\DB;

class RtrwService
{

    protected $model;

    public function __construct(Rtrw $model)
    {
        $this->model = $model;
    }

    public function getAll($request)
    {
        $per_page = $request->per_page ?? 10;
        $data = $this->model->with('periode')->orderBy('created_at');

        if ($search = $request->query('search')) {
            $data->where('nama', 'like', '%' . $search . '%');
        }

        if ($request->page) {
            $data = $data->paginate($per_page);
        } else {
            $data = $data->get();
        }

        return $data;
    }

    public function getKlasifikasiByRTRW($rtrwId)
    {
        $rtrw = $this->model->findOrFail($rtrwId);

        $klasifikasi_pola_ruang = $rtrw->klasifikasis()
            ->whereHas('polaRuang')   // hanya yg punya relasi polaRuang
            ->with('polaRuang')       // load datanya
            ->get();
        $klasifikasi_struktur_ruang = $rtrw->klasifikasis()
            ->whereHas('strukturRuang')   // hanya yg punya relasi strukturRuang
            ->with('strukturRuang')
            ->get();
        $klasifikasi_ketentuan_khusus = $rtrw->klasifikasis()
            ->whereHas('ketentuanKhusus')   // hanya yg punya relasi strukturRuang
            ->with('ketentuanKhusus')
            ->get();
        $klasifikasi_indikasi_program = $rtrw->klasifikasis()
            ->whereHas('indikasiProgram')   // hanya yg punya relasi strukturRuang
            ->with('indikasiProgram')
            ->get();
        $klasifikasi_pkkprl = $rtrw->klasifikasis()
            ->whereHas('pkkprl')   // hanya yg punya relasi strukturRuang
            ->with('pkkprl')
            ->get();

        return [
            'rtrw' => [
                'id' => $rtrw->id,
                'nama' => $rtrw->nama,
                'deskripsi' => $rtrw->deskripsi,
            ],
            'klasifikasi_pola_ruang' => $klasifikasi_pola_ruang,
            'klasifikasi_struktur_ruang' => $klasifikasi_struktur_ruang,
            'klasifikasi_ketentuan_khusus' => $klasifikasi_ketentuan_khusus,
            'klasifikasi_indikasi_program' => $klasifikasi_indikasi_program,
            'klasifikasi_pkkprl' => $klasifikasi_pkkprl,
        ];
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
        return $this->model->findOrFail($id);
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

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
