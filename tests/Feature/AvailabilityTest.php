<?php

use App\Models\Booking;
use App\Models\Court;
use App\Models\CourtClosure;
use App\Models\OperatingHour;
use App\Models\Sport;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Create an active court that offers a sport, open on the date's weekday.
 *
 * @return array{0: Court, 1: Sport}
 */
function courtOpenOn(string $date, string $open = '08:00:00', string $close = '12:00:00'): array
{
    $court = Court::factory()->create();
    $sport = Sport::factory()->create();
    $court->sports()->attach($sport);

    OperatingHour::factory()
        ->for($court)
        ->forDay(CarbonImmutable::parse($date)->dayOfWeek, $open, $close)
        ->create();

    return [$court, $sport];
}

/**
 * @return Collection<string, array{start: string, end: string, label: string, status: string, selectable: bool}>
 */
function slotsFor(Court $court, string $date): Collection
{
    $result = app(AvailabilityService::class)->forCourtAndDate($court, CarbonImmutable::parse($date));

    return collect($result['slots'])->keyBy('start');
}

test('generates hourly free slots within operating hours', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [$court] = courtOpenOn('2026-06-26', '08:00:00', '12:00:00');

    $result = app(AvailabilityService::class)->forCourtAndDate($court, CarbonImmutable::parse('2026-06-26'));

    expect($result['closed'])->toBeFalse()
        ->and($result['slots'])->toHaveCount(4)
        ->and($result['slots'][0])->toMatchArray([
            'start' => '08:00',
            'end' => '09:00',
            'label' => '8:00 AM',
            'status' => 'free',
            'selectable' => true,
        ])
        ->and(collect($result['slots'])->pluck('status')->unique()->values()->all())->toBe(['free']);
});

test('confirmed and pending bookings block the overlapping slot for any sport', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [$court, $sport] = courtOpenOn('2026-06-26', '08:00:00', '12:00:00');

    $otherSport = Sport::factory()->create();
    $court->sports()->attach($otherSport);

    Booking::factory()->for($court)->for($sport)->confirmed()->forSlot('2026-06-26', '09:00')->create();
    Booking::factory()->for($court)->for($otherSport)->pending()->forSlot('2026-06-26', '10:00')->create();

    $slots = slotsFor($court, '2026-06-26');

    expect($slots['08:00']['status'])->toBe('free')
        ->and($slots['09:00']['status'])->toBe('booked')
        ->and($slots['09:00']['selectable'])->toBeFalse()
        ->and($slots['10:00']['status'])->toBe('pending')
        ->and($slots['11:00']['status'])->toBe('free');
});

test('declined and cancelled bookings leave the slot free', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [$court, $sport] = courtOpenOn('2026-06-26', '08:00:00', '12:00:00');

    Booking::factory()->for($court)->for($sport)->declined()->forSlot('2026-06-26', '09:00')->create();
    Booking::factory()->for($court)->for($sport)->cancelled()->forSlot('2026-06-26', '10:00')->create();

    $slots = slotsFor($court, '2026-06-26');

    expect($slots['09:00']['status'])->toBe('free')
        ->and($slots['10:00']['status'])->toBe('free');
});

test('a court closure marks the whole day closed with no slots', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [$court] = courtOpenOn('2026-06-26');

    CourtClosure::factory()->for($court)->on('2026-06-26')->create(['reason' => 'Maintenance']);

    $result = app(AvailabilityService::class)->forCourtAndDate($court, CarbonImmutable::parse('2026-06-26'));

    expect($result['closed'])->toBeTrue()
        ->and($result['closed_reason'])->toBe('Maintenance')
        ->and($result['slots'])->toBeEmpty();
});

test('a weekday with no operating hours is closed', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    $court = Court::factory()->create();

    $result = app(AvailabilityService::class)->forCourtAndDate($court, CarbonImmutable::parse('2026-06-26'));

    expect($result['closed'])->toBeTrue()
        ->and($result['slots'])->toBeEmpty();
});

test('slots earlier today are marked past and not selectable', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-26 10:30:00'));
    [$court] = courtOpenOn('2026-06-26', '08:00:00', '12:00:00');

    $slots = slotsFor($court, '2026-06-26');

    expect($slots['08:00']['status'])->toBe('past')
        ->and($slots['09:00']['status'])->toBe('past')
        ->and($slots['10:00']['status'])->toBe('past')
        ->and($slots['10:00']['selectable'])->toBeFalse()
        ->and($slots['11:00']['status'])->toBe('free');
});

test('the availability endpoint returns slot data as json', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [$court, $sport] = courtOpenOn('2026-06-26', '08:00:00', '10:00:00');

    $this->getJson(route('availability', ['sport_id' => $sport->id, 'date' => '2026-06-26']))
        ->assertSuccessful()
        ->assertJsonPath('closed', false)
        ->assertJsonPath('sport.id', $sport->id)
        ->assertJsonCount(2, 'slots')
        ->assertJsonPath('slots.0.status', 'free');
});

test('the availability endpoint validates sport_id and date', function () {
    $this->getJson(route('availability'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['sport_id', 'date']);
});
