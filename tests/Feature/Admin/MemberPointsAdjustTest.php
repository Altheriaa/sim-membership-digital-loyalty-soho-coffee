<?php

use App\Models\Member;
use App\Models\PointTransaction;

beforeEach(function () {
    $this->member = Member::factory()->create(['points_balance' => 100]);
});

test('admin adds points to a member and the balance is recorded', function () {
    signInAsAdmin();

    $this->post(route('admin.members.adjust-points', $this->member), [
        'points' => '30',
        'description' => 'Hadiah ulang tahun',
    ])->assertRedirect(route('admin.members.show', $this->member))
        ->assertSessionHas('status', 'Poin member berhasil disesuaikan.');

    expect($this->member->refresh()->points_balance)->toBe(130)
        ->and(PointTransaction::query()->sole()->balance_after)->toBe(130);
});

test('admin cannot cut more points than the member owns', function () {
    signInAsAdmin();

    $this->from(route('admin.members.show', $this->member))->post(route('admin.members.adjust-points', $this->member), [
        'points' => '-200',
        'description' => 'Koreksi',
    ])->assertSessionHasErrors(['points' => 'Pengurangan poin melebihi saldo member saat ini (100 poin).']);

    expect($this->member->refresh()->points_balance)->toBe(100)
        ->and(PointTransaction::query()->count())->toBe(0);
});

test('admin cannot enter nonsensical point amounts', function () {
    signInAsAdmin();

    $this->post(route('admin.members.adjust-points', $this->member), ['points' => '0', 'description' => 'Koreksi'])
        ->assertSessionHasErrors(['points']);
    $this->post(route('admin.members.adjust-points', $this->member), ['points' => '1000000000', 'description' => 'Koreksi'])
        ->assertSessionHasErrors(['points']);
    $this->post(route('admin.members.adjust-points', $this->member), ['points' => '-1000000000', 'description' => 'Koreksi'])
        ->assertSessionHasErrors(['points']);

    expect($this->member->refresh()->points_balance)->toBe(100);
});
