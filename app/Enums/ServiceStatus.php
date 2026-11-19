<?php

namespace App\Enums;

/**
 * Status of a laptop service. The current status of a booking is the status of
 * its latest update (or Booked when there are no updates yet).
 */
enum ServiceStatus: string
{
    case Booked = 'booked';
    case Received = 'received';
    case Diagnosing = 'diagnosing';
    case WaitingParts = 'waiting_parts';
    case Repairing = 'repairing';
    case Testing = 'testing';
    case ReadyForPickup = 'ready_for_pickup';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Booked => 'Booked',
            self::Received => 'Laptop Received',
            self::Diagnosing => 'Diagnosing',
            self::WaitingParts => 'Waiting for Parts',
            self::Repairing => 'Repair in Progress',
            self::Testing => 'Testing',
            self::ReadyForPickup => 'Ready for Pickup',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /** Matches the status color mapping in the Style Guide. */
    public function color(): string
    {
        return match ($this) {
            self::Booked, self::WaitingParts => 'warning',
            self::Received, self::Diagnosing, self::Repairing, self::Testing => 'accent',
            self::ReadyForPickup, self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function badgeClass(): string
    {
        return 'tc-badge-'.$this->color();
    }

    public function icon(): string
    {
        return match ($this) {
            self::Booked => 'fa-calendar-check',
            self::Received => 'fa-inbox',
            self::Diagnosing => 'fa-search',
            self::WaitingParts => 'fa-hourglass-half',
            self::Repairing => 'fa-wrench',
            self::Testing => 'fa-flask',
            self::ReadyForPickup => 'fa-box-open',
            self::Completed => 'fa-check',
            self::Cancelled => 'fa-ban',
        };
    }

    /** Position on the progress bar (0-6). Cancelled is off the path. */
    public function step(): ?int
    {
        return match ($this) {
            self::Booked => 0,
            self::Received => 1,
            self::Diagnosing => 2,
            self::WaitingParts, self::Repairing => 3,
            self::Testing => 4,
            self::ReadyForPickup => 5,
            self::Completed => 6,
            self::Cancelled => null,
        };
    }

    /** Completed and Cancelled end the service: no more updates can be added. */
    public function isTerminal(): bool
    {
        return $this === self::Completed || $this === self::Cancelled;
    }

    /** Suggested next status for the "Add update" form. */
    public function suggestedNext(): self
    {
        return match ($this) {
            self::Booked => self::Received,
            self::Received => self::Diagnosing,
            self::Diagnosing => self::Repairing,
            self::WaitingParts => self::Repairing,
            self::Repairing => self::Testing,
            self::Testing => self::ReadyForPickup,
            self::ReadyForPickup => self::Completed,
            default => $this,
        };
    }

    /** Statuses an admin can pick (Booked is implicit when the booking is created). */
    public static function selectable(): array
    {
        return array_values(array_filter(self::cases(), fn (self $s) => $s !== self::Booked));
    }

    public static function stepLabels(): array
    {
        return ['Booked', 'Received', 'Diagnosis', 'Repair', 'Testing', 'Ready', 'Done'];
    }
}
