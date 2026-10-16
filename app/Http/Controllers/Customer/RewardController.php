<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberVoucher;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index(): View
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        $vouchers = Voucher::query()
            ->openForClaims()
            ->with('menuItem')
            ->orderBy('points_cost')
            ->get();

        return view('customer.rewards.index', [
            'member' => $member,
            'vouchers' => $vouchers,
        ]);
    }

    public function claim(Voucher $voucher): RedirectResponse
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        if (! $voucher->isOpenForClaims()) {
            return back()->withErrors(['claim' => 'Promosi voucer ini sedang tidak aktif atau belum dibuka.']);
        }

        if ($voucher->quota !== null && $voucher->quota <= 0) {
            return back()->withErrors(['claim' => 'Kuota voucer ini sudah habis.']);
        }

        if ($member->points_balance < $voucher->points_cost) {
            return back()->withErrors(['claim' => "Poin Anda tidak mencukupi (dibutuhkan {$voucher->points_cost} poin, saldo Anda {$member->points_balance} poin)."]);
        }

        $claimed = DB::transaction(function () use ($member, $voucher): MemberVoucher {
            $member->points_balance -= $voucher->points_cost;
            $member->save();

            $member->pointTransactions()->create([
                'order_id' => null,
                'points' => -$voucher->points_cost,
                'balance_after' => $member->points_balance,
                'description' => "Penukaran voucer: {$voucher->name}",
            ]);

            if ($voucher->quota !== null) {
                $voucher->decrement('quota');
            }

            $validDays = $voucher->valid_days ?? 30;

            return $member->vouchers()->create([
                'voucher_id' => $voucher->id,
                'code' => MemberVoucher::generateCode(),
                'status' => 'active',
                'points_spent' => $voucher->points_cost,
                'expires_at' => Carbon::now()->addDays($validDays)->endOfDay(),
            ]);
        });

        return redirect()->route('customer.vouchers.show', $claimed)->with('status', "Voucer {$voucher->name} berhasil diklaim!");
    }
}
