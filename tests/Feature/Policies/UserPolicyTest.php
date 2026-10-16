<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

test('admins may list and create staff accounts', function () {
    $admin = User::factory()->admin()->create();

    expect(Gate::forUser($admin)->allows('viewAny', User::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', User::class))->toBeTrue();
});

test('owners and cashiers may not list or create staff accounts', function (string $role) {
    $user = User::factory()->create(['role' => $role]);

    expect(Gate::forUser($user)->allows('viewAny', User::class))->toBeFalse()
        ->and(Gate::forUser($user)->allows('create', User::class))->toBeFalse();
})->with(['owner', 'cashier']);

test('admins may update cashiers and other admins', function (string $targetRole) {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create(['role' => $targetRole]);

    expect(Gate::forUser($admin)->allows('update', $target))->toBeTrue();
})->with(['cashier', 'admin']);

test('admins may not update an owner account', function () {
    $admin = User::factory()->admin()->create();
    $owner = User::factory()->owner()->create();

    expect(Gate::forUser($admin)->allows('update', $owner))->toBeFalse();
});

test('owners and cashiers may not update any account', function (string $role) {
    $user = User::factory()->create(['role' => $role]);
    $target = User::factory()->cashier()->create();

    expect(Gate::forUser($user)->allows('update', $target))->toBeFalse();
})->with(['owner', 'cashier']);
