<?php

use App\Models\Member;

test('the otp code stays in session after a mistyped attempt without leaking to response', function () {
    Member::factory()->create(['phone' => '6281234567890']);

    $this->post(route('customer.send-otp'), ['phone' => '0812 3456 7890']);

    $code = session('customer_otp_code');
    expect($code)->toBeString()->toHaveLength(4)
        ->and(session('otp_simulated'))->toBeNull();

    $this->from(route('customer.otp'))
        ->post(route('customer.verify-otp'), ['otp' => '0000'])
        ->assertRedirect(route('customer.otp'))
        ->assertSessionHasErrors(['otp' => 'Kode OTP yang Anda masukkan salah. Sisa percobaan: 4 kali.']);

    expect(session('customer_otp_code'))->toBe($code)
        ->and(session('customer_otp_attempts'))->toBe(1)
        ->and(session('otp_simulated'))->toBeNull();
});

test('five mistyped otp attempts lock the flow and force a new code', function () {
    Member::factory()->create(['phone' => '6281234567890']);

    $this->post(route('customer.send-otp'), ['phone' => '0812 3456 7890']);
    $code = session('customer_otp_code');

    for ($attempt = 1; $attempt < 5; $attempt++) {
        $this->post(route('customer.verify-otp'), ['otp' => '9999'])
            ->assertSessionHasErrors(['otp']);
    }

    $this->post(route('customer.verify-otp'), ['otp' => '9999'])
        ->assertRedirect(route('customer.login'))
        ->assertSessionHasErrors(['phone' => 'Terlalu banyak percobaan salah. Silakan minta kode OTP baru.']);

    expect(session('customer_otp_code'))->toBeNull()
        ->and(session('otp_simulated'))->toBeNull()
        ->and(session('customer_otp_attempts'))->toBeNull()
        ->and(Member::query()->count())->toBe(1);

    $this->post(route('customer.verify-otp'), ['otp' => $code])
        ->assertRedirect(route('customer.login'))
        ->assertSessionHasErrors();

    $this->assertGuest('customer');
});

test('a correct otp signs the member in, keeps them on a fresh session and clears otp state', function () {
    $member = Member::factory()->create(['phone' => '6281234567890']);

    $this->post(route('customer.send-otp'), ['phone' => '0812 3456 7890']);
    $code = session('customer_otp_code');
    $previousSessionId = oldSessionId();

    $this->post(route('customer.verify-otp'), ['otp' => $code])
        ->assertRedirect(route('customer.dashboard'))
        ->assertSessionHas('status');

    expect(session('otp_simulated'))->toBeNull()
        ->and(session('customer_otp_code'))->toBeNull()
        ->and(session()->getId())->not->toBe($previousSessionId);

    $this->assertAuthenticatedAs($member, 'customer');
});

function oldSessionId(): string
{
    return session()->getId();
}
