<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $table = 'dokumen';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'klasifikasi_id',
        'nama',
        'file_dokumen',
    ];

    public function klasifikasi()
    {
        return $this->belongsTo(Klasifikasi::class);
    }
}
