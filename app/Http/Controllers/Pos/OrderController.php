<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\LoyaltySetting;
use App\Models\Member;
use App\Models\MemberVoucher;
use App\Models\MenuItem;
use App\Models\Order;
use App\PaymentMethod;
use App\Support\Rupiah;
use App\VoucherType;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    // Transaksi order
    public function store(OrderRequest $request): JsonResponse
    {
        $settings = LoyaltySetting::current();
        $lines = collect($request->validated('items'));
        $menu = MenuItem::query()->findMany($lines->pluck('id'))->keyBy('id');

        $soldOut = $lines->map(fn (array $line): MenuItem => $menu[$line['id']])->reject->is_available;

        if ($soldOut->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Menu '.$soldOut->pluck('name')->join(', ', ' dan ').' sedang habis. Hapus dari keranjang lalu bayar lagi.',
            ]);
        }

        $orderLines = $lines->map(fn (array $line): array => [
            'menu_item_id' => $line['id'],
            'name' => $menu[$line['id']]->name,
            'unit_price' => $menu[$line['id']]->price,
            'quantity' => $line['quantity'],
            'line_total' => $menu[$line['id']]->price * $line['quantity'],
        ]);

        $subtotal = $orderLines->sum('line_total');
        $pointsToRedeem = $request->integer('points_to_redeem', 0);

        $pinRateKey = $request->filled('member_id')
            ? 'pos-member-pin:'.$request->user()->id.':'.$request->integer('member_id')
            : null;

        $requiresMemberPin = $request->filled('voucher_id') || $pointsToRedeem > 0;

        if ($pinRateKey !== null && $requiresMemberPin && RateLimiter::tooManyAttempts($pinRateKey, 5)) {
            $seconds = RateLimiter::availableIn($pinRateKey);

            throw ValidationException::withMessages([
                'voucher_pin' => "Terlalu banyak percobaan PIN member yang salah. Tunggu {$seconds} detik lalu coba lagi.",
            ]);
        }

        $pinMismatch = false;

        try {
            $order = DB::transaction(function () use ($request, $settings, $orderLines, $subtotal, $pointsToRedeem, &$pinMismatch): Order {
                $member = $request->filled('member_id')
                    ? Member::query()->lockForUpdate()->findOrFail($request->integer('member_id'))
                    : null;

                $memberVoucher = null;
                $voucherDiscount = 0;

                if ($request->filled('voucher_id')) {
                    $memberVoucher = $this->lockedRedeemableVoucher($request, $member, $pinMismatch);
                    $voucher = $memberVoucher->voucher;

                    if ($voucher->type === VoucherType::FreeItem) {
                        $matchingLine = $voucher->menu_item_id !== null
                            ? $orderLines->firstWhere('menu_item_id', $voucher->menu_item_id)
                            : null;

                        if ($matchingLine === null) {
                            throw ValidationException::withMessages([
                                'voucher_id' => 'Voucer ini hanya berlaku jika menu '.($voucher->menuItem?->name ?? 'terkait').' ada di keranjang.',
                            ]);
                        }

                        $voucherDiscount = min($voucher->benefitAmount(), $matchingLine['unit_price'], $subtotal);
                    } else {
                        $voucherDiscount = min($voucher->benefitAmount(), $subtotal);
                    }
                } elseif ($pointsToRedeem > 0) {
                    if ($member === null) {
                        throw ValidationException::withMessages([
                            'points_to_redeem' => 'Pilih member terlebih dahulu sebelum menukarkan poin.',
                        ]);
                    }

                    if (! $request->filled('voucher_pin')) {
                        throw ValidationException::withMessages([
                            'voucher_pin' => 'Masukkan 6-digit PIN Keamanan member untuk menukarkan poin.',
                        ]);
                    }

                    if (! $member->verifyPin($request->string('voucher_pin'))) {
                        $pinMismatch = true;

                        throw ValidationException::withMessages([
                            'voucher_pin' => 'PIN Keamanan member salah. Minta member memeriksa PIN-nya lalu coba lagi.',
                        ]);
                    }
                }

                $remainingAfterVoucher = max(0, $subtotal - $voucherDiscount);
                $pointDiscount = 0;

                if ($pointsToRedeem > 0) {
                    if ($pointsToRedeem > $member->points_balance) {
                        throw ValidationException::withMessages([
                            'points_to_redeem' => "Saldo poin member tidak mencukupi (tersedia {$member->points_balance} poin).",
                        ]);
                    }

                    $pointValue = max(1, $settings->point_value);
                    $maxUsablePoints = min($member->points_balance, (int) floor($remainingAfterVoucher / $pointValue));

                    if ($pointsToRedeem > $maxUsablePoints) {
                        throw ValidationException::withMessages([
                            'points_to_redeem' => "Maksimal poin yang bisa ditukar untuk tagihan ini adalah {$maxUsablePoints} poin.",
                        ]);
                    }

                    $pointDiscount = $pointsToRedeem * $pointValue;
                }

                $discountAmount = $voucherDiscount + $pointDiscount;
                $netSubtotal = max(0, $subtotal - $discountAmount);
                $taxAmount = $settings->taxFor($netSubtotal);
                $total = $netSubtotal + $taxAmount;

                $paymentMethod = $request->enum('payment_method', PaymentMethod::class);
                $cashReceived = $paymentMethod === PaymentMethod::Cash ? (int) $request->validated('cash_received') : null;

                if ($cashReceived !== null && $cashReceived < $total) {
                    throw ValidationException::withMessages([
                        'cash_received' => 'Uang diterima kurang '.Rupiah::format($total - $cashReceived).' dari total '.Rupiah::format($total).'.',
                    ]);
                }

                $pointsEarned = $member
                    ? $settings->pointsFor($netSubtotal, $member->tier()?->multiplierHundredths() ?? 100)
                    : 0;

                $balanceAfterRedeem = null;

                if ($member) {
                    if ($pointsToRedeem > 0) {
                        $member->points_balance -= $pointsToRedeem;
                        $balanceAfterRedeem = $member->points_balance;
                    }

                    $member->points_balance += $pointsEarned;
                    $member->lifetime_spend += $netSubtotal;
                    $member->save();
                }

                $order = Order::create([
                    'cashier_id' => $request->user()->id,
                    'member_id' => $member?->id,
                    'subtotal' => $subtotal,
                    'tax_percent' => $settings->tax_percent,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'point_discount_amount' => $pointDiscount,
                    'total' => $total,
                    'payment_method' => $paymentMethod,
                    'cash_received' => $cashReceived,
                    'change_due' => $cashReceived !== null ? $cashReceived - $total : null,
                    'points_earned' => $pointsEarned,
                    'points_redeemed' => $pointsToRedeem,
                    'member_points_after' => $member?->points_balance,
                    'voucher_id' => $memberVoucher?->id,
                ]);

                $order->items()->createMany($orderLines->all());

                if ($memberVoucher !== null) {
                    $memberVoucher->update([
                        'status' => 'used',
                        'used_at' => now(),
                        'order_id' => $order->id,
                    ]);
                }

                if ($member && $pointsToRedeem > 0) {
                    $member->pointTransactions()->create([
                        'order_id' => $order->id,
                        'points' => -$pointsToRedeem,
                        'balance_after' => $balanceAfterRedeem,
                        'description' => "Tukar poin di {$order->number}",
                    ]);
                }

                if ($member && $pointsEarned > 0) {
                    $member->pointTransactions()->create([
                        'order_id' => $order->id,
                        'points' => $pointsEarned,
                        'balance_after' => $member->points_balance,
                        'description' => "Belanja {$order->number}",
                    ]);
                }

                return $order;
            });
        } catch (ValidationException $exception) {
            // Hit rate limiter outside transaction rollback so failed PIN attempts always persist.
            if ($pinMismatch && $pinRateKey !== null) {
                RateLimiter::hit($pinRateKey, 60);
            }

            throw $exception;
        }

        if ($pinRateKey !== null && $requiresMemberPin) {
            RateLimiter::clear($pinRateKey);
        }

        return response()->json([
            'receipt_url' => route('pos.orders.show', $order),
        ], 201);
    }

    // Nampilin Nota Transaksi
    public function show(Order $order): View
    {
        return view('pos.receipt', [
            'order' => $order->load(['cashier', 'member', 'voidedBy', 'items' => fn ($query) => $query->orderBy('id')]),
        ]);
    }

    private function lockedRedeemableVoucher(OrderRequest $request, ?Member $member, bool &$pinMismatch): MemberVoucher
    {
        if ($member === null) {
            throw ValidationException::withMessages([
                'voucher_id' => 'Pilih member pemilik voucer terlebih dahulu.',
            ]);
        }

        /** @var MemberVoucher $memberVoucher */
        $memberVoucher = MemberVoucher::query()
            ->lockForUpdate()
            ->with('voucher.menuItem')
            ->findOrFail($request->integer('voucher_id'));

        if ($memberVoucher->member_id !== $member->id) {
            throw ValidationException::withMessages([
                'voucher_id' => 'Voucer ini milik member lain. Hapus voucer dari keranjang.',
            ]);
        }

        if (! $memberVoucher->isRedeemable() || $memberVoucher->order_id !== null) {
            throw ValidationException::withMessages([
                'voucher_id' => $memberVoucher->order_id !== null
                    ? 'Voucer sudah pernah digunakan pada transaksi lain.'
                    : 'Voucer sudah kedaluwarsa. Hapus voucer dari keranjang.',
            ]);
        }

        if (! $member->verifyPin($request->string('voucher_pin'))) {
            $pinMismatch = true;

            throw ValidationException::withMessages([
                'voucher_pin' => 'PIN Keamanan member salah. Minta member memeriksa PIN-nya lalu coba lagi.',
            ]);
        }

        return $memberVoucher;
    }
}
