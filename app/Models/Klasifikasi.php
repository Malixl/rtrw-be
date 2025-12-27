<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klasifikasi extends Model
{
    protected $table = 'klasifikasi';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'layer_group_id',
        'nama',
        'deskripsi',
        'tipe',
    ];

    public function layerGroup()
    {
        return $this->belongsTo(LayerGroup::class, 'layer_group_id');
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

    public function dataSpasial()
    {
        return $this->hasMany(DataSpasial::class);
    }
}
