<?php

namespace Database\Factories;

use App\Models\Court;
use App\Models\CourtClosure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourtClosure>
 */
class CourtClosureFactory extends Factory
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
            'date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'reason' => fake()->sentence(3),
        ];
    }

    /**
     * A closure on a specific date.
     */
    public function on(string $date): static
    {
        return $this->state(fn (array $attributes): array => [
            'date' => $date,
        ]);
    }
}
