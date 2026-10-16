<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;

class MenuItemAvailabilityController extends Controller
{
    /**
     * Flip a menu item between Tersedia and Habis.
     */
    public function __invoke(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        $state = $menuItem->is_available ? 'tersedia' : 'habis';

        return back()->with('status', "{$menuItem->name} ditandai {$state}.");
    }
}
