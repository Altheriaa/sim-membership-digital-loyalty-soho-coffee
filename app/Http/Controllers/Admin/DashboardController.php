<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltySetting;
use App\Models\MenuCategory;
use App\Models\MenuItem;
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
            'activeStaff' => User::query()->where('is_active', true)->count(),
            'settings' => LoyaltySetting::current(),
        ]);
    }
}
