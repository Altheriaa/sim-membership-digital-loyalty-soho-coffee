<?php

namespace Database\Seeders;

use App\Models\User;
use App\Role;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    /**
     * Development accounts, one per role. All share the password "password".
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Owner Dev', 'email' => 'owner@example.com', 'role' => Role::Owner],
            ['name' => 'Admin Dev', 'email' => 'admin@example.com', 'role' => Role::Admin],
            ['name' => 'Kasir Dev', 'email' => 'kasir@example.com', 'role' => Role::Cashier],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'is_active' => true,
                    'password' => 'password',
                ],
            );
        }
    }
}
