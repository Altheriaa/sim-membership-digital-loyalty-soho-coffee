<?php

namespace Database\Factories;

use App\Models\Tier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tier>
 */
class TierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'min_spend' => fake()->unique()->numberBetween(1, 500) * 100000,
            'multiplier' => 1,
        ];
    }

    /**
     * Indicate that this is the base tier every member starts in.
     */
    public function base(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_spend' => 0,
        ]);
    }
}
