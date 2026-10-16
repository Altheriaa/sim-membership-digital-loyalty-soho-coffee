<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\LoyaltySetting;
use App\Models\Member;
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
        $total = $subtotal + $taxAmount;

        $paymentMethod = $request->enum('payment_method', PaymentMethod::class);
        $cashReceived = $paymentMethod === PaymentMethod::Cash ? (int) $request->validated('cash_received') : null;

        if ($cashReceived !== null && $cashReceived < $total) {
            throw ValidationException::withMessages([
                'cash_received' => 'Uang diterima kurang '.Rupiah::format($total - $cashReceived).' dari total '.Rupiah::format($total).'.',
            ]);
        }

        $order = DB::transaction(function () use ($request, $settings, $orderLines, $subtotal, $taxAmount, $total, $paymentMethod, $cashReceived): Order {
            $member = $request->filled('member_id')
                ? Member::query()->lockForUpdate()->findOrFail($request->integer('member_id'))
                : null;

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
                'total' => $total,
                'payment_method' => $paymentMethod,
                'cash_received' => $cashReceived,
                'change_due' => $cashReceived !== null ? $cashReceived - $total : null,
                'points_earned' => $pointsEarned,
                'member_points_after' => $member?->points_balance,
            ]);

            $order->items()->createMany($orderLines->all());

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

    public function show(Order $order): View
    {
        return view('pos.receipt', [
            'order' => $order->load(['cashier', 'member', 'items' => fn ($query) => $query->orderBy('id')]),
        ]);
    }
}
