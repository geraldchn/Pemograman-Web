<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id', 'brand', 'model', 'serial_number', 'purchase_year', 'processor', 'ram_gb',
    'storage', 'gpu', 'screen_size', 'os', 'notes', 'photo',
])]
class Laptop extends Model
{
    public const BRANDS = ['Acer', 'Apple', 'ASUS', 'Dell', 'HP', 'Huawei', 'Lenovo', 'MSI', 'Samsung', 'Toshiba', 'Xiaomi'];

    protected function casts(): array
    {
        return ['screen_size' => 'float'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Newest booking first. */
    public function bookings(): HasMany
    {
        return $this->hasMany(ServiceBooking::class)->orderByDesc('id');
    }

    public function latestBooking(): HasOne
    {
        return $this->hasOne(ServiceBooking::class)->latestOfMany();
    }

    public function displayName(): string
    {
        return trim($this->brand.' '.$this->model);
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    /** Spec label => display value, skipping anything the customer left empty. */
    public function specs(): array
    {
        return array_filter([
            'Processor' => $this->processor,
            'RAM' => $this->ram_gb ? $this->ram_gb.' GB' : null,
            'Storage' => $this->storage,
            'Graphics' => $this->gpu,
            'Screen' => $this->screen_size ? rtrim(rtrim(number_format($this->screen_size, 1), '0'), '.').'"' : null,
            'Operating system' => $this->os,
            'Purchase year' => $this->purchase_year,
        ], fn ($v) => filled($v));
    }

    /** Short one-line summary for cards, e.g. "Core i5-12500H · 16 GB · 512 GB SSD". */
    public function specSummary(): string
    {
        return collect([$this->processor, $this->ram_gb ? $this->ram_gb.' GB' : null, $this->storage])
            ->filter()->implode(' · ');
    }
}
