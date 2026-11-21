<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'service_booking_id',
        'kode_referensi',
        'nama_pelanggan',
        'jumlah_tagihan',
        'metode_pembayaran',
        'status_pembayaran',
        'dibayar_pada',
    ];

    protected $casts = [
        'jumlah_tagihan' => 'decimal:2',
        'dibayar_pada' => 'datetime',
    ];
}