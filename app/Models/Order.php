<?php

namespace App\Models;

use App\PaymentMethod;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'cashier_id', 'member_id', 'subtotal', 'tax_percent', 'tax_amount', 'total',
    'payment_method', 'cash_received', 'change_due', 'points_earned', 'member_points_after',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'payment_method' => PaymentMethod::class,
            'cash_received' => 'integer',
            'change_due' => 'integer',
            'points_earned' => 'integer',
            'member_points_after' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * The number printed on the receipt: the sale date followed by the zero-padded id, for example TRX-20261016-000042.
     *
     * @return Attribute<string, never>
     */
    protected function number(): Attribute
    {
        return Attribute::get(fn (): string => 'TRX-'.$this->created_at->format('Ymd').'-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT));
    }
}
