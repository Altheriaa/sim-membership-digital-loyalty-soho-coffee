<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Kolom datetime/timestamp yang dibuat saat aplikasi masih utc.
     *
     * @var array<string, array<int, string>>
     */
    private array $shiftedColumns = [
        'users' => ['created_at', 'updated_at', 'email_verified_at'],
        'members' => ['created_at', 'updated_at'],
        'tiers' => ['created_at', 'updated_at'],
        'vouchers' => ['created_at', 'updated_at', 'deleted_at'],
        'menu_categories' => ['created_at', 'updated_at'],
        'menu_items' => ['created_at', 'updated_at'],
        'orders' => ['created_at', 'updated_at'],
        'order_items' => ['created_at', 'updated_at'],
        'member_vouchers' => ['created_at', 'updated_at'],
        'point_transactions' => ['created_at', 'updated_at'],
        'loyalty_settings' => ['created_at', 'updated_at'],
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->shiftedColumns as $table => $columns) {
            $setClause = collect($columns)
                ->map(fn (string $column): string => sprintf('%s = DATE_ADD(%s, INTERVAL 7 HOUR)', $column, $column))
                ->implode(', ');

            DB::statement(sprintf('UPDATE %s SET %s', $table, $setClause));
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->shiftedColumns as $table => $columns) {
            $setClause = collect($columns)
                ->map(fn (string $column): string => sprintf('%s = DATE_SUB(%s, INTERVAL 7 HOUR)', $column, $column))
                ->implode(', ');

            DB::statement(sprintf('UPDATE %s SET %s', $table, $setClause));
        }
    }
};
