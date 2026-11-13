import { test } from 'node:test';
import assert from 'node:assert/strict';
import pointsEstimator from '../../resources/js/points-estimator.js';

function createEstimator(overrides = {}) {
    return pointsEstimator({
        items: [
            { id: 1, category_id: 10, name: 'Kopi Susu', price: 25000 },
            { id: 2, category_id: 20, name: 'Teh Lemon', price: 18000 },
        ],
        spendPerPoint: 10000,
        tiers: [
            { name: 'Silver', multiplierHundredths: 100 },
            { name: 'Gold', multiplierHundredths: 125 },
            { name: 'Platinum', multiplierHundredths: 150 },
        ],
        checkPhoneUrl: '/app/check-phone',
        ...overrides,
    });
}

test('filters menu items by category and search query and resets the filter', () => {
    const estimator = createEstimator();

    assert.equal(estimator.visibleItemCount, 2);

    estimator.selectedCategory = 10;
    assert.equal(estimator.visibleItemCount, 1);
    assert.equal(estimator.matchesItem(10, 'Kopi Susu'), true);
    assert.equal(estimator.matchesItem(20, 'Teh Lemon'), false);

    estimator.search = 'lemon';
    assert.equal(estimator.visibleItemCount, 0);

    estimator.resetFilter();
    assert.equal(estimator.selectedCategory, 'all');
    assert.equal(estimator.search, '');
    assert.equal(estimator.visibleItemCount, 2);
});

test('starts with zero items, zero subtotal, and idle phone status', () => {
    const estimator = createEstimator();

    assert.equal(estimator.activeSlide, 0);
    assert.equal(estimator.itemCount, 0);
    assert.equal(estimator.subtotal, 0);
    assert.equal(estimator.points, 0);
    assert.equal(estimator.spendToNextPoint, 10000);
    assert.equal(estimator.phoneState, 'idle');
});

test('cycles gallery slides forward, backward, by index, and via swipe gestures', () => {
    const estimator = createEstimator({ slideCount: 4 });

    estimator.nextSlide();
    assert.equal(estimator.activeSlide, 1);

    estimator.prevSlide();
    assert.equal(estimator.activeSlide, 0);

    estimator.prevSlide();
    assert.equal(estimator.activeSlide, 3);

    estimator.goToSlide(2);
    assert.equal(estimator.activeSlide, 2);

    estimator.handleTouchStart({ changedTouches: [{ screenX: 200 }] });
    estimator.handleTouchEnd({ changedTouches: [{ screenX: 120 }] });
    assert.equal(estimator.activeSlide, 3);

    estimator.handleTouchStart({ changedTouches: [{ screenX: 120 }] });
    estimator.handleTouchEnd({ changedTouches: [{ screenX: 220 }] });
    assert.equal(estimator.activeSlide, 2);
});

test('calculates subtotal, floor points, tier bonus, and remaining spend to next point', () => {
    const estimator = createEstimator();

    estimator.add(1);
    estimator.add(2);

    assert.equal(estimator.itemCount, 2);
    assert.equal(estimator.subtotal, 43000);
    assert.equal(estimator.points, 4);
    assert.equal(estimator.spendToNextPoint, 7000);

    estimator.tierIndex = 1;
    assert.equal(estimator.points, 5);

    estimator.tierIndex = 2;
    assert.equal(estimator.points, 6);

    estimator.remove(2);
    assert.equal(estimator.subtotal, 25000);
    assert.equal(estimator.points, 3);

    estimator.clear();
    assert.equal(estimator.itemCount, 0);
    assert.equal(estimator.subtotal, 0);
});

test('validates phone length locally and transitions through loading to registered or error states', async () => {
    const estimator = createEstimator();

    estimator.phone = '0812';
    await estimator.checkPhone();
    assert.equal(estimator.phoneState, 'error');
    assert.match(estimator.phoneError, /10 sampai 15 digit/);

    const originalFetch = globalThis.fetch;

    try {
        globalThis.fetch = async () => ({
            ok: true,
            json: async () => ({ valid: true, registered: true }),
        });

        estimator.phone = '081234567890';
        await estimator.checkPhone();
        assert.equal(estimator.phoneState, 'registered');

        globalThis.fetch = async () => ({
            ok: true,
            json: async () => ({ valid: true, registered: false }),
        });

        await estimator.checkPhone();
        assert.equal(estimator.phoneState, 'unregistered');

        globalThis.fetch = async () => {
            throw new Error('Network down');
        };

        await estimator.checkPhone();
        assert.equal(estimator.phoneState, 'error');
        assert.match(estimator.phoneError, /Gagal terhubung/);
    } finally {
        globalThis.fetch = originalFetch;
    }
});

