<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\LoyaltySetting;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var Member $member */
        $member = Auth::guard('customer')->user();

        $tier = $member->tier();
        $nextTier = $member->nextTier();
        $spendToNext = $member->spendToNextTier();
        $progressPct = $member->tierProgressPercentage();

        $activeVouchersCount = $member->vouchers()->active()->count();
        $recentOrders = $member->orders()->latest('id')->limit(3)->get();
        $settings = LoyaltySetting::current();

        return view('customer.dashboard', [
            'member' => $member,
            'tier' => $tier,
            'nextTier' => $nextTier,
            'spendToNext' => $spendToNext,
            'progressPct' => $progressPct,
            'activeVouchersCount' => $activeVouchersCount,
            'recentOrders' => $recentOrders,
            'settings' => $settings,
        ]);
    }
}
