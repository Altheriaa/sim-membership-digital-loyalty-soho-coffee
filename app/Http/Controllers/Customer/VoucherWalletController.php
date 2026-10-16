<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberVoucher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherWalletController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();
        $tab = $request->query('tab', 'active');

        $query = $member->vouchers()->with(['voucher.menuItem'])->latest('id');

        if ($tab === 'history') {
            $query->where(function ($q): void {
                $q->where('status', 'used')->orWhere('expires_at', '<=', now());
            });
        } else {
            $query->where('status', 'active')->where('expires_at', '>', now());
        }

        $vouchers = $query->paginate(10)->withQueryString();

        $activeCount = $member->vouchers()->where('status', 'active')->where('expires_at', '>', now())->count();
        $historyCount = $member->vouchers()->where(fn ($q) => $q->where('status', 'used')->orWhere('expires_at', '<=', now()))->count();

        return view('customer.vouchers.index', [
            'member' => $member,
            'tab' => $tab,
            'vouchers' => $vouchers,
            'activeCount' => $activeCount,
            'historyCount' => $historyCount,
        ]);
    }

    public function show(MemberVoucher $voucher): View
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        abort_if($voucher->member_id !== $member->id, 403);

        $voucher->load(['voucher.menuItem', 'order']);

        return view('customer.vouchers.show', [
            'member' => $member,
            'voucher' => $voucher,
        ]);
    }
}
