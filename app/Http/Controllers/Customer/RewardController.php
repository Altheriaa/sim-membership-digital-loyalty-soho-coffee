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
use Illuminate\Validation\ValidationException;

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

        $claimed = DB::transaction(function () use ($member, $voucher): MemberVoucher {
            $lockedVoucher = Voucher::query()->lockForUpdate()->findOrFail($voucher->id);

            if (! $lockedVoucher->isOpenForClaims()) {
                throw ValidationException::withMessages([
                    'claim' => 'Promosi voucer ini sedang tidak aktif atau belum dibuka.',
                ]);
            }

            if ($lockedVoucher->quota !== null && $lockedVoucher->quota <= 0) {
                throw ValidationException::withMessages([
                    'claim' => 'Kuota voucer ini sudah habis.',
                ]);
            }

            $lockedMember = Member::query()->lockForUpdate()->findOrFail($member->id);

            if ($lockedMember->points_balance < $lockedVoucher->points_cost) {
                throw ValidationException::withMessages([
                    'claim' => "Poin Anda tidak mencukupi (dibutuhkan {$lockedVoucher->points_cost} poin, saldo Anda {$lockedMember->points_balance} poin).",
                ]);
            }

            $lockedMember->points_balance -= $lockedVoucher->points_cost;
            $lockedMember->save();

            $lockedMember->pointTransactions()->create([
                'order_id' => null,
                'points' => -$lockedVoucher->points_cost,
                'balance_after' => $lockedMember->points_balance,
                'description' => "Penukaran voucer: {$lockedVoucher->name}",
            ]);

            if ($lockedVoucher->quota !== null) {
                $lockedVoucher->decrement('quota');
            }

            $validDays = $lockedVoucher->valid_days ?? 30;

            return $lockedMember->vouchers()->create([
                'voucher_id' => $lockedVoucher->id,
                'code' => MemberVoucher::generateCode(),
                'status' => 'active',
                'points_spent' => $lockedVoucher->points_cost,
                'expires_at' => Carbon::now()->addDays($validDays)->endOfDay(),
            ]);
        });

        return redirect()->route('customer.vouchers.show', $claimed)->with('status', "Voucer {$voucher->name} berhasil diklaim!");
    }
}
