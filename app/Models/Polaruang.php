<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Polaruang extends Model
{
    protected $table = 'polaruang';
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
