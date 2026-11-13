<?php

use App\Models\LoyaltySetting;
use App\Models\Member;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Tier;
use App\Models\Voucher;

test('the promotional home page shows available menu items, active voucher promos, and links to the guide page', function () {
    LoyaltySetting::create([
        'spend_per_point' => 15000,
        'point_value' => 1000,
        'tax_percent' => 10,
    ]);
    $category = MenuCategory::factory()->create(['name' => 'Signature Espresso']);
    MenuItem::factory()->for($category, 'category')->create(['name' => 'Kopi Susu Gula Aren', 'price' => 25000]);
    MenuItem::factory()->for($category, 'category')->soldOut()->create(['name' => 'Cold Brew Habis', 'price' => 28000]);
    Tier::factory()->base()->create(['name' => 'Bronze', 'multiplier' => 1.00]);
    Tier::factory()->create(['name' => 'Gold', 'min_spend' => 1000000, 'multiplier' => 1.50]);
    Voucher::factory()->create(['name' => 'Potongan Sepuluh Ribu', 'points_cost' => 12, 'discount_amount' => 10000]);
    Voucher::factory()->inactive()->create(['name' => 'Voucher Nonaktif', 'points_cost' => 8]);

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSeeText('Seduhan kopi segar dan ruang santai untuk hari Anda.')
        ->assertSeeText('Espresso, Americano, & Kopi Susu Racikan Harian')
        ->assertSeeText('Rp 15.000')
        ->assertSeeText('Signature Espresso')
        ->assertSeeText('Kopi Susu Gula Aren')
        ->assertDontSeeText('Cold Brew Habis')
        ->assertSeeText('Potongan Sepuluh Ribu')
        ->assertDontSeeText('Voucher Nonaktif')
        ->assertSeeText('Gabung Member Gratis')
        ->assertSee(route('guide'), false);
});

test('the guide landing page renders the interactive points receipt, steps, tiers, and link back to home', function () {
    LoyaltySetting::create([
        'spend_per_point' => 15000,
        'point_value' => 1000,
        'tax_percent' => 10,
    ]);
    $category = MenuCategory::factory()->create(['name' => 'Signature Espresso']);
    MenuItem::factory()->for($category, 'category')->create(['name' => 'Kopi Susu Gula Aren', 'price' => 25000]);
    Tier::factory()->base()->create(['name' => 'Bronze', 'multiplier' => 1.00]);
    Tier::factory()->create(['name' => 'Gold', 'min_spend' => 1000000, 'multiplier' => 1.50]);
    Voucher::factory()->create(['name' => 'Potongan Sepuluh Ribu', 'points_cost' => 12, 'discount_amount' => 10000]);

    $response = $this->get(route('guide'));

    $response->assertOk()
        ->assertSeeText('Hitung poin pesanan Anda')
        ->assertSeeText('Rp 15.000')
        ->assertSeeText('Kopi Susu Gula Aren')
        ->assertSeeText('Bronze')
        ->assertSeeText('Gold')
        ->assertSeeText('1,5x')
        ->assertSeeText('Potongan Sepuluh Ribu')
        ->assertSee(route('home'), false);
});

test('both landing pages point signed-in members directly to their digital member card', function () {
    $member = Member::factory()->create();

    $this->actingAs($member, 'customer')
        ->get(route('home'))
        ->assertOk()
        ->assertSeeText('Buka Kartu Member')
        ->assertDontSeeText('Gabung Member Gratis');

    $this->actingAs($member, 'customer')
        ->get(route('guide'))
        ->assertOk()
        ->assertSeeText('Buka kartu member saya')
        ->assertDontSeeText('Daftar atau masuk dengan WhatsApp');
});

test('both landing pages show explicit empty states when menu items, tiers, and rewards are not configured yet', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeText('Daftar menu belum diisi di sistem kasir.')
        ->assertSeeText('Belum ada promo voucher yang dibuka hari ini.');

    $this->get(route('guide'))
        ->assertOk()
        ->assertSeeText('Daftar menu belum diisi di sistem kasir.')
        ->assertSeeText('Tingkat member belum diatur di panel admin.')
        ->assertSeeText('Belum ada reward yang dibuka saat ini.');
});
