<?php

use App\Models\User;
use App\Role;

test('hasRole matches any of the given roles', function () {
    $admin = User::factory()->admin()->make();

    expect($admin->hasRole(Role::Admin))->toBeTrue()
        ->and($admin->hasRole(Role::Owner, Role::Admin))->toBeTrue()
        ->and($admin->hasRole(Role::Owner, Role::Cashier))->toBeFalse();
});

test('countByRole counts accounts per role value', function () {
    User::factory()->owner()->create();
    User::factory()->cashier()->count(2)->create();

    $counts = User::countByRole();
    ksort($counts);

    expect($counts)->toBe(['cashier' => 2, 'owner' => 1]);
});

test('new accounts default to the cashier role and start active', function () {
    $user = User::factory()->create()->refresh();

    expect($user->role)->toBe(Role::Cashier)
        ->and($user->is_active)->toBeTrue();
});
