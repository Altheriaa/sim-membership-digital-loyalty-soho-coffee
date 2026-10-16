<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexMenuItemsRequest;
use App\Http\Requests\MenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Voucher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(IndexMenuItemsRequest $request): View
    {
        $categoryId = $request->categoryId();
        $search = $request->search();

        return view('admin.menu-items.index', [
            'items' => MenuItem::query()
                ->with('category')
                ->inCategory($categoryId)
                ->matching($search)
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15)
                ->withQueryString(),
            'categories' => MenuCategory::query()->ordered()->get(['id', 'name']),
            'totalItems' => MenuItem::query()->count(),
            'categoryId' => $categoryId,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.menu-items.form', [
            'item' => new MenuItem(['is_available' => true]),
            'categories' => MenuCategory::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function store(MenuItemRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['menu_category_id', 'name', 'price']);
        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu-items', MenuItem::IMAGE_DISK);
        }

        $item = MenuItem::create($data);

        return redirect()->route('admin.menu-items.index')->with('status', "Menu {$item->name} ditambahkan.");
    }

    public function edit(MenuItem $menuItem): View
    {
        return view('admin.menu-items.form', [
            'item' => $menuItem,
            'categories' => MenuCategory::query()->ordered()->get(['id', 'name']),
        ]);
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->safe()->only(['menu_category_id', 'name', 'price']);
        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('image')) {
            $this->deleteImage($menuItem);
            $data['image_path'] = $request->file('image')->store('menu-items', MenuItem::IMAGE_DISK);
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($menuItem);
            $data['image_path'] = null;
        }

        $menuItem->update($data);

        return redirect()->route('admin.menu-items.index')->with('status', "Menu {$menuItem->name} diperbarui.");
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        if (Voucher::withTrashed()->where('menu_item_id', $menuItem->id)->exists()) {
            return back()->withErrors([
                'menu_item' => "Menu {$menuItem->name} dipakai oleh voucer. Ubah voucernya dulu, atau tandai menu ini Habis.",
            ]);
        }

        $this->deleteImage($menuItem);
        $menuItem->delete();

        return redirect()->route('admin.menu-items.index')->with('status', "Menu {$menuItem->name} dihapus.");
    }

    private function deleteImage(MenuItem $menuItem): void
    {
        if ($menuItem->image_path !== null) {
            Storage::disk(MenuItem::IMAGE_DISK)->delete($menuItem->image_path);
        }
    }
}
