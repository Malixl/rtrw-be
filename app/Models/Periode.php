<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tahun_mulai',
        'tahun_akhir',
    ];

    public function rtrws()
    {
        return $this->hasMany(Rtrw::class, 'periode_id');
    }
}
