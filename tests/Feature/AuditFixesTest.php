<?php

use App\Models\LoyaltySetting;
use App\Models\Member;
use App\Models\MemberVoucher;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\PointTransaction;
use App\Models\Tier;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Carbon;

test('customer otp login never exposes the otp code in responses or simulation session and stays valid for five minutes', function () {
    Member::factory()->create(['phone' => '6281234567890']);

    $this->post(route('customer.send-otp'), ['phone' => '081234567890'])
        ->assertRedirect(route('customer.otp'));

    $code = session('customer_otp_code');

    expect($code)->toBeString()->toHaveLength(4)
        ->and(session('otp_simulated'))->toBeNull();

    $this->get(route('customer.otp'))
        ->assertOk()
        ->assertDontSee($code)
        ->assertSeeText('Masa aktif 5 menit');

    $this->travel(3)->minutes();

    $this->post(route('customer.verify-otp'), ['otp' => $code])
        ->assertRedirect(route('customer.dashboard'));

    $this->assertAuthenticated('customer');
});

test('re-submitting customer login with the same phone during cooldown does not regenerate an active otp', function () {
    Member::factory()->create(['phone' => '6281234567890']);

    $this->post(route('customer.send-otp'), ['phone' => '081234567890']);
    $firstCode = session('customer_otp_code');

    $this->travel(20)->seconds();

    $this->post(route('customer.send-otp'), ['phone' => '081234567890'])
        ->assertRedirect(route('customer.otp'));

    expect(session('customer_otp_code'))->toBe($firstCode);
});

test('claimed member voucher remains readable and redeemable at pos after master voucher is soft deleted', function () {
    $member = Member::factory()->create(['points_balance' => 100]);
    $member->setPin('123456');

    $item = MenuItem::factory()->create(['price' => 25000]);
    $voucher = Voucher::factory()->freeItem($item)->create(['points_cost' => 10]);

    $claimed = MemberVoucher::query()->create([
        'member_id' => $member->id,
        'voucher_id' => $voucher->id,
        'code' => 'KOP-SOFT01',
        'points_spent' => 10,
        'status' => 'active',
        'expires_at' => now()->addDays(10),
    ]);

    $voucher->delete();

    $this->actingAs($member, 'customer')
        ->get(route('customer.vouchers.index'))
        ->assertOk()
        ->assertSeeText($voucher->name);

    $this->actingAs($member, 'customer')
        ->get(route('customer.vouchers.show', $claimed))
        ->assertOk()
        ->assertSeeText('KOP-SOFT01');

    $cashier = User::factory()->cashier()->create();
    $this->actingAs($cashier);

    $this->getJson(route('pos.vouchers.lookup', [
        'code' => 'KOP-SOFT01',
        'member_id' => $member->id,
    ]))->assertOk()
        ->assertJsonPath('voucher.menu_item_id', $item->id);

    $this->post(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '123456',
        'payment_method' => 'cash',
        'cash_received' => 0,
    ])->assertCreated();

    expect($claimed->fresh()->status)->toBe('used');
});

test('pos member pin and customer current pin update are rate limited after five wrong attempts', function () {
    $cashier = User::factory()->cashier()->create();
    $member = Member::factory()->create(['points_balance' => 100]);
    $member->setPin('123456');
    $item = MenuItem::factory()->create(['price' => 25000]);

    $this->actingAs($cashier);

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->postJson(route('pos.orders.store'), [
            'items' => [['id' => $item->id, 'quantity' => 1]],
            'member_id' => $member->id,
            'points_to_redeem' => 10,
            'voucher_pin' => '000000',
            'payment_method' => 'non_cash',
        ])->assertStatus(422)->assertJsonValidationErrors(['voucher_pin']);
    }

    $blocked = $this->postJson(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => $member->id,
        'points_to_redeem' => 10,
        'voucher_pin' => '123456',
        'payment_method' => 'non_cash',
    ])->assertStatus(422);

    expect($blocked->json('errors.voucher_pin.0'))->toContain('Terlalu banyak percobaan PIN');

    $this->actingAs($member, 'customer');

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->from(route('customer.pin.show'))
            ->post(route('customer.pin.update'), [
                'current_pin' => '000000',
                'pin' => '654321',
                'pin_confirmation' => '654321',
            ])->assertSessionHasErrors('current_pin');
    }

    $this->from(route('customer.pin.show'))
        ->post(route('customer.pin.update'), [
            'current_pin' => '123456',
            'pin' => '654321',
            'pin_confirmation' => '654321',
        ])->assertSessionHasErrors('current_pin');

    expect($member->fresh()->verifyPin('123456'))->toBeTrue();
});

