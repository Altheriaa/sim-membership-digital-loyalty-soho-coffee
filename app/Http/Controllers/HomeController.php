<?php

namespace App\Http\Controllers;

use App\Models\LoyaltySetting;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Tier;
use App\Models\Voucher;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Send signed-in staff to their role workspace; everyone else sees the main promotional landing page.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($staff = $request->user('web')) {
            return redirect()->route($staff->role->homeRoute());
        }

        return view('home', $this->landingData());
    }

    /**
     * Show the membership guide and interactive points calculator page.
     */
    public function guide(): View
    {
        return view('landing', $this->landingData());
    }

    /**
     * @return array<string, mixed>
     */
    private function landingData(): array
    {
        $categories = MenuCategory::query()
            ->ordered()
            ->whereHas('items', fn (Builder $query) => $query->where('is_available', true))
            ->with(['items' => fn ($query) => $query->where('is_available', true)->orderBy('name')])
            ->get();

        return [
            'settings' => LoyaltySetting::current(),
            'tiers' => Tier::query()->ascending()->get(),
            'vouchers' => Voucher::query()->openForClaims()->with('menuItem')->orderBy('points_cost')->get(),
            'categories' => $categories,
            'menuItems' => $categories->flatMap(fn (MenuCategory $category) => $category->items->map(fn (MenuItem $item): array => [
                'id' => $item->id,
                'category_id' => $category->id,
                'category_name' => $category->name,
                'name' => $item->name,
                'price' => $item->price,
                'image_url' => $item->image_url,
            ]))->values(),
            'isMember' => auth('customer')->check(),
        ];
    }
}
