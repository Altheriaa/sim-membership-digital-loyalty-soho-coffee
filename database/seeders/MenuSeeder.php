<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Voucher;
use App\VoucherType;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Placeholder menu and vouchers for development. The cafe replaces them with its real offer.
     */
    public function run(): void
    {
        $menu = [
            'Kopi' => [['Espresso', 18000], ['Americano', 20000], ['Kopi Susu', 25000]],
            'Non-Kopi' => [['Teh Lemon', 18000], ['Cokelat', 24000]],
            'Makanan' => [['Roti Bakar', 22000]],
        ];

        $order = 0;

        foreach ($menu as $categoryName => $items) {
            $category = MenuCategory::query()->updateOrCreate(['name' => $categoryName], ['sort_order' => $order++]);

            foreach ($items as [$name, $price]) {
                MenuItem::query()->updateOrCreate(
                    ['menu_category_id' => $category->id, 'name' => $name],
                    ['price' => $price, 'is_available' => true],
                );
            }
        }

        $freeCoffee = MenuItem::query()->where('name', 'Kopi Susu')->firstOrFail();

        Voucher::query()->updateOrCreate(
            ['name' => 'Gratis 1 Kopi Susu'],
            [
                'type' => VoucherType::FreeItem,
                'menu_item_id' => $freeCoffee->id,
                'discount_amount' => null,
                'points_cost' => 20,
                'valid_days' => 30,
                'is_active' => true,
            ],
        );

        Voucher::query()->updateOrCreate(
            ['name' => 'Diskon Rp 10.000'],
            [
                'type' => VoucherType::DiscountAmount,
                'menu_item_id' => null,
                'discount_amount' => 10000,
                'points_cost' => 12,
                'valid_days' => 14,
                'is_active' => true,
            ],
        );
    }
}
