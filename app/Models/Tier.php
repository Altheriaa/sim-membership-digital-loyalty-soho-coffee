<?php

namespace App\Models;

use Database\Factories\TierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
     * The point multiplier times 100, so 1.25 becomes 125 and point math stays in whole numbers.
     */
    public function multiplierHundredths(): int
    {
        return (int) round((float) $this->multiplier * 100);
    }

    /**
     * The multiplier as a person reads it in Indonesian: 1.25 becomes "1,25", 1.00 becomes "1".
     */
    public function multiplierLabel(): string
    {
        return str_replace('.', ',', rtrim(rtrim((string) $this->multiplier, '0'), '.'));
    }

    /**
     * The highest tier whose threshold the lifetime spend reaches.
     */
    public static function forSpend(?int $lifetimeSpend = 0): ?self
    {
        $lifetimeSpend = (int) $lifetimeSpend;

        return static::query()
            ->where('min_spend', '<=', $lifetimeSpend)
            ->orderByDesc('min_spend')
            ->first();
    }

    /**
     * The tier for a lifetime spend using tiers loaded in one query, avoiding a
     * fresh database trip per member row.
     *
     * @param  Collection<int, self>  $tiers
     */
    public static function forSpendFrom(Collection $tiers, ?int $lifetimeSpend = 0): ?self
    {
        $lifetimeSpend = (int) $lifetimeSpend;

        return $tiers
            ->filter(fn (self $tier): bool => $tier->min_spend <= $lifetimeSpend)
            ->sortByDesc('min_spend')
            ->first();
    }
}
