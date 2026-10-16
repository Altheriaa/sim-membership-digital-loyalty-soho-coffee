<?php

use App\Models\MenuItem;
use App\Models\User;

dataset('owner forbidden routes', [
    'menu' => 'admin.menu-items.index',
    'categories' => 'admin.categories.index',
    'tiers' => 'admin.tiers.index',
    'vouchers' => 'admin.vouchers.index',
    'staff' => 'admin.staff.index',
    'loyalty rules' => 'admin.loyalty.edit',
    'pos' => 'pos.index',
]);

dataset('cashier forbidden routes', [
    'dashboard' => 'admin.dashboard',
    'menu' => 'admin.menu-items.index',
    'categories' => 'admin.categories.index',
    'tiers' => 'admin.tiers.index',
    'vouchers' => 'admin.vouchers.index',
    'staff' => 'admin.staff.index',
    'loyalty rules' => 'admin.loyalty.edit',
]);

dataset('protected routes', [
    'dashboard' => 'admin.dashboard',
    'pos' => 'pos.index',
    'menu' => 'admin.menu-items.index',
    'categories' => 'admin.categories.index',
    'tiers' => 'admin.tiers.index',
    'vouchers' => 'admin.vouchers.index',
    'staff' => 'admin.staff.index',
    'loyalty rules' => 'admin.loyalty.edit',
]);

test('guests are redirected to the login page from every protected screen', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with('protected routes');

test('admins can open every back office screen and the register', function (string $route) {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route($route))->assertOk();
})->with('protected routes');

test('owners get 403 on every management screen and on the register', function (string $route) {
    $this->actingAs(User::factory()->owner()->create());

    $this->get(route($route))->assertForbidden();
})->with('owner forbidden routes');

test('owners can open the dashboard', function () {
    $this->actingAs(User::factory()->owner()->create());

    $this->get(route('admin.dashboard'))->assertOk();
});

test('cashiers can open the register', function () {
    $this->actingAs(User::factory()->cashier()->create());

    $this->get(route('pos.index'))->assertOk();
});

test('cashiers get 403 on the dashboard and on every management screen', function (string $route) {
    $this->actingAs(User::factory()->cashier()->create());

    $this->get(route($route))->assertForbidden();
})->with('cashier forbidden routes');

test('a cashier cannot create a menu item through the write endpoint', function () {
    $this->actingAs(User::factory()->cashier()->create());

    $this->post(route('admin.menu-items.store'), ['name' => 'Kopi Gelap', 'price' => 20000])->assertForbidden();

    expect(MenuItem::query()->count())->toBe(0);
});
