<?php

namespace App\Support;

use App\Models\Booking;
use Carbon\CarbonImmutable;

class BookingSummary
{
    /**
     * Shape a booking for admin tables (expects `sport` to be loaded).
     *
     * @return array{
     *     id: int,
     *     date: string,
     *     date_full: string,
     *     time: string,
     *     sport: string,
     *     guest_name: string,
     *     guest_phone: string,
     *     status: string
     * }
     */
    public static function make(Booking $booking): array
    {
        return [
            'id' => $booking->id,
            'date' => $booking->booking_date->format('M j'),
            'date_full' => $booking->booking_date->format('M j, Y'),
            'time' => CarbonImmutable::parse($booking->start_time)->format('g:i A'),
            'sport' => $booking->sport->name,
            'guest_name' => $booking->guest_name,
            'guest_phone' => $booking->guest_phone,
            'status' => $booking->status->value,
        ];
    }
}
