<?php

namespace App\Http\Controllers;

use App\Models\LoyaltySetting;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Tier;
use App\Models\User;
use App\Models\Voucher;
use App\Role;
use Illuminate\Contracts\View\View;

class OwnerDashboardController extends Controller
{
    public function __invoke(): View
    {
        $availableItems = MenuItem::where('is_available', true)->count();
        $totalItems = MenuItem::count();
        $totalCategories = MenuCategory::count();
        $openVouchers = Voucher::openForClaims()->count();

        $totalTiers = Tier::count();
        $activeStaff = User::where('role', Role::Admin)->where('is_active', true)->count();
        $settings = LoyaltySetting::current();

        return view('owner.dashboard', compact(
            'availableItems',
            'totalItems',
            'totalCategories',
            'openVouchers',
            'totalTiers',
            'activeStaff',
            'settings',
        ));
    }
}
