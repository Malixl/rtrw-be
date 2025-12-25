<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayerGroup extends Model
{
    protected $table = 'layer_groups';

    protected $fillable = [
        'nama_layer_group',
        'deskripsi',
        'urutan_tampil',
    ];

    public function klasifikasis()
    {
        return $this->hasMany(Klasifikasi::class, 'layer_group_id');
    }
}
