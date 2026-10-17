<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\MemberVoucher;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MemberVoucher>
 */
class MemberVoucherFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'voucher_id' => Voucher::factory(),
            'code' => 'KOP-'.strtoupper(Str::random(6)),
            'status' => 'active',
            'points_spent' => 10,
            'expires_at' => Carbon::now()->addDays(30)->endOfDay(),
        ];
    }

    public function used(): static
    {
        return $this->state(fn (): array => [
            'status' => 'used',
            'used_at' => Carbon::now(),
        ]);
    }
}
