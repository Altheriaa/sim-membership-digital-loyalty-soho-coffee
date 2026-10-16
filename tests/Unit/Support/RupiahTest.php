<?php

use App\Support\Rupiah;

test('format writes whole rupiah with dot separators', function (int|float|string $amount, string $expected) {
    expect(Rupiah::format($amount))->toBe($expected);
})->with([
    'zero' => [0, 'Rp 0'],
    'hundreds' => [500, 'Rp 500'],
    'thousands' => [25000, 'Rp 25.000'],
    'millions' => [1500000, 'Rp 1.500.000'],
    'numeric string' => ['12500', 'Rp 12.500'],
    'decimal string from the database' => ['10000.00', 'Rp 10.000'],
    'rounds half up' => [999.6, 'Rp 1.000'],
    'negative' => [-5000, '-Rp 5.000'],
]);
