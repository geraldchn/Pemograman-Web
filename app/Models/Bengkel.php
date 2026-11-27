<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'nama',
    'alamat',
    'telepon',
    'link_maps',
    'foto',
    'jam_buka',
    'jam_tutup',
    'kuota_harian',
    'is_open',
])]
class Bengkel extends Model
{
    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'kuota_harian' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ServiceBooking::class, 'bengkel_id');
    }

    public function fotoUrl(): string
    {
        if ($this->foto && Storage::disk('public')->exists($this->foto)) {
            return asset('storage/' . $this->foto);
        }

        return asset('images/logo/favicon.png');
    }

    /**
     * Mengecek apakah bengkel sudah memiliki riwayat booking (untuk syarat hapus).
     */
    public function hasBookings(): bool
    {
        if (Schema::hasTable('service_bookings') && Schema::hasColumn('service_bookings', 'bengkel_id')) {
            return DB::table('service_bookings')->where('bengkel_id', $this->id)->exists();
        }

        return false;
    }

    /**
     * Menghitung kuota terpakai hari ini (terhubung ke tabel booking Modul 3).
     */
    public function kuotaTerpakaiHariIni(): int
    {
        if (Schema::hasTable('service_bookings') && Schema::hasColumn('service_bookings', 'bengkel_id')) {
            return DB::table('service_bookings')
                ->where('bengkel_id', $this->id)
                ->whereDate('scheduled_at', now()->toDateString())
                ->count();
        }

        return 0;
    }

    public function sisaKuotaHariIni(): int
    {
        return max(0, $this->kuota_harian - $this->kuotaTerpakaiHariIni());
    }
}