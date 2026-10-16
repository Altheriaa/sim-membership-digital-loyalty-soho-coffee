<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Tier;
use App\Models\User;
use App\Models\Voucher;

test('dashboard shows how many menu items can be sold right now', function () {
    signInAsAdmin();
    $category = MenuCategory::factory()->create();
    MenuItem::factory()->count(3)->create(['menu_category_id' => $category->id]);
    MenuItem::factory()->soldOut()->create(['menu_category_id' => $category->id]);

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertViewHas('availableItems', 3)
        ->assertViewHas('totalItems', 4)
        ->assertSeeText('Menu yang bisa dijual sekarang')
        ->assertSeeText('3 tersedia dari 4 menu, di 1 kategori');
});

test('dashboard summarises tiers, staff and the point rules', function () {
    signInAsAdmin();
    Tier::factory()->base()->create();
    Tier::factory()->create();
    User::factory()->cashier()->inactive()->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSeeText('2 tingkat keanggotaan')
        ->assertSeeText('1 akun aktif')
        ->assertSeeText('Rp 10.000 = 1 poin, 1 poin = Rp 1.000, pajak 0%');
});

test('dashboard counts only vouchers that can be claimed today', function () {
    travelTo('2026-10-15');
    signInAsAdmin();
    Voucher::factory()->create();
    Voucher::factory()->inactive()->create();
    Voucher::factory()->create(['available_from' => '2026-11-01']);
    Voucher::factory()->create(['available_until' => '2026-10-01']);

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertViewHas('openVouchers', 1)
        ->assertSeeText('1 voucer bisa diklaim hari ini');
});

test('dashboard points the admin to the first step when there is no menu yet', function () {
    signInAsAdmin();

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSeeText('Belum ada menu yang tersedia.')
        ->assertSeeText('Tambah menu pertama')
        ->assertSee(route('admin.menu-items.create'), false);
});

test('dashboard says plainly that sales analytics are not built yet and shows no sample numbers', function () {
    signInAsAdmin();

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSeeText('Analitik penjualan belum tersedia')
        ->assertSeeText('Dashboard ini tidak menampilkan angka contoh.');
});

test('owners see the same summary without management buttons', function () {
    $this->actingAs(User::factory()->owner()->create());
    MenuItem::factory()->create();

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSeeText('1 tersedia dari 1 menu')
        ->assertDontSeeText('Kelola menu')
        ->assertDontSeeText('Kelola staf');
});

test('admins see a management button for every master data row', function () {
    signInAsAdmin();

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSeeText('Kelola menu')
        ->assertSeeText('Kelola voucer')
        ->assertSeeText('Kelola tier')
        ->assertSeeText('Ubah aturan')
        ->assertSeeText('Kelola staf');
});

test('the sidebar shows the signed-in name and role, and only admins get management links', function () {
    signInAsAdmin();

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Admin Utama')
        ->assertSeeText('Aturan poin')
        ->assertSeeText('Keluar');

    auth()->logout();
    $this->actingAs(User::factory()->owner()->create(['name' => 'Pak Owner']));

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('Pak Owner')
        ->assertDontSeeText('Aturan poin');
});
