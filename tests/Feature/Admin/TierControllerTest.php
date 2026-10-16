<?php

use App\Models\Tier;

test('tier index lists tiers from the lowest threshold and marks the base tier', function () {
    signInAsAdmin();
    Tier::factory()->create(['name' => 'Gold', 'min_spend' => 1000000, 'multiplier' => 1.25]);
    Tier::factory()->base()->create(['name' => 'Silver', 'multiplier' => 1]);

    $response = $this->get(route('admin.tiers.index'));

    $response->assertOk()
        ->assertSeeTextInOrder(['Silver', 'Tier dasar', 'Semua member', '1×', 'Gold', 'Mulai Rp 1.000.000', '1,25×']);
});

test('tier index asks for a base tier when none exist', function () {
    signInAsAdmin();

    $this->get(route('admin.tiers.index'))
        ->assertOk()
        ->assertSeeText('Belum ada tier')
        ->assertSeeText('Tambah tier dasar');
});

test('admin creates a tier', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.tiers.store'), ['name' => 'Gold', 'min_spend' => 1000000, 'multiplier' => '1.25']);

    $response->assertRedirect(route('admin.tiers.index'))
        ->assertSessionHas('status', 'Tier Gold ditambahkan.');

    $tier = Tier::query()->where('name', 'Gold')->firstOrFail();

    expect($tier->min_spend)->toBe(1000000)
        ->and($tier->multiplier)->toBe('1.25');
});

test('creating a tier requires a name, a threshold and a multiplier', function () {
    signInAsAdmin();

    $response = $this->from(route('admin.tiers.create'))->post(route('admin.tiers.store'), []);

    $response->assertSessionHasErrors([
        'name' => 'Nama wajib diisi.',
        'min_spend' => 'Syarat belanja wajib diisi.',
        'multiplier' => 'Pengali poin wajib diisi.',
    ]);
});

test('creating a tier rejects a duplicate name and a duplicate threshold', function () {
    signInAsAdmin();
    Tier::factory()->create(['name' => 'Gold', 'min_spend' => 1000000]);

    $response = $this->post(route('admin.tiers.store'), ['name' => 'Gold', 'min_spend' => 1000000, 'multiplier' => 1.5]);

    $response->assertSessionHasErrors([
        'name' => 'Nama sudah dipakai.',
        'min_spend' => 'Sudah ada tier dengan syarat belanja yang sama.',
    ]);
});

test('the multiplier must stay between 1 and 10 with at most two decimals', function (string $multiplier, string $message) {
    signInAsAdmin();

    $response = $this->post(route('admin.tiers.store'), ['name' => 'Aneh', 'min_spend' => 500000, 'multiplier' => $multiplier]);

    $response->assertSessionHasErrors(['multiplier' => $message]);
    expect(Tier::query()->count())->toBe(0);
})->with([
    'below one' => ['0.5', 'Pengali poin harus di antara 1 dan 10.'],
    'above ten' => ['11', 'Pengali poin harus di antara 1 dan 10.'],
    'three decimals' => ['1.255', 'Pengali poin harus memiliki 0-2 angka desimal.'],
]);

test('admin updates a tier and may keep its own threshold', function () {
    signInAsAdmin();
    $tier = Tier::factory()->create(['name' => 'Gold', 'min_spend' => 1000000, 'multiplier' => 1.25]);

    $this->put(route('admin.tiers.update', $tier), ['name' => 'Gold Plus', 'min_spend' => 1000000, 'multiplier' => '1.30'])
        ->assertRedirect(route('admin.tiers.index'));

    $tier->refresh();

    expect($tier->name)->toBe('Gold Plus')
        ->and($tier->multiplier)->toBe('1.30');
});

test('the base tier must keep a zero threshold', function () {
    signInAsAdmin();
    $base = Tier::factory()->base()->create(['name' => 'Silver']);

    $response = $this->put(route('admin.tiers.update', $base), ['name' => 'Silver', 'min_spend' => 50000, 'multiplier' => 1]);

    $response->assertSessionHasErrors(['min_spend' => 'Tier dasar harus tetap mulai dari Rp 0.']);
    expect($base->refresh()->min_spend)->toBe(0);
});

test('the base tier cannot be deleted', function () {
    signInAsAdmin();
    $base = Tier::factory()->base()->create();

    $response = $this->from(route('admin.tiers.edit', $base))->delete(route('admin.tiers.destroy', $base));

    $response->assertSessionHasErrors(['tier' => 'Tier dasar tidak bisa dihapus karena setiap member harus punya tier.']);
    expect(Tier::query()->whereKey($base->id)->exists())->toBeTrue();
});

test('admin deletes a higher tier', function () {
    signInAsAdmin();
    $gold = Tier::factory()->create(['name' => 'Gold', 'min_spend' => 1000000]);

    $this->delete(route('admin.tiers.destroy', $gold))
        ->assertRedirect(route('admin.tiers.index'))
        ->assertSessionHas('status', 'Tier Gold dihapus.');

    expect(Tier::query()->whereKey($gold->id)->exists())->toBeFalse();
});

test('the base tier form fixes the threshold instead of offering it', function () {
    signInAsAdmin();
    $base = Tier::factory()->base()->create();

    $this->get(route('admin.tiers.edit', $base))
        ->assertOk()
        ->assertSeeText('Ini tier dasar')
        ->assertDontSeeText('Hapus tier');
});
