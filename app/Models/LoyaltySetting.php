<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['spend_per_point', 'point_value', 'tax_percent'])]
class LoyaltySetting extends Model
{
    public const DEFAULTS = [
        'spend_per_point' => 10000,
        'point_value' => 1000,
        'tax_percent' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'spend_per_point' => 'integer',
            'point_value' => 'integer',
            'tax_percent' => 'decimal:2',
        ];
    }

    /**
     * The single settings row, created with the defaults on first use.
     */
    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create(self::DEFAULTS);
    }

    /**
     * The tax rate in hundredths of a percent, so 11.5% becomes 1150 and the math stays in whole numbers.
     */
    public function taxBasisPoints(): int
    {
        return (int) round((float) $this->tax_percent * 100);
    }

    /**
     * Tax on a subtotal, rounded half up to the nearest rupiah.
     */
    public function taxFor(int $subtotal): int
    {
        return intdiv($subtotal * $this->taxBasisPoints() + 5000, 10000);
    }

    /**
     * Points a purchase earns: whole spend steps, multiplied by the tier bonus, rounded down.
     *
     * @param  int  $multiplierHundredths  The tier multiplier times 100, so 1.25 is passed as 125.
     */
    public function pointsFor(int $spend, int $multiplierHundredths = 100): int
    {
        return intdiv(intdiv($spend, $this->spend_per_point) * $multiplierHundredths, 100);
    }
}
