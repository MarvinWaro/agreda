<?php

use App\Jobs\NotifyGuestOfDecision;
use App\Jobs\NotifyOwnerOfBooking;
use App\Models\Booking;
use App\Models\Court;
use App\Models\OperatingHour;
use App\Models\Sport;
use App\Models\User;
use App\Services\FacebookService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\mock;

test('submitting a booking dispatches the owner notification', function () {
    Queue::fake();
    $this->travelTo(CarbonImmutable::parse('2026-06-25 09:00:00'));

    $court = Court::factory()->create();
    $sport = Sport::factory()->create();
    $court->sports()->attach($sport);
    OperatingHour::factory()
        ->for($court)
        ->forDay(CarbonImmutable::parse('2026-06-26')->dayOfWeek, '08:00:00', '22:00:00')
        ->create();

    $this->post(route('booking.store'), [
        'sport_id' => $sport->id,
        'date' => '2026-06-26',
        'start_time' => '09:00',
        'guest_name' => 'Juan Dela Cruz',
        'guest_phone' => '09170000000',
    ]);

    Queue::assertPushed(NotifyOwnerOfBooking::class, 1);
});

test('confirming a booking dispatches the guest notification', function () {
    Queue::fake();
    $booking = Booking::factory()->pending()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.bookings.confirm', $booking));

    Queue::assertPushed(NotifyGuestOfDecision::class, 1);
});

test('declining a booking dispatches the guest notification', function () {
    Queue::fake();
    $booking = Booking::factory()->pending()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.bookings.decline', $booking));

    Queue::assertPushed(NotifyGuestOfDecision::class, 1);
});

test('a non-pending booking does not notify the guest', function () {
    Queue::fake();
    $booking = Booking::factory()->confirmed()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.bookings.confirm', $booking));

    Queue::assertNotPushed(NotifyGuestOfDecision::class);
});

test('the owner notification job sends a message and records it', function () {
    $booking = Booking::factory()->pending()->create();

    mock(FacebookService::class)
        ->shouldReceive('sendOwnerMessage')
        ->once();

    (new NotifyOwnerOfBooking($booking))->handle(app(FacebookService::class));

    $this->assertDatabaseHas('booking_notifications', [
        'booking_id' => $booking->id,
        'channel' => 'facebook',
        'status' => 'sent',
    ]);
});

test('the guest notification job sends a message and records it', function () {
    $booking = Booking::factory()->confirmed()->create();

    mock(FacebookService::class)
        ->shouldReceive('sendGuestMessage')
        ->once();

    (new NotifyGuestOfDecision($booking))->handle(app(FacebookService::class));

    $this->assertDatabaseHas('booking_notifications', [
        'booking_id' => $booking->id,
        'channel' => 'facebook',
        'status' => 'sent',
    ]);
});