test('dashboard dynamically renders custom tiers and avoids march 31 month overflow while preserving full last month data', function () {
    signInAsAdmin();

    Tier::factory()->create(['name' => 'Bronze', 'min_spend' => 0, 'multiplier' => 1.00]);
    Tier::factory()->create(['name' => 'Legend', 'min_spend' => 500000, 'multiplier' => 2.00]);
    Member::factory()->create(['lifetime_spend' => 600000]);

    $this->travelTo(Carbon::parse('2026-03-31 12:00:00'));

    Order::factory()->create([
        'total' => 75000,
        'subtotal' => 75000,
        'created_at' => Carbon::parse('2026-02-15 10:00:00'),
    ]);

    $response = $this->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSeeText('Legend')
        ->assertSeeText('Bronze')
        ->assertSeeText('Rp 0')
        ->assertViewHas('tierSegments', fn (array $segments): bool => count($segments) === 2 && $segments[0]['name'] === 'Legend');

    $marchChart = $response->viewData('chartData');
    expect($marchChart['pathLastMonth'])->not->toBe($marchChart['pathThisMonth']);

    // Now test April viewing March 31 revenue is not truncated at day 30.
    $this->travelTo(Carbon::parse('2026-04-10 12:00:00'));

    Order::factory()->create([
        'total' => 250000,
        'subtotal' => 250000,
        'created_at' => Carbon::parse('2026-03-31 20:00:00'),
    ]);

    $aprilResponse = $this->get(route('admin.dashboard'));
    $chartData = $aprilResponse->viewData('chartData');

    // March 31 total = 250000 renders non-zero last-month curve instead of flat baseline.
    expect($chartData['yAxisLabels'][0]['label'])->toBe('500 rb')
        ->and($chartData['pathLastMonth'])->not->toBe($chartData['pathThisMonth']);
});

test('free item voucher requires its linked menu item in cart and calculates tax and points on net spend', function () {
    LoyaltySetting::current()->update([
        'spend_per_point' => 10000,
        'point_value' => 1000,
        'tax_percent' => 10,
    ]);

    $cashier = User::factory()->cashier()->create();
    $member = Member::factory()->create(['points_balance' => 100, 'lifetime_spend' => 0]);
    $member->setPin('123456');

    $americano = MenuItem::factory()->create(['name' => 'Americano', 'price' => 20000]);
    $matcha = MenuItem::factory()->create(['name' => 'Matcha Latte', 'price' => 30000]);
    $voucher = Voucher::factory()->freeItem($matcha)->create();

    $claimed = MemberVoucher::query()->create([
        'member_id' => $member->id,
        'voucher_id' => $voucher->id,
        'code' => 'KOP-FREE01',
        'points_spent' => 15,
        'status' => 'active',
        'expires_at' => now()->addDays(5),
    ]);

    $this->actingAs($cashier);

    $this->postJson(route('pos.orders.store'), [
        'items' => [['id' => $americano->id, 'quantity' => 2]],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '123456',
        'payment_method' => 'non_cash',
    ])->assertStatus(422)->assertJsonValidationErrors(['voucher_id']);

    $this->postJson(route('pos.orders.store'), [
        'items' => [
            ['id' => $americano->id, 'quantity' => 1],
            ['id' => $matcha->id, 'quantity' => 1],
        ],
        'member_id' => $member->id,
        'voucher_id' => $claimed->id,
        'voucher_pin' => '123456',
        'payment_method' => 'non_cash',
    ])->assertCreated();

    $order = Order::query()->sole();
    // Subtotal 50.000 - Free Matcha 30.000 = 20.000 net; 10% tax = 2.000; total = 22.000; points = 2
    expect($order->subtotal)->toBe(50000)
        ->and($order->discount_amount)->toBe(30000)
        ->and($order->tax_amount)->toBe(2000)
        ->and($order->total)->toBe(22000)
        ->and($order->points_earned)->toBe(2)
        ->and($member->fresh()->lifetime_spend)->toBe(20000);
});

