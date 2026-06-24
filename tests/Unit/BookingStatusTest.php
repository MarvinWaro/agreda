<?php

use App\Enums\BookingStatus;

test('pending and confirmed statuses occupy a slot', function () {
    expect(BookingStatus::Pending->occupiesSlot())->toBeTrue()
        ->and(BookingStatus::Confirmed->occupiesSlot())->toBeTrue();
});

test('declined, cancelled and completed statuses do not occupy a slot', function () {
    expect(BookingStatus::Declined->occupiesSlot())->toBeFalse()
        ->and(BookingStatus::Cancelled->occupiesSlot())->toBeFalse()
        ->and(BookingStatus::Completed->occupiesSlot())->toBeFalse();
});

test('occupying values returns only the pending and confirmed values', function () {
    expect(BookingStatus::occupyingValues())->toBe(['pending', 'confirmed']);
});
