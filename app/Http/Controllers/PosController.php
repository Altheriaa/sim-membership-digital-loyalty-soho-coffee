<?php

namespace App\Http\Controllers;

use App\Models\LoyaltySetting;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Contracts\View\View;

class PosController extends Controller
{
    public function __invoke(): View
    {
        $settings = LoyaltySetting::current();

        return view('pos.index', [
            'categories' => MenuCategory::query()->ordered()->get(['id', 'name']),
            'items' => MenuItem::query()
                ->orderBy('name')
                ->orderBy('id')
                ->get()
                ->map(fn (MenuItem $item): array => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'categoryId' => $item->menu_category_id,
                    'available' => $item->is_available,
                    'imageUrl' => $item->image_url,
                ])
                ->all(),
            'settings' => $settings,
        ]);
    }
}
