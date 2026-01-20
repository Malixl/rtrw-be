<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatasAdministrasi extends Model
{
    use HasFactory;

    protected $table = 'batas_administrasi';

    protected $fillable = [
        'nama',
        'deskripsi',
        'geojson_file',
        'tipe_geometri',
        'tipe_garis',
        'warna',
        'klasifikasi_id',
        'processing_status',
        'processing_error',
    ];

    public function klasifikasi()
    {
        return $this->belongsTo(Klasifikasi::class);
    }
}
