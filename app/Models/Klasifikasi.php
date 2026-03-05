<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klasifikasi extends Model
{
    protected $table = 'klasifikasi';

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
        return $this->hasMany(Polaruang::class);
    }

    public function strukturRuang()
    {
        return $this->hasMany(StrukturRuang::class);
    }

    public function ketentuanKhusus()
    {
        return $this->hasMany(KetentuanKhusus::class);
    }

    public function dokumen()
    {
        return $this->hasMany(Dokumen::class);
    }

    public function kawasanStrategiProvinsi()
    {
        return $this->hasMany(KawasanStrategiProvinsi::class);
    }

    public function dataSpasial()
    {
        return $this->hasMany(DataSpasial::class);
    }

    public function batasAdministrasi()
    {
        return $this->hasMany(BatasAdministrasi::class);
    }
}
