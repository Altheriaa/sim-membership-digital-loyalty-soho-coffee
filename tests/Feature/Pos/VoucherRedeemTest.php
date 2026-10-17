<?php

use App\Models\LoyaltySetting;
use App\Models\Member;
use App\Models\MemberVoucher;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;

function signInAsCashier(): User
{
    return test()->actingAs(User::factory()->cashier()->create());
}

beforeEach(function () {
    LoyaltySetting::create([
        'spend_per_point' => 10000,
        'point_value' => 1000,
        'tax_percent' => '11',
    ]);
});

function openVoucherFor(Member $member, Voucher $voucher): MemberVoucher
{
    return MemberVoucher::factory()->create([
        'member_id' => $member->id,
        'voucher_id' => $voucher->id,
        'points_spent' => $voucher->points_cost,
        'expires_at' => now()->addDays(30)->endOfDay(),
    ]);
}

test('kasir finds a member voucher by its claim code for the selected member', function () {
    signInAsCashier();
    $member = Member::factory()->create();
    $member->setPin('123456');
    $member->refresh();
    $voucher = Voucher::factory()->freeItem(MenuItem::factory()->create(['price' => 20000]))->create(['points_cost' => 20]);
    $claimed = openVoucherFor($member, $voucher);

    $response = $this->get(route('pos.vouchers.lookup', [
        'code' => $claimed->code,
        'member_id' => $member->id,
    ]));

    $response->assertOk()
        ->assertJsonPath('voucher.id', $claimed->id)
        ->assertJsonPath('voucher.code', $claimed->code)
        ->assertJsonPath('voucher.name', $voucher->name)
        ->assertJsonPath('voucher.amount', 20000)
        ->assertJsonPath('voucher.points_spent', 20)
        ->assertJsonPath('voucher.owner_name', $member->name);
});

test('kasir cannot pull a voucher that belongs to another member', function () {
    signInAsCashier();
    $member = Member::factory()->create();
    $member->setPin('123456');
    $other = Member::factory()->create();
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);
    $claimed = openVoucherFor($other, $voucher);

    $this->get(route('pos.vouchers.lookup', [
        'code' => $claimed->code,
        'member_id' => $member->id,
    ]))->assertInvalid(['code' => 'Kode voucer tidak ditemukan untuk member ini. Periksa kode di halaman Kupon Saya aplikasi member.']);
});

test('kasir gets a clear message when the voucher is used twice, expired or not formatted', function () {
    signInAsCashier();
    $member = Member::factory()->create();
    $member->setPin('123456');
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);

    $used = MemberVoucher::factory()->used()->create([
        'member_id' => $member->id,
        'voucher_id' => $voucher->id,
    ]);

    $this->get(route('pos.vouchers.lookup', [
        'code' => $used->code,
        'member_id' => $member->id,
    ]))->assertInvalid(['code' => 'Voucer sudah pernah digunakan pada transaksi lain.']);

    $expired = MemberVoucher::factory()->create([
        'member_id' => $member->id,
        'voucher_id' => $voucher->id,
        'expires_at' => now()->subDay(),
    ]);

    $this->get(route('pos.vouchers.lookup', [
        'code' => $expired->code,
        'member_id' => $member->id,
    ]))->assertInvalid(['code' => 'Voucer sudah kedaluwarsa sejak '.$expired->expires_at->translatedFormat('d M Y, H:i').' WIB.']);

    $this->get(route('pos.vouchers.lookup', [
        'code' => 'SALAH-KODE',
        'member_id' => $member->id,
    ]))->assertInvalid(['code']);
});

test('kasir is told when a member has no security pin yet', function () {
    signInAsCashier();
    $member = Member::factory()->create(['security_pin' => null]);
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);
    $claimed = openVoucherFor($member, $voucher);

    $this->get(route('pos.vouchers.lookup', [
        'code' => $claimed->code,
        'member_id' => $member->id,
    ]))->assertInvalid(['pin']);
});

test('paying with a member voucher discounts the order and consumes the voucher', function () {
    $cashier = signInAsCashier();
    $member = Member::factory()->create(['points_balance' => 500]);
    $member->setPin('123456');
    $member->refresh();
    $item = MenuItem::factory()->create(['price' => 20000]);
    $voucher = Voucher::factory()->freeItem($item)->create(['points_cost' => 20]);
    $claimed = openVoucherFor($member, $voucher);

    $response = $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 2]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '123456',
        'payment_method' => 'cash',
        'cash_received' => 100000,
    ]);

    $response->assertCreated();

    $order = Order::query()->sole();

    expect($order->subtotal)->toBe(40000)
        ->and($order->tax_amount)->toBe(4400)
        ->and($order->discount_amount)->toBe(20000)
        ->and($order->total)->toBe(24400)
        ->and($order->cash_received)->toBe(100000)
        ->and($order->change_due)->toBe(75600)
        ->and($order->voucher_id)->toBe($claimed->id)
        ->and($order->points_earned)->toBe(4)
        ->and($order->member_points_after)->toBe(504);

    $claimed->refresh();

    expect($claimed->status)->toBe('used')
        ->and($claimed->order_id)->toBe($order->id)
        ->and($claimed->used_at)->not->toBeNull();

    $member->refresh();

    expect($member->points_balance)->toBe(504);
});

