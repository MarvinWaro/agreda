<?php

namespace Database\Factories;

use App\Models\Court;
use App\Models\OperatingHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OperatingHour>
 */
class OperatingHourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'court_id' => Court::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'open_time' => '08:00:00',
            'close_time' => '22:00:00',
        ];
    }

    /**
     * Operating hours for a specific weekday (0 = Sunday ... 6 = Saturday).
     */
    public function forDay(int $dayOfWeek, string $open = '08:00:00', string $close = '22:00:00'): static
    {
        return $this->state(fn (array $attributes): array => [
            'day_of_week' => $dayOfWeek,
            'open_time' => $open,
            'close_time' => $close,
        ]);
    }
}
