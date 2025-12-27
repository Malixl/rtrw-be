<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayerGroup extends Model
{
    protected $table = 'layer_groups';

    // Public API uses `layer_group_name`, DB column remains `nama_layer_group`.
    protected $fillable = [
        'layer_group_name',
        'deskripsi',
        'urutan_tampil',
    ];

    public function klasifikasis()
    {
        return $this->hasMany(Klasifikasi::class, 'layer_group_id');
    }

    // Map public attribute `layer_group_name` to DB column `nama_layer_group`.
    public function getLayerGroupNameAttribute()
    {
        return $this->attributes['nama_layer_group'] ?? null;
    }

    public function setLayerGroupNameAttribute($value)
    {
        $this->attributes['nama_layer_group'] = $value;
    }
}