test('cashier can redeem member points directly at pos using point value and net math', function () {
    LoyaltySetting::current()->update([
        'spend_per_point' => 10000,
        'point_value' => 1000,
        'tax_percent' => 10,
    ]);

    $cashier = User::factory()->cashier()->create();
    $member = Member::factory()->create(['points_balance' => 30, 'lifetime_spend' => 0]);
    $member->setPin('123456');

    $item = MenuItem::factory()->create(['price' => 35000]);

    $this->actingAs($cashier);

    // Reject redeeming points beyond net order limit
    $this->postJson(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => $member->id,
        'points_to_redeem' => 40,
        'voucher_pin' => '123456',
        'payment_method' => 'non_cash',
    ])->assertStatus(422)->assertJsonValidationErrors(['points_to_redeem']);

    // Redeem 15 points = Rp 15.000 off Rp 35.000 -> net Rp 20.000, 10% tax = 2.000, total = 22.000, earn 2 points.
    $this->postJson(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => $member->id,
        'points_to_redeem' => 15,
        'voucher_pin' => '123456',
        'payment_method' => 'non_cash',
    ])->assertCreated();

    $order = Order::query()->sole();
    $member->refresh();

    expect($order->points_redeemed)->toBe(15)
        ->and($order->point_discount_amount)->toBe(15000)
        ->and($order->discount_amount)->toBe(15000)
        ->and($order->tax_amount)->toBe(2000)
        ->and($order->total)->toBe(22000)
        ->and($order->points_earned)->toBe(2)
        ->and($member->points_balance)->toBe(17)
        ->and($member->lifetime_spend)->toBe(20000)
        ->and(PointTransaction::query()->where('order_id', $order->id)->count())->toBe(2);
});

test('role navigation links match user permissions across pos sidebar dashboard and receipt', function () {
    $cashier = User::factory()->cashier()->create();
    $owner = User::factory()->owner()->create();
    $order = Order::factory()->create(['cashier_id' => $cashier->id]);

    $this->actingAs($cashier)
        ->get(route('pos.index'))
        ->assertOk()
        ->assertSee('href="'.route('admin.reports.index').'"', false);

    $this->actingAs($cashier)
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertSee('href="'.route('pos.index').'"', false)
        ->assertDontSee('href="'.route('admin.dashboard').'"', false);

    $this->actingAs($owner)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('href="'.route('admin.tiers.index').'"', false);

    $this->actingAs($owner)
        ->get(route('pos.orders.show', $order))
        ->assertOk()
        ->assertDontSee('href="'.route('pos.index').'"', false);
});

test('admin can void an order cleanly reversing member points spend voucher and report metrics', function () {
    LoyaltySetting::current()->update([
        'spend_per_point' => 10000,
        'point_value' => 1000,
        'tax_percent' => 0,
    ]);

    $cashier = User::factory()->cashier()->create();
    $owner = User::factory()->owner()->create();
    $admin = signInAsAdmin();

    $member = Member::factory()->create(['points_balance' => 25, 'lifetime_spend' => 100000]);
    $member->setPin('123456');

    $item = MenuItem::factory()->create(['name' => 'Caramel Latte', 'price' => 30000]);
    $voucher = Voucher::factory()->create(['discount_amount' => 10000]);
    $claimed = MemberVoucher::query()->create([
        'member_id' => $member->id,
        'voucher_id' => $voucher->id,
        'code' => 'KOP-VOID01',
        'points_spent' => 5,
        'status' => 'active',
        'expires_at' => now()->addDays(5),
    ]);

    $this->actingAs($cashier)
        ->postJson(route('pos.orders.store'), [
            'items' => [['id' => $item->id, 'quantity' => 2]],
            'member_id' => $member->id,
            'voucher_id' => $claimed->id,
            'points_to_redeem' => 10,
            'voucher_pin' => '123456',
            'payment_method' => 'non_cash',
        ])->assertCreated();

    $order = Order::query()->sole();
    // Subtotal 60k - 10k voucher - 10k points = 40k net; earn 4 pts -> member balance 25 - 10 + 4 = 19, spend 140k.
    expect($member->fresh()->points_balance)->toBe(19)
        ->and($member->fresh()->lifetime_spend)->toBe(140000)
        ->and($claimed->fresh()->status)->toBe('used');

    // Cashier and Owner cannot void
    $this->actingAs($cashier)
        ->post(route('admin.orders.void', $order), ['void_reason' => 'Coba batal'])
        ->assertForbidden();

    $this->actingAs($owner)
        ->post(route('admin.orders.void', $order), ['void_reason' => 'Coba batal'])
        ->assertForbidden();

    // Admin voids the order
    $this->actingAs($admin)
        ->from(route('admin.reports.index'))
        ->post(route('admin.orders.void', $order), ['void_reason' => 'Salah input menu'])
        ->assertRedirect(route('admin.reports.index'));

    $order->refresh();
    $member->refresh();
    $claimed->refresh();

    expect($order->isVoided())->toBeTrue()
        ->and($order->voided_by)->toBe($admin->id)
        ->and($order->void_reason)->toBe('Salah input menu')
        ->and($member->points_balance)->toBe(25)
        ->and($member->lifetime_spend)->toBe(100000)
        ->and($claimed->status)->toBe('active')
        ->and($claimed->used_at)->toBeNull()
        ->and($claimed->order_id)->toBeNull();

    // Double void is rejected
    $this->actingAs($admin)
        ->post(route('admin.orders.void', $order), ['void_reason' => 'Ulang'])
        ->assertSessionHasErrors('void');

    // Reports and Dashboard exclude voided totals while report table lists receipt status
    $this->actingAs($admin)
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertViewHas('totalOrders', 0)
        ->assertViewHas('grossSales', 0)
        ->assertViewHas('bestSellers', fn ($items): bool => $items->isEmpty())
        ->assertSeeText('Dibatalkan')
        ->assertSeeText('Salah input menu');

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('todayOrders', 0)
        ->assertViewHas('todaySales', 0);
});

