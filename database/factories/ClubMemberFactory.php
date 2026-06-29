<?php

namespace Database\Factories;

use App\Enums\ClubMemberStatus;
use App\Enums\Sex;
use App\Models\Club;
use App\Models\ClubMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClubMember>
 */
class ClubMemberFactory extends Factory
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
            'club_role_id' => null,
            'full_name' => fake()->name(),
            'age' => fake()->numberBetween(16, 60),
            'sex' => fake()->randomElement(Sex::cases()),
            'occupation' => fake()->jobTitle(),
            'address' => fake()->address(),
            'phone' => fake()->numerify('09#########'),
            'notes' => null,
            'status' => ClubMemberStatus::Pending,
            'reviewed_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ClubMemberStatus::Pending,
            'reviewed_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ClubMemberStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ClubMemberStatus::Declined,
            'reviewed_at' => now(),
        ]);
    }
}
