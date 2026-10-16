<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuCategoryRequest;
use App\Models\MenuCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MenuCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => MenuCategory::query()->withCount('items')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new MenuCategory(['sort_order' => 0]),
        ]);
    }

    public function store(MenuCategoryRequest $request): RedirectResponse
    {
        $category = MenuCategory::create($request->validated());

        return redirect()->route('admin.categories.index')->with('status', "Kategori {$category->name} ditambahkan.");
    }

    public function edit(MenuCategory $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(MenuCategoryRequest $request, MenuCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('status', "Kategori {$category->name} diperbarui.");
    }

    public function destroy(MenuCategory $category): RedirectResponse
    {
        if ($category->items()->exists()) {
            return back()->withErrors([
                'category' => "Kategori {$category->name} masih berisi menu. Pindahkan atau hapus menunya dulu.",
            ]);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', "Kategori {$category->name} dihapus.");
    }
}
