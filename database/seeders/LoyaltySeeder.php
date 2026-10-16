<?php

namespace Database\Seeders;

use App\Models\LoyaltySetting;
use App\Models\Tier;
use Illuminate\Database\Seeder;

class LoyaltySeeder extends Seeder
{
    /**
     * Placeholder rules for development. The cafe sets its real thresholds in the admin panel.
     */
    public function run(): void
    {
        LoyaltySetting::current();

        $tiers = [
            ['name' => 'Silver', 'min_spend' => 0, 'multiplier' => 1.00],
            ['name' => 'Gold', 'min_spend' => 1_000_000, 'multiplier' => 1.25],
            ['name' => 'Platinum', 'min_spend' => 5_000_000, 'multiplier' => 1.50],
        ];

        foreach ($tiers as $tier) {
            Tier::query()->updateOrCreate(['name' => $tier['name']], $tier);
        }
    }
}
