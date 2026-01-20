<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSpasial extends Model
{
    protected $table = 'data_spasial';

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
        'processing_status',
        'processing_error',
    ];

    public function klasifikasi()
    {
        return $this->belongsTo(Klasifikasi::class);
    }
}
