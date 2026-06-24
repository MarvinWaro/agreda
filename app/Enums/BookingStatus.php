<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Declined => 'Declined',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
        };
    }

    /**
     * Whether a booking in this status occupies its time slot, making the
     * slot unavailable to other requests. Centralises the availability rule
     * from the spec: pending and confirmed bookings both block a slot.
     */
    public function occupiesSlot(): bool
    {
        return match ($this) {
            self::Pending, self::Confirmed => true,
            self::Declined, self::Cancelled, self::Completed => false,
        };
    }

    /**
     * Statuses that occupy a slot, for use in query constraints.
     *
     * @return array<int, string>
     */
    public static function occupyingValues(): array
    {
        return array_values(array_map(
            static fn (self $status): string => $status->value,
            array_filter(self::cases(), static fn (self $status): bool => $status->occupiesSlot()),
        ));
    }
}
