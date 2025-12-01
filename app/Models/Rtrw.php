<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rtrw extends Model
{
    protected $table = 'rtrw';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nama',
        'deskripsi',
        'periode_id',
    ];

    public function klasifikasis()
    {
        return $this->hasMany(Klasifikasi::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }
}
