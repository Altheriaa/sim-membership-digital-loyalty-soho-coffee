import { test } from 'node:test';
import assert from 'node:assert/strict';
import {
    cashSuggestions,
    formatRupiah,
    isValidPhone,
    normalizePhone,
    pointsFor,
    subtotalOf,
    taxFor,
} from '../../resources/js/pos-math.js';

test('normalizePhone brings every common way of typing a number to 62...', () => {
    assert.equal(normalizePhone('0812-3456-7890'), '6281234567890');
    assert.equal(normalizePhone('+62 812 3456 7890'), '6281234567890');
    assert.equal(normalizePhone('812 3456 7890'), '6281234567890');
    assert.equal(normalizePhone('6281234567890'), '6281234567890');
    assert.equal(normalizePhone(''), '');
    assert.equal(normalizePhone(null), '');
});

test('isValidPhone accepts Indonesian mobile numbers only', () => {
    assert.equal(isValidPhone('6281234567890'), true);
    assert.equal(isValidPhone('628123456'), false);
    assert.equal(isValidPhone('6221234567'), false);
    assert.equal(isValidPhone('62812345678901234'), false);
});

test('taxFor rounds half up to the nearest rupiah, like the server', () => {
    assert.equal(taxFor(50000, 0), 0);
    assert.equal(taxFor(50000, 1100), 5500);
    assert.equal(taxFor(18500, 1000), 1850);
    assert.equal(taxFor(5, 1000), 1);
    assert.equal(taxFor(4, 1000), 0);
});

test('pointsFor counts whole spend steps, then applies the tier bonus and rounds down', () => {
    assert.equal(pointsFor(95000, 10000), 9);
    assert.equal(pointsFor(95000, 10000, 125), 11);
    assert.equal(pointsFor(9999, 10000, 200), 0);
    assert.equal(pointsFor(100000, 10000, 150), 15);
});

test('subtotalOf multiplies price by quantity per line', () => {
    assert.equal(subtotalOf([]), 0);
    assert.equal(subtotalOf([{ price: 18000, quantity: 2 }, { price: 25000, quantity: 1 }]), 61000);
});

test('formatRupiah groups thousands with dots', () => {
    assert.equal(formatRupiah(0), 'Rp 0');
    assert.equal(formatRupiah(1250000), 'Rp 1.250.000');
    assert.equal(formatRupiah(-5000), '-Rp 5.000');
});

test('cashSuggestions offers the exact total and the next round notes without duplicates', () => {
    assert.deepEqual(cashSuggestions(0), []);
    assert.deepEqual(cashSuggestions(43000), [43000, 50000, 100000]);
    assert.deepEqual(cashSuggestions(50000), [50000, 100000]);
    assert.deepEqual(cashSuggestions(61500), [61500, 70000, 100000]);
});
