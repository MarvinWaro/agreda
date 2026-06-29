<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Sport;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly BookingPricer $pricer,
    ) {}

    /**
     * Create a pending booking request for a contiguous range of one-hour
     * slots, guarding against two visitors grabbing overlapping times.
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
        string $endTime,
        array $guest,
    ): Booking {
        $grid = $this->availability->forCourtAndDate($court, $date);

        if ($grid['closed']) {
            throw ValidationException::withMessages([
                'date' => 'The court is closed on that date.',
            ]);
        }

        $range = $this->slotsInRange(collect($grid['slots']), $startTime, $endTime);

        $startColumn = $startTime.':00';
        $endColumn = $endTime.':00';
        $price = $this->priceForRange($sport, $date, $range);

        return DB::transaction(function () use ($sport, $court, $date, $guest, $startColumn, $endColumn, $price): Booking {
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
                    'start_time' => 'That time was just taken. Please pick another.',
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
                'total_price' => $price,
            ]);
        });
    }

    /**
     * Pull the contiguous, selectable run of slots covering [start, end).
     *
     * @param  Collection<int, array{start: string, end: string, label: string, status: string, selectable: bool}>  $slots
     * @return Collection<int, array{start: string, end: string, label: string, status: string, selectable: bool}>
     *
     * @throws ValidationException
     */
    private function slotsInRange(Collection $slots, string $startTime, string $endTime): Collection
    {
        $range = $slots
            ->filter(fn (array $slot): bool => $slot['start'] >= $startTime && $slot['end'] <= $endTime)
            ->values();

        $isContiguous = $range->every(
            fn (array $slot, int $index): bool => $index === 0 || $range[$index - 1]['end'] === $slot['start'],
        );

        $coversRange = $range->isNotEmpty()
            && $range->first()['start'] === $startTime
            && $range->last()['end'] === $endTime;

        $allFree = $range->every(fn (array $slot): bool => $slot['selectable'] === true);

        if (! $coversRange || ! $isContiguous || ! $allFree) {
            throw ValidationException::withMessages([
                'start_time' => 'That time range is no longer available.',
            ]);
        }

        return $range;
    }

    /**
     * Sum each one-hour slot's rate across the booked range.
     *
     * @param  Collection<int, array{start: string, end: string, label: string, status: string, selectable: bool}>  $range
     */
    private function priceForRange(Sport $sport, CarbonImmutable $date, Collection $range): string
    {
        $total = $range->sum(
            fn (array $slot): float => (float) $this->pricer->priceFor($sport, $this->startMoment($date, $slot['start'])),
        );

        return number_format($total, 2, '.', '');
    }

    private function startMoment(CarbonImmutable $date, string $startTime): CarbonImmutable
    {
        [$hour, $minute] = array_pad(explode(':', $startTime), 2, '0');

        return $date->startOfDay()->setTime((int) $hour, (int) $minute);
    }
}
