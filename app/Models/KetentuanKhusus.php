<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KetentuanKhusus extends Model
{
    protected $table = 'ketentuan_khusus';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'klasifikasi_id',
        'nama',
        'deskripsi',
        'geojson_file',
        'warna',
    ];

    public function klasifikasi()
    {
        return $this->belongsTo(Klasifikasi::class);
    }
}
