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
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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
        $taxAmount = $settings->taxFor($subtotal);

        $order = DB::transaction(function () use ($request, $settings, $orderLines, $subtotal, $taxAmount): Order {
            $member = $request->filled('member_id')
                ? Member::query()->lockForUpdate()->findOrFail($request->integer('member_id'))
                : null;

            $memberVoucher = null;

            if ($request->filled('voucher_id')) {
                $memberVoucher = $this->lockedRedeemableVoucher($request, $member);
            }

            $discountAmount = $memberVoucher !== null ? min($memberVoucher->voucher->benefitAmount(), $subtotal) : 0;
            $total = $subtotal - $discountAmount + $taxAmount;

            $paymentMethod = $request->enum('payment_method', PaymentMethod::class);
            $cashReceived = $paymentMethod === PaymentMethod::Cash ? (int) $request->validated('cash_received') : null;

            if ($cashReceived !== null && $cashReceived < $total) {
                throw ValidationException::withMessages([
                    'cash_received' => 'Uang diterima kurang '.Rupiah::format($total - $cashReceived).' dari total '.Rupiah::format($total).'.',
                ]);
            }

            $pointsEarned = $member
                ? $settings->pointsFor($subtotal, $member->tier()?->multiplierHundredths() ?? 100)
                : 0;

            if ($member) {
                $member->points_balance += $pointsEarned;
                $member->lifetime_spend += $subtotal;
                $member->save();
            }

            $order = Order::create([
                'cashier_id' => $request->user()->id,
                'member_id' => $member?->id,
                'subtotal' => $subtotal,
                'tax_percent' => $settings->tax_percent,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'cash_received' => $cashReceived,
                'change_due' => $cashReceived !== null ? $cashReceived - $total : null,
                'points_earned' => $pointsEarned,
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

        return response()->json([
            'receipt_url' => route('pos.orders.show', $order),
        ], 201);
    }

    // Nampilin Nota Transaksi
    public function show(Order $order): View
    {
        return view('pos.receipt', [
            'order' => $order->load(['cashier', 'member', 'items' => fn ($query) => $query->orderBy('id')]),
        ]);
    }

    /**
     * The claimed voucher behind a payment, locked and fully re-verified at this
     * instant: owned by the selected member, still unused and unexpired, paid
     * for with the right member PIN. The kasir lookup is only for display.
     */
    private function lockedRedeemableVoucher(OrderRequest $request, Member $member): MemberVoucher
    {
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
            throw ValidationException::withMessages([
                'voucher_pin' => 'PIN Keamanan member salah. Minta member memeriksa PIN-nya lalu coba lagi.',
            ]);
        }

        return $memberVoucher;
    }
}
