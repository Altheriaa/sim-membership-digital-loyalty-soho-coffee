<?php

use App\Models\LoyaltySetting;

test('the loyalty rules page starts from the defaults and creates a single settings row', function () {
    signInAsAdmin();

    $response = $this->get(route('admin.loyalty.edit'));

    $response->assertOk()
        ->assertSee('name="spend_per_point"', false)
        ->assertSee('value="10000"', false)
        ->assertSee('value="1000"', false);
    expect(LoyaltySetting::query()->count())->toBe(1);

    $this->get(route('admin.loyalty.edit'))->assertOk();

    expect(LoyaltySetting::query()->count())->toBe(1);
});

test('admin saves the point and tax rules', function () {
    signInAsAdmin();

    $response = $this->put(route('admin.loyalty.update'), [
        'spend_per_point' => 5000,
        'point_value' => 500,
        'tax_percent' => '11.5',
    ]);

    $response->assertRedirect(route('admin.loyalty.edit'))
        ->assertSessionHas('status', 'Aturan poin dan pajak disimpan.');

    $settings = LoyaltySetting::current();

    expect($settings->spend_per_point)->toBe(5000)
        ->and($settings->point_value)->toBe(500)
        ->and($settings->tax_percent)->toBe('11.50')
        ->and(LoyaltySetting::query()->count())->toBe(1);
});

test('the page shows what the saved rules mean for a hundred thousand rupiah', function () {
    signInAsAdmin();
    LoyaltySetting::current()->update(['spend_per_point' => 5000, 'point_value' => 500]);

    $response = $this->get(route('admin.loyalty.edit'));

    $response->assertOk()
        ->assertSeeText('Belanja Rp 100.000 menghasilkan 20 poin')
        ->assertSeeText('10 poin bernilai Rp 5.000');
});

test('the rules reject values that would break point maths', function (string $field, mixed $value, string $message) {
    signInAsAdmin();

    $payload = ['spend_per_point' => 10000, 'point_value' => 1000, 'tax_percent' => 10, $field => $value];

    $response = $this->put(route('admin.loyalty.update'), $payload);

    $response->assertSessionHasErrors([$field => $message]);
    expect(LoyaltySetting::current()->spend_per_point)->toBe(10000);
})->with([
    'zero spend per point' => ['spend_per_point', 0, 'Belanja per poin minimal 1.'],
    'zero point value' => ['point_value', 0, 'Nilai satu poin minimal 1.'],
    'tax above one hundred' => ['tax_percent', 101, 'Pajak harus di antara 0 dan 100.'],
    'negative tax' => ['tax_percent', -1, 'Pajak harus di antara 0 dan 100.'],
    'three decimals of tax' => ['tax_percent', '10.123', 'Pajak harus memiliki 0-2 angka desimal.'],
    'text instead of a number' => ['spend_per_point', 'banyak', 'Belanja per poin harus berupa bilangan bulat.'],
]);

test('every rule is required', function () {
    signInAsAdmin();

    $response = $this->from(route('admin.loyalty.edit'))->put(route('admin.loyalty.update'), []);

    $response->assertSessionHasErrors([
        'spend_per_point' => 'Belanja per poin wajib diisi.',
        'point_value' => 'Nilai satu poin wajib diisi.',
        'tax_percent' => 'Pajak wajib diisi.',
    ]);
});
