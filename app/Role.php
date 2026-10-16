<?php

namespace App;

enum Role: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Cashier = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Cashier => 'Kasir',
        };
    }

    /**
     * The named route a signed-in user of this role lands on.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Owner, self::Admin => 'admin.dashboard',
            self::Cashier => 'pos.index',
        };
    }

    /**
     * The roles an admin is allowed to hand out. Owner accounts are created outside the panel.
     *
     * @return array<int, Role>
     */
    public static function assignableByAdmin(): array
    {
        return [self::Admin, self::Cashier];
    }
}
