<?php

use App\Models\MenuItem;
use App\Models\Voucher;
use App\VoucherType;

test('voucher index shows what each voucher gives, its points, quota and status', function () {
    signInAsAdmin();
    $item = MenuItem::factory()->create(['name' => 'Kopi Susu']);
    Voucher::factory()->freeItem($item)->create(['name' => 'Gratis Kopi', 'points_cost' => 20, 'quota' => 50, 'valid_days' => 30]);
    Voucher::factory()->inactive()->create(['name' => 'Potongan', 'discount_amount' => 10000, 'points_cost' => 12, 'quota' => null, 'valid_days' => 14]);

    $response = $this->get(route('admin.vouchers.index'));

    $response->assertOk()
        ->assertSeeTextInOrder(['Potongan', 'Diskon Rp 10.000', 'berlaku 14 hari setelah diklaim', '12', 'Tanpa batas', 'Nonaktif'])
        ->assertSeeTextInOrder(['Gratis Kopi', 'Gratis: Kopi Susu', 'berlaku 30 hari setelah diklaim', '20', '50', 'Aktif']);
});

test('voucher index explains what a voucher is when none exist', function () {
    signInAsAdmin();

    $this->get(route('admin.vouchers.index'))
        ->assertOk()
        ->assertSeeText('Belum ada voucer')
        ->assertSeeText('Tambah voucer pertama');
});

test('voucher index does not list deleted vouchers', function () {
    signInAsAdmin();
    Voucher::factory()->create(['name' => 'Sudah Dihapus'])->delete();
    Voucher::factory()->create(['name' => 'Masih Ada']);

    $this->get(route('admin.vouchers.index'))
        ->assertOk()
        ->assertSeeText('Masih Ada')
        ->assertDontSeeText('Sudah Dihapus');
});

test('admin creates a free item voucher', function () {
    signInAsAdmin();
    $item = MenuItem::factory()->create();

    $response = $this->post(route('admin.vouchers.store'), [
        'name' => 'Gratis 1 Kopi',
        'type' => 'free_item',
        'menu_item_id' => $item->id,
        'discount_amount' => '',
        'points_cost' => 20,
        'quota' => 100,
        'valid_days' => 30,
        'available_from' => '2026-11-01',
        'available_until' => '2026-11-30',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.vouchers.index'))
        ->assertSessionHas('status', 'Voucer Gratis 1 Kopi dibuat.');

    $voucher = Voucher::query()->where('name', 'Gratis 1 Kopi')->firstOrFail();

    expect($voucher->type)->toBe(VoucherType::FreeItem)
        ->and($voucher->menu_item_id)->toBe($item->id)
        ->and($voucher->discount_amount)->toBeNull()
        ->and($voucher->points_cost)->toBe(20)
        ->and($voucher->quota)->toBe(100)
        ->and($voucher->available_from->toDateString())->toBe('2026-11-01')
        ->and($voucher->available_until->toDateString())->toBe('2026-11-30')
        ->and($voucher->is_active)->toBeTrue();
});

test('admin creates a discount voucher with unlimited quota', function () {
    signInAsAdmin();

    $this->post(route('admin.vouchers.store'), [
        'name' => 'Diskon 10 Ribu',
        'type' => 'discount_amount',
        'discount_amount' => 10000,
        'points_cost' => 12,
        'quota' => '',
        'valid_days' => 14,
        'is_active' => '0',
    ])->assertRedirect(route('admin.vouchers.index'));

    $voucher = Voucher::query()->where('name', 'Diskon 10 Ribu')->firstOrFail();

    expect($voucher->type)->toBe(VoucherType::DiscountAmount)
        ->and($voucher->discount_amount)->toBe(10000)
        ->and($voucher->menu_item_id)->toBeNull()
        ->and($voucher->quota)->toBeNull()
        ->and($voucher->is_active)->toBeFalse();
});

test('a free item voucher drops a discount amount that was left in the form', function () {
    signInAsAdmin();
    $item = MenuItem::factory()->create();

    $this->post(route('admin.vouchers.store'), [
        'name' => 'Gratis',
        'type' => 'free_item',
        'menu_item_id' => $item->id,
        'discount_amount' => 5000,
        'points_cost' => 10,
        'valid_days' => 7,
    ])->assertRedirect(route('admin.vouchers.index'));

    expect(Voucher::query()->where('name', 'Gratis')->firstOrFail()->discount_amount)->toBeNull();
});

test('a discount voucher drops a menu item that was left in the form', function () {
    signInAsAdmin();
    $item = MenuItem::factory()->create();

    $this->post(route('admin.vouchers.store'), [
        'name' => 'Potongan',
        'type' => 'discount_amount',
        'menu_item_id' => $item->id,
        'discount_amount' => 5000,
        'points_cost' => 10,
        'valid_days' => 7,
    ])->assertRedirect(route('admin.vouchers.index'));

    expect(Voucher::query()->where('name', 'Potongan')->firstOrFail()->menu_item_id)->toBeNull();
});

test('creating a voucher requires the basics', function () {
    signInAsAdmin();

    $response = $this->from(route('admin.vouchers.create'))->post(route('admin.vouchers.store'), []);

    $response->assertSessionHasErrors([
        'name' => 'Nama wajib diisi.',
        'type' => 'Jenis voucer wajib diisi.',
        'points_cost' => 'Harga poin wajib diisi.',
        'valid_days' => 'Masa berlaku wajib diisi.',
    ]);
    expect(Voucher::query()->count())->toBe(0);
});

test('a free item voucher must name the menu item', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.vouchers.store'), [
        'name' => 'Gratis',
        'type' => 'free_item',
        'points_cost' => 10,
        'valid_days' => 7,
    ]);

    $response->assertSessionHasErrors(['menu_item_id' => 'Pilih menu yang diberikan gratis.']);
});

