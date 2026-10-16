<?php

namespace App\Models;

use App\Support\PhoneNumber;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

#[Fillable(['name', 'phone', 'points_balance', 'lifetime_spend', 'security_pin'])]
class Member extends Authenticatable
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points_balance' => 'integer',
            'lifetime_spend' => 'integer',
        ];
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<PointTransaction, $this>
     */
    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    /**
     * @return HasMany<MemberVoucher, $this>
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(MemberVoucher::class);
    }

    public function hasPin(): bool
    {
        return ! empty($this->security_pin);
    }

    public function verifyPin(string $pin): bool
    {
        return ! empty($this->security_pin) && Hash::check($pin, $this->security_pin);
    }

    public function setPin(string $pin): void
    {
        $this->security_pin = Hash::make($pin);
        $this->save();
    }

    /**
     * The tier the lifetime spend has reached, or null while the cafe has not set up any tier.
     */
    public function tier(): ?Tier
    {
        return Tier::forSpend($this->lifetime_spend);
    }

    public function nextTier(): ?Tier
    {
        return Tier::query()
            ->where('min_spend', '>', $this->lifetime_spend)
            ->orderBy('min_spend')
            ->first();
    }

    public function spendToNextTier(): int
    {
        $next = $this->nextTier();

        return $next ? max(0, $next->min_spend - $this->lifetime_spend) : 0;
    }

    public function tierProgressPercentage(): int
    {
        $current = $this->tier();
        $next = $this->nextTier();

        if (! $next) {
            return 100;
        }

        $base = $current ? $current->min_spend : 0;
        $range = max(1, $next->min_spend - $base);
        $progress = max(0, $this->lifetime_spend - $base);

        return min(100, (int) round(($progress / $range) * 100));
    }

    public function displayPhone(): string
    {
        return PhoneNumber::display($this->phone);
    }

    public function maskedPhone(): string
    {
        return PhoneNumber::masked($this->phone);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeMatching(Builder $query, ?string $search): void
    {
        $search = trim((string) $search);

        if ($search === '') {
            return;
        }

        $normalized = PhoneNumber::normalize($search);

        $query->where(function (Builder $builder) use ($search, $normalized): void {
            $builder->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");

            if ($normalized !== '') {
                $builder->orWhere('phone', 'like', "%{$normalized}%");
            }
        });
    }
}
