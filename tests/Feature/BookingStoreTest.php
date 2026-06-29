<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\OperatingHour;
use App\Models\Sport;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Active court offering a sport, open 8am–10pm on the date's weekday.
 *
 * @return array{0: Court, 1: Sport}
 */
function bookingCourt(string $date): array
{
    $court = Court::factory()->create();
    $sport = Sport::factory()->create(['rate_offpeak' => 500, 'rate_peak' => 800]);
    $court->sports()->attach($sport);

    OperatingHour::factory()
        ->for($court)
        ->forDay(CarbonImmutable::parse($date)->dayOfWeek, '08:00:00', '22:00:00')
        ->create();

    return [$court, $sport];
}

test('the booking page lists only active sports', function () {
    Court::factory()->create();
    Sport::factory()->count(3)->create();
    Sport::factory()->inactive()->create();

    $this->get(route('booking.create'))
        ->assertInertia(fn (Assert $page) => $page->component('public/book')->has('sports', 3));
});

test('a visitor can submit a booking request', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [$court, $sport] = bookingCourt('2026-06-26');

    $response = $this->post(route('booking.store'), [
        'sport_id' => $sport->id,
        'date' => '2026-06-26',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'guest_name' => 'Juan Dela Cruz',
        'guest_phone' => '09171234567',
        'notes' => 'Birthday game',
    ]);

    $booking = Booking::sole();

    $response->assertRedirect(route('booking.done', $booking));

    expect($booking->status)->toBe(BookingStatus::Pending)
        ->and($booking->court_id)->toBe($court->id)
        ->and($booking->sport_id)->toBe($sport->id)
        ->and($booking->start_time)->toBe('09:00:00')
        ->and($booking->end_time)->toBe('10:00:00')
        ->and($booking->total_price)->toBe('500.00')
        ->and($booking->guest_name)->toBe('Juan Dela Cruz');
});

test('evening slots are charged the peak rate', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [, $sport] = bookingCourt('2026-06-26');

    $this->post(route('booking.store'), [
        'sport_id' => $sport->id,
        'date' => '2026-06-26',
        'start_time' => '18:00',
        'end_time' => '19:00',
        'guest_name' => 'Mia Reyes',
        'guest_phone' => '09170000000',
    ]);

    expect(Booking::sole()->total_price)->toBe('800.00');
});

test('a visitor can book a contiguous multi-hour range', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [, $sport] = bookingCourt('2026-06-26');

    $response = $this->post(route('booking.store'), [
        'sport_id' => $sport->id,
        'date' => '2026-06-26',
        'start_time' => '09:00',
        'end_time' => '11:00',
        'guest_name' => 'Multi Hour',
        'guest_phone' => '09170000000',
    ]);

    $booking = Booking::sole();

    $response->assertRedirect(route('booking.done', $booking));

    expect($booking->start_time)->toBe('09:00:00')
        ->and($booking->end_time)->toBe('11:00:00')
        ->and($booking->total_price)->toBe('1000.00');
});

test('a multi-hour range overlapping an existing booking is rejected', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [$court, $sport] = bookingCourt('2026-06-26');

    Booking::factory()->for($court)->for($sport)->confirmed()->forSlot('2026-06-26', '10:00', '11:00')->create();

    $this->from(route('booking.create'))
        ->post(route('booking.store'), [
            'sport_id' => $sport->id,
            'date' => '2026-06-26',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'guest_name' => 'Blocked Guest',
            'guest_phone' => '09170000000',
        ])
        ->assertRedirect(route('booking.create'))
        ->assertSessionHasErrors('start_time');

    expect(Booking::count())->toBe(1);
});

test('a slot that is already taken is rejected and creates no booking', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [$court, $sport] = bookingCourt('2026-06-26');

    Booking::factory()->for($court)->for($sport)->confirmed()->forSlot('2026-06-26', '09:00')->create();

    $this->from(route('booking.create'))
        ->post(route('booking.store'), [
            'sport_id' => $sport->id,
            'date' => '2026-06-26',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'guest_name' => 'Late Guest',
            'guest_phone' => '09170000000',
        ])
        ->assertRedirect(route('booking.create'))
        ->assertSessionHasErrors('start_time');

    expect(Booking::count())->toBe(1);
});

test('a booking in the past is rejected', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [, $sport] = bookingCourt('2026-06-24');

    $this->from(route('booking.create'))
        ->post(route('booking.store'), [
            'sport_id' => $sport->id,
            'date' => '2026-06-24',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'guest_name' => 'Time Traveller',
            'guest_phone' => '09170000000',
        ])
        ->assertSessionHasErrors('date');

    expect(Booking::count())->toBe(0);
});

test('booking validation requires the key fields', function () {
    $this->from(route('booking.create'))
        ->post(route('booking.store'), [])
        ->assertSessionHasErrors(['sport_id', 'date', 'start_time', 'end_time', 'guest_name', 'guest_phone']);
});

test('the confirmation page shows the booking summary', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));
    [$court, $sport] = bookingCourt('2026-06-26');

    $booking = Booking::factory()->for($court)->for($sport)->pending()->forSlot('2026-06-26', '09:00')->create();

    $this->get(route('booking.done', $booking))
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/booking-confirmation')
            ->where('booking.reference', $booking->id)
            ->where('booking.status', 'pending')
            ->where('booking.sport', $sport->name));
});
