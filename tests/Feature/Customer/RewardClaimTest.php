<?php

use App\Models\Member;
use App\Models\MemberVoucher;
use App\Models\PointTransaction;
use App\Models\Voucher;

test('member claims an open voucher with points and owns a fresh active voucher', function () {
    $member = Member::factory()->create(['points_balance' => 500]);
    $voucher = Voucher::factory()->freeItem(MenuItem::factory()->create(['price' => 25000]))->create(['points_cost' => 20]);

    $response = $this->actingAs($member, 'customer')->post(route('customer.rewards.claim', $voucher));

    $response->assertRedirect(route('customer.vouchers.show', MemberVoucher::query()->sole()))
        ->assertSessionHas('status', 'Voucer '.$voucher->name.' berhasil diklaim!');

    $claimed = MemberVoucher::query()->sole();

    expect($claimed->code)->toStartWith('KOP-')
        ->and($claimed->status)->toBe('active')
        ->and($claimed->points_spent)->toBe(20)
        ->and($claimed->expires_at->isFuture())->toBeTrue();

    expect($member->refresh()->points_balance)->toBe(480)
        ->and(PointTransaction::query()->sole()->points)->toBe(-20);
});

test('member cannot claim without enough points', function () {
    $member = Member::factory()->create(['points_balance' => 10]);
    $voucher = Voucher::factory()->create(['points_cost' => 20]);

    $this->actingAs($member, 'customer')
        ->from(route('customer.rewards.index'))
        ->post(route('customer.rewards.claim', $voucher))
        ->assertSessionHasErrors(['claim' => 'Poin Anda tidak mencukupi (dibutuhkan 20 poin, saldo Anda 10 poin).']);

    expect($member->refresh()->points_balance)->toBe(10)
        ->and(MemberVoucher::query()->count())->toBe(0);
});

test('member cannot claim when the quota is gone', function () {
    $member = Member::factory()->create(['points_balance' => 100]);
    $voucher = Voucher::factory()->create(['points_cost' => 20, 'quota' => 0]);

    $this->actingAs($member, 'customer')
        ->post(route('customer.rewards.claim', $voucher))
        ->assertSessionHasErrors(['claim' => 'Kuota voucer ini sudah habis.']);

    expect($member->refresh()->points_balance)->toBe(100);
});

test('member cannot claim a voucher that is switched off', function () {
    $member = Member::factory()->create(['points_balance' => 100]);
    $voucher = Voucher::factory()->inactive()->create(['points_cost' => 20]);

    $this->actingAs($member, 'customer')
        ->post(route('customer.rewards.claim', $voucher))
        ->assertSessionHasErrors(['claim' => 'Promosi voucer ini sedang tidak aktif atau belum dibuka.']);

    expect($member->refresh()->points_balance)->toBe(100);
});
