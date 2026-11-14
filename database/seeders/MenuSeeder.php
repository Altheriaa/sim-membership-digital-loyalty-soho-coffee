<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Voucher;
use App\VoucherType;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            'Kopi' => [['Espresso', 18000], ['Americano', 20000], ['Kopi Susu', 25000]],
            'Non-Kopi' => [['Teh Lemon', 18000], ['Cokelat', 24000]],
            'Makanan' => [
                ['Ayam Geprek', 20000],
                ['Ayam Penyet', 20000],
                ['Chicken Cordon Blue', 65000],
                ['Chicken Crispy', 40000],
                ['Chicken Steak', 40000],
                ['Nasi Ayam Rica-Rica', 30000],
                ['Nasi Ayam Sambal Matah', 30000],
                ['Nasi Dori Rica-Rica', 30000],
                ['Nasi Dori Sambal Matah', 30000],
                ['Nasi Goreng Ayam', 30000],
                ['Nasi Goreng Belacan', 30000],
                ['Nasi Goreng Daun Jeruk', 28000],
                ['Nasi Goreng Daun Jeruk Ayam Sambal Kerok', 30000],
                ['Nasi Goreng Kampoeng Ayam', 30000],
                ['Nasi Goreng Kampoeng Telur', 20000],
                ['Nasi Goreng Seafood', 30000],
                ['Nasi Goreng Telur', 20000],
                ['Nasi Goreng Tomyam', 30000],
                ['Nasi Goreng Xo', 30000],
                ['Nasi Kecombrang Ayam Cabe Hijau', 30000],
                ['Nasi Liwet', 30000],
                ['Pasta Agilo Olio', 30000],
                ['Pasta Bolognese', 30000],
                ['Pasta Carbonara', 30000],
                ['Roti Bakar', 22000],
                ['Spaghetti Brulee', 30000],
                ['Thai Basil Chicken Rice', 30000],
            ],
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
