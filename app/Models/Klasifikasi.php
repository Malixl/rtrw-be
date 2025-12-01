<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klasifikasi extends Model
{
    protected $table = 'klasifikasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'rtrw_id',
        'nama',
        'deskripsi',
        'tipe',
    ];

    public function rtrw()
    {
        return $this->belongsTo(Rtrw::class);
    }

    public function polaRuang()
    {
        return $this->hasMany(PolaRuang::class);
    }

    public function strukturRuang()
    {
        return $this->hasMany(StrukturRuang::class);
    }

    public function ketentuanKhusus()
    {
        return $this->hasMany(KetentuanKhusus::class);
    }
    public function indikasiProgram()
    {
        return $this->hasMany(indikasiProgram::class);
    }
    public function pkkprl()
    {
        return $this->hasMany(Pkkprl::class);
    }
}
