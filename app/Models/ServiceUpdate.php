<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_booking_id', 'user_id', 'status', 'note'])]
class ServiceUpdate extends Model
{
    protected function casts(): array
    {
        return ['status' => ServiceStatus::class];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(ServiceBooking::class, 'service_booking_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wasEdited(): bool
    {
        return $this->updated_at->gt($this->created_at->copy()->addSeconds(2));
    }
}
