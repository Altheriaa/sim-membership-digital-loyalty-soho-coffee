<?php

use App\Models\Tier;

test('forSpend returns the highest tier whose threshold the spend reaches', function (int $spend, string $expected) {
    Tier::factory()->base()->create(['name' => 'Silver']);
    Tier::factory()->create(['name' => 'Gold', 'min_spend' => 1000000]);
    Tier::factory()->create(['name' => 'Platinum', 'min_spend' => 5000000]);

    expect(Tier::forSpend($spend)->name)->toBe($expected);
})->with([
    'no spend' => [0, 'Silver'],
    'just below gold' => [999999, 'Silver'],
    'exactly gold' => [1000000, 'Gold'],
    'between gold and platinum' => [4999999, 'Gold'],
    'exactly platinum' => [5000000, 'Platinum'],
    'far above platinum' => [90000000, 'Platinum'],
]);

test('forSpend returns null when no tier has been defined', function () {
    expect(Tier::forSpend(100000))->toBeNull();
});

test('only the zero-threshold tier counts as the base tier', function () {
    expect(Tier::factory()->base()->make()->isBase())->toBeTrue()
        ->and(Tier::factory()->make(['min_spend' => 1000])->isBase())->toBeFalse();
});
