<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\SlotStatus;
use App\Models\Booking;
use App\Models\Court;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function __construct(private readonly int $slotMinutes = 60) {}

    /**
     * Build the slot grid for a court on a single date.
     *
     * @return array{
     *     date: string,
     *     closed: bool,
     *     closed_reason: string|null,
     *     slots: list<array{start: string, end: string, label: string, status: string, selectable: bool}>
     * }
     */
    public function forCourtAndDate(Court $court, CarbonImmutable $date): array
    {
        $date = $date->startOfDay();

        $windows = $court->operatingHours()
            ->where('day_of_week', $date->dayOfWeek)
            ->orderBy('open_time')
            ->get();

        $closure = $court->closures()
            ->whereDate('date', $date->toDateString())
            ->first();

        if ($closure !== null) {
            return $this->closedDay($date, $closure->reason);
        }

        if ($windows->isEmpty()) {
            return $this->closedDay($date, 'Closed on this day');
        }

        // One physical court: any sport's pending/confirmed booking blocks the slot.
        $bookings = $court->bookings()
            ->whereDate('booking_date', $date->toDateString())
            ->occupying()
            ->get(['start_time', 'end_time', 'status']);

        $now = CarbonImmutable::now();
        $slots = [];

        foreach ($windows as $window) {
            $cursor = $this->at($date, $window->open_time);
            $closesAt = $this->at($date, $window->close_time);

            while ($cursor->lt($closesAt)) {
                $slotEnd = $cursor->addMinutes($this->slotMinutes);

                if ($slotEnd->gt($closesAt)) {
                    break;
                }

                $status = $this->statusFor($cursor, $slotEnd, $bookings, $now, $date);

                $slots[] = [
                    'start' => $cursor->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'label' => $cursor->format('g:i A'),
                    'status' => $status->value,
                    'selectable' => $status->isSelectable(),
                ];

                $cursor = $slotEnd;
            }
        }

        return [
            'date' => $date->toDateString(),
            'closed' => false,
            'closed_reason' => null,
            'slots' => $slots,
        ];
    }

    /**
     * Build the closed-day payload (closure or no operating hours).
     *
     * @return array{date: string, closed: bool, closed_reason: string|null, slots: list<array{start: string, end: string, label: string, status: string, selectable: bool}>}
     */
    private function closedDay(CarbonImmutable $date, ?string $reason): array
    {
        return [
            'date' => $date->toDateString(),
            'closed' => true,
            'closed_reason' => $reason,
            'slots' => [],
        ];
    }

    /**
     * Resolve the state of a single slot.
     *
     * @param  Collection<int, Booking>  $bookings
     */
    private function statusFor(
        CarbonImmutable $start,
        CarbonImmutable $end,
        Collection $bookings,
        CarbonImmutable $now,
        CarbonImmutable $date,
    ): SlotStatus {
        if ($start->lte($now)) {
            return SlotStatus::Past;
        }

        foreach ($bookings as $booking) {
            $bookingStart = $this->at($date, $booking->start_time);
            $bookingEnd = $this->at($date, $booking->end_time);

            if ($start->lt($bookingEnd) && $bookingStart->lt($end)) {
                return $booking->status === BookingStatus::Confirmed
                    ? SlotStatus::Booked
                    : SlotStatus::Pending;
            }
        }

        return SlotStatus::Free;
    }

    /**
     * Anchor a "HH:MM[:SS]" time string onto the given date.
     */
    private function at(CarbonImmutable $date, string $time): CarbonImmutable
    {
        [$hour, $minute] = array_pad(explode(':', $time), 2, '0');

        return $date->setTime((int) $hour, (int) $minute);
    }
}