test('best seller merges sales history after menu item is renamed and prevents void if earned points were spent', function () {
    LoyaltySetting::current()->update([
        'spend_per_point' => 10000,
        'point_value' => 1000,
        'tax_percent' => 0,
    ]);

    $admin = signInAsAdmin();
    $item = MenuItem::factory()->create(['name' => 'Kopi Susu V1', 'price' => 50000]);

    $this->postJson(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'payment_method' => 'non_cash',
    ])->assertCreated();

    $item->update(['name' => 'Kopi Susu V2']);

    $this->postJson(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 2]],
        'payment_method' => 'non_cash',
    ])->assertCreated();

    $report = $this->get(route('admin.reports.index'));
    $bestSellers = $report->viewData('bestSellers');

    expect($bestSellers)->toHaveCount(1)
        ->and((int) $bestSellers->first()->total_qty)->toBe(3)
        ->and($bestSellers->first()->menuItem->name)->toBe('Kopi Susu V2');

    // Member spends earned points on a voucher -> cannot void order cleanly
    $member = Member::factory()->create(['points_balance' => 0]);
    $this->postJson(route('pos.orders.store'), [
        'items' => [['id' => $item->id, 'quantity' => 1]],
        'member_id' => $member->id,
        'payment_method' => 'non_cash',
    ])->assertCreated();

    $memberOrder = Order::query()->latest('id')->first();
    $member->update(['points_balance' => 1]);

    $this->from(route('admin.reports.index'))
        ->post(route('admin.orders.void', $memberOrder), ['void_reason' => 'Keliru'])
        ->assertSessionHasErrors('void');

    expect($memberOrder->fresh()->isVoided())->toBeFalse();
});

test('all customer portal pages render consistently and pin page surfaces reset error alerts', function () {
    $this->get(route('customer.login'))
        ->assertOk()
        ->assertSeeText('Masuk Portal Member')
        ->assertDontSee('bg-destructive/90');

    $member = Member::factory()->create(['phone' => '6281234567890']);
    $member->setPin('123456');

    $this->post(route('customer.send-otp'), ['phone' => '081234567890']);
    $this->get(route('customer.otp'))
        ->assertOk()
        ->assertSeeText('Verifikasi OTP')
        ->assertSeeText('Masa aktif 5 menit');

    $order = Order::factory()->create(['member_id' => $member->id]);
    $item = MenuItem::factory()->create(['price' => 25000]);
    $voucher = Voucher::factory()->freeItem($item)->create();
    $claimed = MemberVoucher::query()->create([
        'member_id' => $member->id,
        'voucher_id' => $voucher->id,
        'code' => 'KOP-UISYNC',
        'points_spent' => 10,
        'status' => 'active',
        'expires_at' => now()->addDays(5),
    ]);

    $this->actingAs($member, 'customer')
        ->get(route('customer.dashboard'))
        ->assertOk();

    $this->get(route('customer.rewards.index'))->assertOk();
    $this->get(route('customer.vouchers.index'))->assertOk();
    $this->get(route('customer.vouchers.show', $claimed))->assertOk();
    $this->get(route('customer.history.index'))->assertOk();
    $this->get(route('customer.receipt', $order))
        ->assertOk()
        ->assertSeeText('Cetak Struk');

    $this->get(route('customer.pin.show'))
        ->assertOk()
        ->assertSeeText('PIN Keamanan')
        ->assertDontSee('rounded-full bg-secondary');

    session([
        'reset_pin_otp' => '4321',
        'reset_pin_expires' => now()->subMinute()->timestamp,
        'reset_pin_attempts' => 0,
    ]);

    $this->get(route('customer.pin.reset.form'))
        ->assertOk()
        ->assertSeeText('Reset PIN Keamanan');

    $this->followingRedirects()
        ->post(route('customer.pin.reset.confirm'), [
            'otp' => '4321',
            'pin' => '654321',
            'pin_confirmation' => '654321',
        ])
        ->assertOk()
        ->assertSeeText('Sesi OTP reset telah kedaluwarsa. Silakan ajukan reset kembali.');
});
