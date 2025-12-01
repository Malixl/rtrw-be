<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndikasiProgram extends Model
{
    protected $table = 'indikasi_program';
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
