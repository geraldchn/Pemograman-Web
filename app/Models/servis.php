<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Servis extends Model
{
    protected $table = 'servis';

    protected $fillable = [
        'kode_servis',
        'nama_pelanggan',
        'jenis_layanan',
        'keluhan',
        'status',
    ];

    public function penilaian(): HasOne
    {
        return $this->hasOne(Penilaian::class, 'servis_id');
    }
}