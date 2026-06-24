<?php

namespace App\Enums;

enum SlotStatus: string
{
    case Free = 'free';
    case Pending = 'pending';
    case Booked = 'booked';
    case Past = 'past';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Pending => 'Pending',
            self::Booked => 'Booked',
            self::Past => 'Past',
            self::Closed => 'Closed',
        };
    }

    /**
     * Whether a visitor can select this slot to request a booking.
     */
    public function isSelectable(): bool
    {
        return $this === self::Free;
    }
}
