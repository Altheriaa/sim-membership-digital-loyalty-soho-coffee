<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['member_id', 'voucher_id', 'code', 'status', 'points_spent', 'expires_at', 'used_at', 'order_id'])]
class MemberVoucher extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points_spent' => 'integer',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo<Voucher, $this>
     */
    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active')->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->status === 'active' && $this->expires_at->isPast();
    }

    public function statusLabel(): string
    {
        if ($this->status === 'used') {
            return 'Sudah Digunakan';
        }

        if ($this->isExpired()) {
            return 'Kedaluwarsa';
        }

        return 'Siap Digunakan';
    }

    public function remainingTime(): string
    {
        if ($this->expires_at->isPast()) {
            return 'Berakhir';
        }

        $now = Carbon::now();
        $diffDays = (int) $now->diffInDays($this->expires_at);

        if ($diffDays > 0) {
            return "{$diffDays} hari lagi";
        }

        $diffHours = (int) $now->diffInHours($this->expires_at);
        if ($diffHours > 0) {
            return "{$diffHours} jam lagi";
        }

        $diffMinutes = (int) $now->diffInMinutes($this->expires_at);

        return "{$diffMinutes} menit lagi";
    }

    public static function generateCode(): string
    {
        do {
            $code = 'KOP-'.strtoupper(Str::random(4));
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }
}
