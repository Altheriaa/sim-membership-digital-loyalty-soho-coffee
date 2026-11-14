<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoidOrderRequest;
use App\Models\Member;
use App\Models\MemberVoucher;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderVoidController extends Controller
{
    public function __invoke(VoidOrderRequest $request, Order $order): RedirectResponse
    {
        $reason = trim($request->string('void_reason')->toString());

        DB::transaction(function () use ($order, $reason, $request): void {
            /** @var Order $lockedOrder */
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->isVoided()) {
                throw ValidationException::withMessages([
                    'void' => 'Transaksi ini sudah dibatalkan sebelumnya.',
                ]);
            }

            if ($lockedOrder->member_id !== null) {
                /** @var Member $member */
                $member = Member::query()->lockForUpdate()->findOrFail($lockedOrder->member_id);

                if (($member->points_balance + $lockedOrder->points_redeemed) < $lockedOrder->points_earned) {
                    throw ValidationException::withMessages([
                        'void' => 'Poin yang diperoleh dari transaksi ini sudah dipakai menukar voucer. Sesuaikan poin member terlebih dahulu.',
                    ]);
                }

                if ($lockedOrder->points_redeemed > 0) {
                    $member->points_balance += $lockedOrder->points_redeemed;
                    $member->pointTransactions()->create([
                        'order_id' => $lockedOrder->id,
                        'points' => $lockedOrder->points_redeemed,
                        'balance_after' => $member->points_balance,
                        'description' => "Pengembalian poin pembatalan {$lockedOrder->number}",
                    ]);
                }

                if ($lockedOrder->points_earned > 0) {
                    $member->points_balance -= $lockedOrder->points_earned;
                    $member->pointTransactions()->create([
                        'order_id' => $lockedOrder->id,
                        'points' => -$lockedOrder->points_earned,
                        'balance_after' => $member->points_balance,
                        'description' => "Pembatalan poin {$lockedOrder->number}",
                    ]);
                }

                $netSpend = max(0, $lockedOrder->subtotal - $lockedOrder->discount_amount);
                $member->lifetime_spend = max(0, $member->lifetime_spend - $netSpend);
                $member->save();
            }

            if ($lockedOrder->voucher_id !== null) {
                $memberVoucher = MemberVoucher::query()->lockForUpdate()->find($lockedOrder->voucher_id);

                if ($memberVoucher !== null) {
                    $memberVoucher->update([
                        'status' => 'active',
                        'used_at' => null,
                        'order_id' => null,
                    ]);
                }
            }

            $lockedOrder->update([
                'voided_at' => now(),
                'voided_by' => $request->user()->id,
                'void_reason' => $reason,
            ]);
        });

        return back()->with('status', "Transaksi {$order->number} berhasil dibatalkan.");
    }
}
