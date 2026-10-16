<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Voucher;
use App\VoucherType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * A discount voucher by default.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'type' => VoucherType::DiscountAmount,
            'menu_item_id' => null,
            'discount_amount' => fake()->numberBetween(5, 20) * 1000,
            'points_cost' => fake()->numberBetween(5, 30),
            'quota' => null,
            'valid_days' => 30,
            'available_from' => null,
            'available_until' => null,
            'is_active' => true,
        ];
    }

    /**
     * A voucher that gives one menu item for free.
     */
    public function freeItem(?MenuItem $item = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VoucherType::FreeItem,
            'menu_item_id' => $item?->id ?? MenuItem::factory(),
            'discount_amount' => null,
        ]);
    }

    /**
     * Indicate that the voucher is switched off.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