test('a discount amount voucher takes its nominal value off the total', function () {
    signInAsCashier();
    $member = Member::factory()->create(['points_balance' => 500]);
    $member->setPin('123456');
    $member->refresh();
    $item = MenuItem::factory()->create(['price' => 20000]);
    $voucher = Voucher::factory()->create(['discount_amount' => 15000, 'points_cost' => 12]);
    $claimed = openVoucherFor($member, $voucher);

    $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 2]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '123456',
        'payment_method' => 'cash',
        'cash_received' => 50000,
    ])->assertCreated();

    $order = Order::query()->sole();

    expect($order->discount_amount)->toBe(15000)
        ->and($order->total)->toBe(29400);
});

test('a voucher never discounts more than the subtotal', function () {
    signInAsCashier();
    $member = Member::factory()->create(['points_balance' => 500]);
    $member->setPin('123456');
    $member->refresh();
    $item = MenuItem::factory()->create(['price' => 20000]);
    $voucher = Voucher::factory()->create(['discount_amount' => 60000]);
    $claimed = openVoucherFor($member, $voucher);

    $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 2]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '123456',
        'payment_method' => 'cash',
        'cash_received' => 20000,
    ])->assertCreated();

    $order = Order::query()->sole();

    expect($order->discount_amount)->toBe(40000)
        ->and($order->total)->toBe(4400);
});

test('a wrong member pin blocks the sale and the voucher stays claimable again', function () {
    signInAsCashier();
    $member = Member::factory()->create(['points_balance' => 500]);
    $member->setPin('123456');
    $item = MenuItem::factory()->create(['price' => 20000]);
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);
    $claimed = openVoucherFor($member, $voucher);

    $this->from(route('pos.index'))->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '654321',
        'payment_method' => 'cash',
        'cash_received' => 100000,
    ])->assertInvalid(['voucher_pin' => 'PIN Keamanan member salah.']);

    expect($claimed->refresh()->status)->toBe('active')
        ->and(Order::query()->count())->toBe(0)
        ->and($member->refresh()->points_balance)->toBe(500);
});

test('a voucher of another member cannot be paid with', function () {
    signInAsCashier();
    $member = Member::factory()->create(['points_balance' => 500]);
    $member->setPin('123456');
    $other = Member::factory()->create();
    $other->setPin('123456');
    $item = MenuItem::factory()->create(['price' => 20000]);
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);
    $claimed = openVoucherFor($other, $voucher);

    $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '123456',
        'payment_method' => 'cash',
        'cash_received' => 100000,
    ])->assertInvalid(['voucher_id']);

    expect($claimed->refresh()->status)->toBe('active');
});

test('the same voucher cannot be paid with twice', function () {
    signInAsCashier();
    $member = Member::factory()->create(['points_balance' => 500]);
    $member->setPin('123456');
    $item = MenuItem::factory()->create(['price' => 20000]);
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);
    $claimed = openVoucherFor($member, $voucher);

    $body = fn () => $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '123456',
        'payment_method' => 'cash',
        'cash_received' => 100000,
    ]);

    $body()->assertCreated();
    $body()->assertInvalid(['voucher_id' => 'Voucer sudah pernah digunakan pada transaksi lain.']);

    expect(Order::query()->count())->toBe(1);
});

test('paying with a voucher without selecting a member is rejected', function () {
    signInAsCashier();
    $item = MenuItem::factory()->create(['price' => 20000]);
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);

    $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => null,
        'voucher_id' => MemberVoucher::factory()->create(['voucher_id' => $voucher->id])->id,
        'voucher_pin' => '123456',
        'payment_method' => 'cash',
        'cash_received' => 100000,
    ])->assertInvalid(['voucher_id']);
});

test('paying with a voucher without the member pin is rejected', function () {
    signInAsCashier();
    $member = Member::factory()->create();
    $member->setPin('123456');
    $item = MenuItem::factory()->create(['price' => 20000]);
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);
    $claimed = openVoucherFor($member, $voucher);

    $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => null,
        'payment_method' => 'cash',
        'cash_received' => 100000,
    ])->assertInvalid(['voucher_pin']);
});

test('the kasir receipt shows the voucher it redeemed', function () {
    signInAsCashier();
    $member = Member::factory()->create();
    $member->setPin('123456');
    $item = MenuItem::factory()->create(['price' => 20000]);
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);
    $claimed = openVoucherFor($member, $voucher);

    $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 2]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '123456',
        'payment_method' => 'cash',
        'cash_received' => 60000,
    ])->assertCreated();

    $order = Order::query()->sole();

    $this->get(route('pos.orders.show', $order))->assertOk()
        ->assertSeeText('Potongan voucer')
        ->assertSeeText($claimed->code);
});

test('regular orders without a voucher keep their old totals', function () {
    signInAsCashier();
    $member = Member::factory()->create(['points_balance' => 500]);
    $item = MenuItem::factory()->create(['price' => 20000]);

    $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 2]],
        'member_id' => $member->id,
        'payment_method' => 'cash',
        'cash_received' => 50000,
    ])->assertCreated();

    $order = Order::query()->sole();

    expect($order->discount_amount)->toBe(0)
        ->and($order->voucher_id)->toBeNull()
        ->and($order->total)->toBe(44400);
});
