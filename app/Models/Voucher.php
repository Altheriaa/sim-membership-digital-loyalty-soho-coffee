<?php

namespace App\Models;

use App\Support\Rupiah;
use App\VoucherType;
use Database\Factories\VoucherFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'type', 'menu_item_id', 'discount_amount', 'points_cost', 'quota', 'valid_days', 'available_from', 'available_until', 'is_active'])]
class Voucher extends Model
{
    /** @use HasFactory<VoucherFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => VoucherType::class,
            'discount_amount' => 'integer',
            'points_cost' => 'integer',
            'quota' => 'integer',
            'valid_days' => 'integer',
            'available_from' => 'date',
            'available_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<MenuItem, $this>
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * The rupiah value this voucher takes off an order: the menu item price for
     * a free item, otherwise the configured discount amount.
     */
    public function benefitAmount(): int
    {
        return (int) match ($this->type) {
            VoucherType::FreeItem => $this->menuItem?->price ?? 0,
            VoucherType::DiscountAmount => $this->discount_amount ?? 0,
        };
    }

    /**
     * The benefit a person reads on labels: what the voucher gives (admin list and kasir share this).
     */
    public function benefitLabel(): string
    {
        return match ($this->type) {
            VoucherType::FreeItem => 'Gratis: '.$this->menuItem?->name,
            VoucherType::DiscountAmount => 'Diskon '.Rupiah::format((int) $this->discount_amount),
        };
    }

    /**
     * Vouchers whose claims are open today: switched on and inside the optional date window.
     *
     * @param  Builder<Voucher>  $query
     */
    #[Scope]
    protected function openForClaims(Builder $query): void
    {
        $today = today()->toDateString();

        $query->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('available_from')->orWhere('available_from', '<=', $today))
            ->where(fn (Builder $query) => $query->whereNull('available_until')->orWhere('available_until', '>=', $today));
    }

    public function isOpenForClaims(): bool
    {
        return $this->statusLabel() === 'Aktif';
    }

    /**
     * The state a person reads in the voucher list.
     */
    public function statusLabel(): string
    {
        $today = today();

        return match (true) {
            ! $this->is_active => 'Nonaktif',
            $this->available_from !== null && $this->available_from->gt($today) => 'Belum mulai',
            $this->available_until !== null && $this->available_until->lt($today) => 'Berakhir',
            default => 'Aktif',
        };
    }
}
