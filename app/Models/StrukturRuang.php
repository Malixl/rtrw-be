<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrukturRuang extends Model
{
    protected $table = 'struktur_ruang';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'klasifikasi_id',
        'nama',
        'deskripsi',
        'geojson_file',
        'tipe_geometri',
        'icon_titik',
        'tipe_garis',
        'warna',
    ];

    public function klasifikasi()
    {
        return $this->belongsTo(Klasifikasi::class);
    }
}
