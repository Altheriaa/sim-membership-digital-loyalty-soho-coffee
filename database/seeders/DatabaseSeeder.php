<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed development data. Production starts empty so the cafe enters its own menu and rules.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        $this->call([
            StaffSeeder::class,
            LoyaltySeeder::class,
            MenuSeeder::class,
        ]);
    }
}
