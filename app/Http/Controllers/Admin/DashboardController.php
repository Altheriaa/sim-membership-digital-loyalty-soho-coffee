<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltySetting;
use App\Models\Member;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Tier;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'availableItems' => MenuItem::query()->where('is_available', true)->count(),
            'totalItems' => MenuItem::query()->count(),
            'totalCategories' => MenuCategory::query()->count(),
            'openVouchers' => Voucher::query()->openForClaims()->count(),
            'totalTiers' => Tier::query()->count(),
            'totalMembers' => Member::query()->count(),
            'todayOrders' => Order::query()->whereDate('created_at', today())->count(),
            'todaySales' => (int) Order::query()->whereDate('created_at', today())->sum('total'),
            'activeStaff' => User::query()->where('is_active', true)->count(),
            'settings' => LoyaltySetting::current(),
        ]);
    }
}
