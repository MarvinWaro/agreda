<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Sport;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly BookingPricer $pricer,
    ) {}

    /**
     * Create a pending booking request for a slot, guarding against
     * two visitors grabbing the same slot at the same time.
     *
     * @param  array{guest_name: string, guest_phone: string, notes: string|null}  $guest
     *
     * @throws ValidationException
     */
    public function request(
        Sport $sport,
        Court $court,
        CarbonImmutable $date,
        string $startTime,
        array $guest,
    ): Booking {
        $grid = $this->availability->forCourtAndDate($court, $date);

        if ($grid['closed']) {
            throw ValidationException::withMessages([
                'date' => 'The court is closed on that date.',
            ]);
        }

        $slot = collect($grid['slots'])->firstWhere('start', $startTime);

        if ($slot === null || $slot['selectable'] !== true) {
            throw ValidationException::withMessages([
                'start_time' => 'That time slot is no longer available.',
            ]);
        }

        $start = $this->startMoment($date, $startTime);
        $startColumn = $startTime.':00';
        $endColumn = $slot['end'].':00';

        return DB::transaction(function () use ($sport, $court, $date, $guest, $start, $startColumn, $endColumn): Booking {
            // Re-check inside the transaction with a row lock: another request
            // may have taken an overlapping slot since we read availability.
            $taken = Booking::query()
                ->where('court_id', $court->id)
                ->whereDate('booking_date', $date->toDateString())
                ->occupying()
                ->where('start_time', '<', $endColumn)
                ->where('end_time', '>', $startColumn)
                ->lockForUpdate()
                ->exists();

            if ($taken) {
                throw ValidationException::withMessages([
                    'start_time' => 'That time slot was just taken. Please pick another.',
                ]);
            }

            return Booking::create([
                'court_id' => $court->id,
                'sport_id' => $sport->id,
                'guest_name' => $guest['guest_name'],
                'guest_phone' => $guest['guest_phone'],
                'notes' => $guest['notes'],
                'booking_date' => $date->toDateString(),
                'start_time' => $startColumn,
                'end_time' => $endColumn,
                'status' => BookingStatus::Pending,
                'total_price' => $this->pricer->priceFor($sport, $start),
            ]);
        });
    }

    private function startMoment(CarbonImmutable $date, string $startTime): CarbonImmutable
    {
        [$hour, $minute] = array_pad(explode(':', $startTime), 2, '0');

        return $date->startOfDay()->setTime((int) $hour, (int) $minute);
    }
}