test('a discount voucher must state the discount amount', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.vouchers.store'), [
        'name' => 'Potongan',
        'type' => 'discount_amount',
        'points_cost' => 10,
        'valid_days' => 7,
    ]);

    $response->assertSessionHasErrors(['discount_amount' => 'Isi nominal diskon.']);
});

test('a voucher cannot end before it starts', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.vouchers.store'), [
        'name' => 'Terbalik',
        'type' => 'discount_amount',
        'discount_amount' => 5000,
        'points_cost' => 10,
        'valid_days' => 7,
        'available_from' => '2026-12-10',
        'available_until' => '2026-12-01',
    ]);

    $response->assertSessionHasErrors(['available_until' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.']);
});

test('voucher numbers must stay in range', function (string $field, mixed $value, string $message) {
    signInAsAdmin();

    $response = $this->post(route('admin.vouchers.store'), [
        'name' => 'Batas',
        'type' => 'discount_amount',
        'discount_amount' => 5000,
        'points_cost' => 10,
        'valid_days' => 7,
        $field => $value,
    ]);

    $response->assertSessionHasErrors([$field => $message]);
    expect(Voucher::query()->count())->toBe(0);
})->with([
    'zero points' => ['points_cost', 0, 'Harga poin minimal 1.'],
    'zero quota' => ['quota', 0, 'Kuota minimal 1.'],
    'zero validity' => ['valid_days', 0, 'Masa berlaku minimal 1.'],
    'validity above a year' => ['valid_days', 366, 'Masa berlaku tidak boleh lebih dari 365.'],
]);

test('admin switches a voucher from free item to discount', function () {
    signInAsAdmin();
    $voucher = Voucher::factory()->freeItem()->create(['name' => 'Gratis']);

    $this->put(route('admin.vouchers.update', $voucher), [
        'name' => 'Potongan Baru',
        'type' => 'discount_amount',
        'discount_amount' => 7500,
        'points_cost' => 9,
        'valid_days' => 21,
        'is_active' => '1',
    ])->assertRedirect(route('admin.vouchers.index'));

    $voucher->refresh();

    expect($voucher->name)->toBe('Potongan Baru')
        ->and($voucher->type)->toBe(VoucherType::DiscountAmount)
        ->and($voucher->discount_amount)->toBe(7500)
        ->and($voucher->menu_item_id)->toBeNull();
});

test('admin deletes a voucher by soft delete so it can still be traced', function () {
    signInAsAdmin();
    $voucher = Voucher::factory()->create(['name' => 'Akan Dihapus']);

    $this->delete(route('admin.vouchers.destroy', $voucher))
        ->assertRedirect(route('admin.vouchers.index'))
        ->assertSessionHas('status', 'Voucer Akan Dihapus dihapus.');

    expect(Voucher::query()->whereKey($voucher->id)->exists())->toBeFalse()
        ->and(Voucher::withTrashed()->whereKey($voucher->id)->exists())->toBeTrue();
});

test('the voucher form shows only the field of the saved type', function () {
    signInAsAdmin();
    $voucher = Voucher::factory()->freeItem()->create();

    $response = $this->get(route('admin.vouchers.edit', $voucher));

    $response->assertOk()
        ->assertSee('name="menu_item_id"', false)
        ->assertSee('name="discount_amount"', false);
    expect($response->getContent())
        ->toMatch('/x-show="type === \'discount_amount\'"[^>]*style="display: none"/');
});
