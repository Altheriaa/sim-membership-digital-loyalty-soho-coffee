<?php

use App\Models\Member;

beforeEach(function () {
    $this->member = Member::factory()->create(['phone' => '6281234567890']);
    $this->member->setPin('123456');
    $this->member->refresh();

    $this->actingAs($this->member, 'customer')->post(route('customer.pin.reset.request'));

    $this->resetCode = session('otp_simulated')['code'];
});

test('mistyped reset otp attempts are limited to five tries', function () {
    for ($attempt = 1; $attempt < 5; $attempt++) {
        $this->post(route('customer.pin.reset.confirm'), ['otp' => '0000', 'pin' => '111111', 'pin_confirmation' => '111111'])
            ->assertSessionHasErrors(['otp']);
    }

    $this->post(route('customer.pin.reset.confirm'), ['otp' => '0000', 'pin' => '111111', 'pin_confirmation' => '111111'])
        ->assertRedirect(route('customer.pin.show'))
        ->assertSessionHasErrors(['error' => 'Terlalu banyak percobaan salah. Silakan ajukan reset PIN kembali.']);

    expect($this->member->refresh()->verifyPin('123456'))->toBeTrue()
        ->and(session('otp_simulated'))->toBeNull()
        ->and(session('reset_pin_otp'))->toBeNull();
});

test('a correct reset otp updates the pin and clears the simulation', function () {
    $this->post(route('customer.pin.reset.confirm'), ['otp' => $this->resetCode, 'pin' => '999999', 'pin_confirmation' => '999999'])
        ->assertRedirect(route('customer.pin.show'))
        ->assertSessionHas('status', 'PIN Keamanan berhasil direset dan diperbarui.');

    expect($this->member->refresh()->verifyPin('999999'))->toBeTrue()
        ->and(session('otp_simulated'))->toBeNull();
});
