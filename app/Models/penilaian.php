<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    protected $table = 'penilaian';

    protected $fillable = [
        'servis_id',
        'rating',
        'ulasan',
    ];

    public function servis(): BelongsTo
    {
        return $this->belongsTo(Servis::class, 'servis_id');
    }
}