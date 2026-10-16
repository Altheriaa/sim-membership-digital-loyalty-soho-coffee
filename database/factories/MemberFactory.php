<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'phone' => '628'.fake()->unique()->numerify('##########'),
            'points_balance' => 0,
            'lifetime_spend' => 0,
        ];
    }
}
