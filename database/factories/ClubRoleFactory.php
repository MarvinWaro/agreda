<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\ClubRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClubRole>
 */
class ClubRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'name' => fake()->unique()->jobTitle(),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
        ]);
    }
}
