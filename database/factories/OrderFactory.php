<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * A non-cash sale of Rp 25.000 with no tax and no member.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cashier_id' => User::factory()->cashier(),
            'member_id' => null,
            'subtotal' => 25000,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'total' => 25000,
            'payment_method' => PaymentMethod::NonCash,
            'cash_received' => null,
            'change_due' => null,
            'points_earned' => 0,
            'member_points_after' => null,
        ];
    }
}
