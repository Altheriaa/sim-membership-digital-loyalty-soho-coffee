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
}
