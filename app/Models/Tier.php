<?php

namespace App\Models;

use Database\Factories\TierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'min_spend', 'multiplier'])]
class Tier extends Model
{
    /** @use HasFactory<TierFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_spend' => 'integer',
            'multiplier' => 'decimal:2',
        ];
    }

    /**
     * @param  Builder<Tier>  $query
     */
    #[Scope]
    protected function ascending(Builder $query): void
    {
        $query->orderBy('min_spend');
    }

    /**
     * The base tier is the one every member starts in, so it must keep a zero threshold.
     */
    public function isBase(): bool
    {
        return $this->min_spend === 0;
    }

    /**
     * The highest tier whose threshold the lifetime spend reaches.
     */
    public static function forSpend(int $lifetimeSpend): ?self
    {
        return static::query()
            ->where('min_spend', '<=', $lifetimeSpend)
            ->orderByDesc('min_spend')
            ->first();
    }
}
