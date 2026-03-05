<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KawasanStrategiProvinsi extends Model
{
    protected $table = 'kawasan_strategi_provinsi';

    protected $fillable = [
        'klasifikasi_id',
        'nama',
        'deskripsi',
        'geojson_file',
        'tipe_geometri',
        'icon_titik',
        'tipe_garis',
        'warna',
        'processing_status',
        'processing_error',
    ];

    public function klasifikasi()
    {
        return $this->belongsTo(Klasifikasi::class);
    }
}
