<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 20);

        return [
            'user_id' => null,
            'court_id' => Court::factory(),
            'sport_id' => Sport::factory(),
            'guest_name' => fake()->name(),
            'guest_phone' => fake()->numerify('09#########'),
            'booking_date' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', $startHour + 1),
            'status' => BookingStatus::Pending,
            'total_price' => fake()->randomFloat(2, 200, 800),
            'notes' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BookingStatus::Pending,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BookingStatus::Confirmed,
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BookingStatus::Declined,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BookingStatus::Cancelled,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => BookingStatus::Completed,
        ]);
    }

    /**
     * Pin the booking to a specific date and one-hour slot.
     */
    public function forSlot(string $date, string $startTime, ?string $endTime = null): static
    {
        return $this->state(function (array $attributes) use ($date, $startTime, $endTime): array {
            $start = strlen($startTime) === 5 ? $startTime.':00' : $startTime;
            $end = $endTime ?? sprintf('%02d:00:00', ((int) substr($start, 0, 2)) + 1);

            return [
                'booking_date' => $date,
                'start_time' => $start,
                'end_time' => strlen($end) === 5 ? $end.':00' : $end,
            ];
        });
    }
}
