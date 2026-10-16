<?php

use App\Models\Voucher;

test('statusLabel tells inactive, upcoming, expired and active vouchers apart', function (array $attributes, string $expected) {
    travelTo('2026-10-15');

    $voucher = Voucher::factory()->make($attributes);

    expect($voucher->statusLabel())->toBe($expected)
        ->and($voucher->isOpenForClaims())->toBe($expected === 'Aktif');
})->with([
    'switched off' => [['is_active' => false], 'Nonaktif'],
    'switched off beats a valid window' => [['is_active' => false, 'available_from' => '2026-10-01'], 'Nonaktif'],
    'starts tomorrow' => [['available_from' => '2026-10-16'], 'Belum mulai'],
    'ended yesterday' => [['available_until' => '2026-10-14'], 'Berakhir'],
    'starts today' => [['available_from' => '2026-10-15'], 'Aktif'],
    'ends today' => [['available_until' => '2026-10-15'], 'Aktif'],
    'no window at all' => [[], 'Aktif'],
]);

test('the openForClaims scope applies the same rules as statusLabel', function () {
    travelTo('2026-10-15');
    $open = Voucher::factory()->create(['name' => 'Terbuka']);
    Voucher::factory()->inactive()->create();
    Voucher::factory()->create(['available_from' => '2026-10-16']);
    Voucher::factory()->create(['available_until' => '2026-10-14']);
    $endsToday = Voucher::factory()->create(['available_until' => '2026-10-15']);

    $ids = Voucher::query()->openForClaims()->pluck('id')->all();

    expect($ids)->toEqualCanonicalizing([$open->id, $endsToday->id]);
});
