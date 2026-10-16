<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;

test('category index lists categories by display order with their menu counts', function () {
    signInAsAdmin();
    $drinks = MenuCategory::factory()->create(['name' => 'Minuman', 'sort_order' => 2]);
    MenuCategory::factory()->create(['name' => 'Kopi', 'sort_order' => 1]);
    MenuItem::factory()->count(3)->create(['menu_category_id' => $drinks->id]);

    $response = $this->get(route('admin.categories.index'));

    $response->assertOk()
        ->assertSeeTextInOrder(['Kopi', '0', 'Minuman', '3']);
});

test('category index explains what a category is when none exist', function () {
    signInAsAdmin();

    $response = $this->get(route('admin.categories.index'));

    $response->assertOk()
        ->assertSeeText('Belum ada kategori')
        ->assertSeeText('Tambah kategori pertama');
});

test('admin creates a category', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.categories.store'), ['name' => 'Kopi', 'sort_order' => 1]);

    $response->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('status', 'Kategori Kopi ditambahkan.');
    expect(MenuCategory::query()->where('name', 'Kopi')->firstOrFail()->sort_order)->toBe(1);
});

test('creating a category requires a name and an order', function () {
    signInAsAdmin();

    $response = $this->from(route('admin.categories.create'))->post(route('admin.categories.store'), []);

    $response->assertSessionHasErrors([
        'name' => 'Nama wajib diisi.',
        'sort_order' => 'Urutan wajib diisi.',
    ]);
    expect(MenuCategory::query()->count())->toBe(0);
});

test('creating a category rejects a duplicate name and a negative order', function () {
    signInAsAdmin();
    MenuCategory::factory()->create(['name' => 'Kopi']);

    $response = $this->post(route('admin.categories.store'), ['name' => 'Kopi', 'sort_order' => -1]);

    $response->assertSessionHasErrors([
        'name' => 'Nama sudah dipakai.',
        'sort_order' => 'Urutan minimal 0.',
    ]);
    expect(MenuCategory::query()->count())->toBe(1);
});

test('admin renames a category and may keep its own name', function () {
    signInAsAdmin();
    $category = MenuCategory::factory()->create(['name' => 'Kopi', 'sort_order' => 1]);

    $this->put(route('admin.categories.update', $category), ['name' => 'Kopi', 'sort_order' => 5])
        ->assertRedirect(route('admin.categories.index'));

    expect($category->refresh()->sort_order)->toBe(5);

    $this->put(route('admin.categories.update', $category), ['name' => 'Kopi Spesial', 'sort_order' => 5])
        ->assertRedirect(route('admin.categories.index'));

    expect($category->refresh()->name)->toBe('Kopi Spesial');
});

test('admin deletes an empty category', function () {
    signInAsAdmin();
    $category = MenuCategory::factory()->create(['name' => 'Musiman']);

    $response = $this->delete(route('admin.categories.destroy', $category));

    $response->assertRedirect(route('admin.categories.index'))
        ->assertSessionHas('status', 'Kategori Musiman dihapus.');
    expect(MenuCategory::query()->whereKey($category->id)->exists())->toBeFalse();
});

test('a category that still holds menu items cannot be deleted', function () {
    signInAsAdmin();
    $category = MenuCategory::factory()->create(['name' => 'Kopi']);
    MenuItem::factory()->create(['menu_category_id' => $category->id]);

    $response = $this->from(route('admin.categories.edit', $category))->delete(route('admin.categories.destroy', $category));

    $response->assertRedirect(route('admin.categories.edit', $category))
        ->assertSessionHasErrors(['category' => 'Kategori Kopi masih berisi menu. Pindahkan atau hapus menunya dulu.']);
    expect(MenuCategory::query()->whereKey($category->id)->exists())->toBeTrue();
});

test('category names are escaped in the list', function () {
    signInAsAdmin();
    MenuCategory::factory()->create(['name' => '<b>Tebal</b>']);

    $this->get(route('admin.categories.index'))
        ->assertOk()
        ->assertDontSee('<b>Tebal</b>', false)
        ->assertSee(e('<b>Tebal</b>'), false);
});
