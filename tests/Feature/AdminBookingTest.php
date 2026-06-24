<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from the admin area', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('non-admin users cannot reach the admin area', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admins can view the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/dashboard')
            ->has('stats')
            ->has('latest'));
});

test('the dashboard reports pending, today and week counts', function () {
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));

    Booking::factory()->pending()->forSlot('2026-06-25', '09:00')->create();
    Booking::factory()->confirmed()->forSlot('2026-06-25', '10:00')->create();
    Booking::factory()->pending()->forSlot('2026-06-26', '09:00')->create();
    Booking::factory()->declined()->forSlot('2026-06-25', '11:00')->create();
    Booking::factory()->pending()->forSlot('2026-06-30', '09:00')->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.pending', 3)
            ->where('stats.today', 2)
            ->where('stats.week', 3)
            ->has('latest', 3));
});

test('the bookings index can filter by status', function () {
    Booking::factory()->pending()->create();
    Booking::factory()->confirmed()->create();
    Booking::factory()->confirmed()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.bookings.index', ['status' => 'confirmed']))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/bookings')
            ->has('bookings.data', 2)
            ->where('filters.status', 'confirmed'));
});

test('an admin can confirm a pending booking', function () {
    $booking = Booking::factory()->pending()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.bookings.index'))
        ->patch(route('admin.bookings.confirm', $booking))
        ->assertRedirect(route('admin.bookings.index'));

    expect($booking->refresh()->status)->toBe(BookingStatus::Confirmed);
});

test('an admin can decline a pending booking', function () {
    $booking = Booking::factory()->pending()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.bookings.index'))
        ->patch(route('admin.bookings.decline', $booking))
        ->assertRedirect(route('admin.bookings.index'));

    expect($booking->refresh()->status)->toBe(BookingStatus::Declined);
});

test('a non-pending booking is not changed by confirm', function () {
    $booking = Booking::factory()->declined()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.bookings.index'))
        ->patch(route('admin.bookings.confirm', $booking));

    expect($booking->refresh()->status)->toBe(BookingStatus::Declined);
});

test('non-admins cannot confirm bookings', function () {
    $booking = Booking::factory()->pending()->create();

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.bookings.confirm', $booking))
        ->assertForbidden();

    expect($booking->refresh()->status)->toBe(BookingStatus::Pending);
});
