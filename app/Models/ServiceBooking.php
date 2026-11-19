<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['laptop_id', 'issue', 'scheduled_at'])]
class ServiceBooking extends Model
{
    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime'];
    }

    public function laptop(): BelongsTo
    {
        return $this->belongsTo(Laptop::class);
    }

    /** Oldest first (chronological). */
    public function updates(): HasMany
    {
        return $this->hasMany(ServiceUpdate::class)->orderBy('id');
    }

    public function latestUpdate(): HasOne
    {
        return $this->hasOne(ServiceUpdate::class)->latestOfMany();
    }

    public function currentStatus(): ServiceStatus
    {
        return $this->latestUpdate?->status ?? ServiceStatus::Booked;
    }

    public function code(): string
    {
        return 'TC-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
