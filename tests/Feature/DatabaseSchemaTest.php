<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\OperatingHour;
use App\Models\Setting;
use App\Models\Sport;
use App\Models\User;
use Carbon\CarbonInterface;

test('a court and its sports are linked through the pivot', function () {
    $court = Court::factory()->create();
    $sports = Sport::factory()->count(4)->create();

    $court->sports()->attach($sports);

    expect($court->sports)->toHaveCount(4)
        ->and($sports->first()->courts->pluck('id'))->toContain($court->id);
});

test('a booking belongs to a court and sport with an optional user', function () {
    $booking = Booking::factory()->create();

    expect($booking->court)->toBeInstanceOf(Court::class)
        ->and($booking->sport)->toBeInstanceOf(Sport::class)
        ->and($booking->user)->toBeNull();
});

test('booking status casts to the BookingStatus enum', function () {
    $booking = Booking::factory()->confirmed()->create();

    expect($booking->refresh()->status)->toBe(BookingStatus::Confirmed);
});

test('booking_date casts to Carbon while the time columns stay strings', function () {
    $booking = Booking::factory()->forSlot('2026-07-01', '09:00')->create()->refresh();

    expect($booking->booking_date)->toBeInstanceOf(CarbonInterface::class)
        ->and($booking->booking_date->toDateString())->toBe('2026-07-01')
        ->and($booking->start_time)->toBe('09:00:00')
        ->and($booking->end_time)->toBe('10:00:00');
});

test('the occupying scope returns only pending and confirmed bookings', function () {
    Booking::factory()->pending()->create();
    Booking::factory()->confirmed()->create();
    Booking::factory()->declined()->create();
    Booking::factory()->cancelled()->create();

    expect(Booking::query()->occupying()->count())->toBe(2);
});

test('an admin role grants admin access while a role-less user is denied', function () {
    $admin = User::factory()->admin()->create();
    $guest = User::factory()->create();

    expect($admin->can('admin.access'))->toBeTrue()
        ->and($guest->can('admin.access'))->toBeFalse();
});

test('the database seeder provisions a court, four sports, hours and an owner', function () {
    $this->seed();

    expect(Court::count())->toBe(1)
        ->and(Sport::count())->toBe(4)
        ->and(OperatingHour::count())->toBe(7)
        ->and(Setting::count())->toBeGreaterThanOrEqual(5)
        ->and(Court::first()->sports)->toHaveCount(4);

    $owner = User::query()->where('email', 'owner@agreda.test')->first();

    expect($owner)->not->toBeNull()
        ->and($owner->hasRole('Super Admin'))->toBeTrue();
});

test('the database seeder is idempotent', function () {
    $this->seed();
    $this->seed();

    expect(Sport::count())->toBe(4)
        ->and(Court::count())->toBe(1)
        ->and(OperatingHour::count())->toBe(7);
});
