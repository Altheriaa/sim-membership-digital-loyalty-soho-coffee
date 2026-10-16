<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Voucher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('menu index lists items by name with category, price in rupiah and availability', function () {
    signInAsAdmin();
    $coffee = MenuCategory::factory()->create(['name' => 'Kopi']);
    MenuItem::factory()->create(['menu_category_id' => $coffee->id, 'name' => 'Kopi Susu', 'price' => 25000]);
    MenuItem::factory()->soldOut()->create(['menu_category_id' => $coffee->id, 'name' => 'Americano', 'price' => 1500000]);

    $response = $this->get(route('admin.menu-items.index'));

    $response->assertOk()
        ->assertSeeTextInOrder(['Americano', 'Kopi', 'Rp 1.500.000', 'Habis'])
        ->assertSeeTextInOrder(['Kopi Susu', 'Kopi', 'Rp 25.000', 'Tersedia']);
});

test('menu index only lists items of the requested category', function () {
    signInAsAdmin();
    $coffee = MenuCategory::factory()->create(['name' => 'Kopi']);
    $food = MenuCategory::factory()->create(['name' => 'Makanan']);
    MenuItem::factory()->create(['menu_category_id' => $coffee->id, 'name' => 'Kopi Susu']);
    MenuItem::factory()->create(['menu_category_id' => $food->id, 'name' => 'Roti Bakar']);

    $response = $this->get(route('admin.menu-items.index', ['category' => $food->id]));

    $response->assertOk()
        ->assertSeeText('Roti Bakar')
        ->assertDontSeeText('Kopi Susu');
});

test('menu index searches item names and treats percent signs literally', function () {
    signInAsAdmin();
    MenuItem::factory()->create(['name' => 'Diskon 100% Kopi']);
    MenuItem::factory()->create(['name' => 'Roti Bakar']);

    $this->get(route('admin.menu-items.index', ['search' => 'roti']))
        ->assertOk()
        ->assertSeeText('Roti Bakar')
        ->assertDontSeeText('Diskon 100% Kopi');

    $this->get(route('admin.menu-items.index', ['search' => '%']))
        ->assertOk()
        ->assertSeeText('Diskon 100% Kopi')
        ->assertDontSeeText('Roti Bakar');
});

test('menu index redirects an unknown category to the unfiltered list with an error', function () {
    signInAsAdmin();

    $this->get(route('admin.menu-items.index', ['category' => 9999]))
        ->assertRedirect(route('admin.menu-items.index'))
        ->assertSessionHasErrors('category');
});

test('menu index asks for a category first when there are no categories', function () {
    signInAsAdmin();

    $response = $this->get(route('admin.menu-items.index'));

    $response->assertOk()
        ->assertSeeText('Belum ada menu')
        ->assertSeeText('Menu harus masuk ke sebuah kategori. Buat kategori dulu, lalu tambahkan menu pertama.');
});

test('menu index invites the first item when categories exist but items do not', function () {
    signInAsAdmin();
    MenuCategory::factory()->create();

    $response = $this->get(route('admin.menu-items.index'));

    $response->assertOk()
        ->assertSeeText('Tambahkan menu pertama supaya kasir bisa mulai menjual.')
        ->assertSeeText('Tambah menu pertama');
});

test('menu index names the search term when nothing matches', function () {
    signInAsAdmin();
    MenuItem::factory()->create(['name' => 'Kopi Susu']);

    $response = $this->get(route('admin.menu-items.index', ['search' => 'zzz']));

    $response->assertOk()
        ->assertSeeText('Tidak ada menu yang cocok dengan “zzz”')
        ->assertSeeText('Hapus filter');
});

test('menu index paginates fifteen items per page', function () {
    signInAsAdmin();
    $category = MenuCategory::factory()->create();
    MenuItem::factory()->count(16)->sequence(fn ($sequence) => [
        'menu_category_id' => $category->id,
        'name' => sprintf('Hidangan %02d', $sequence->index + 1),
    ])->create();

    $this->get(route('admin.menu-items.index'))
        ->assertOk()
        ->assertSeeText('Hidangan 15')
        ->assertDontSeeText('Hidangan 16')
        ->assertSeeText('Menampilkan 1 sampai 15 dari 16');

    $this->get(route('admin.menu-items.index', ['page' => 2]))
        ->assertOk()
        ->assertSeeText('Hidangan 16');
});

test('menu index escapes item names', function () {
    signInAsAdmin();
    MenuItem::factory()->create(['name' => '<script>alert(1)</script>']);

    $this->get(route('admin.menu-items.index'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee(e('<script>alert(1)</script>'), false);
});

test('the create form asks for a category first when none exist', function () {
    signInAsAdmin();

    $this->get(route('admin.menu-items.create'))
        ->assertOk()
        ->assertSeeText('Buat kategori dulu')
        ->assertDontSee('name="price"', false);
});

test('admin creates a menu item with a photo', function () {
    Storage::fake('public');
    signInAsAdmin();
    $category = MenuCategory::factory()->create();

    $response = $this->post(route('admin.menu-items.store'), [
        'menu_category_id' => $category->id,
        'name' => 'Kopi Susu',
        'price' => 25000,
        'is_available' => '1',
        'image' => UploadedFile::fake()->image('kopi.jpg', 600, 600),
    ]);

    $response->assertRedirect(route('admin.menu-items.index'))
        ->assertSessionHas('status', 'Menu Kopi Susu ditambahkan.');

    $item = MenuItem::query()->where('name', 'Kopi Susu')->firstOrFail();

    expect($item->menu_category_id)->toBe($category->id)
        ->and($item->price)->toBe(25000)
        ->and($item->is_available)->toBeTrue()
        ->and($item->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($item->image_path);
});

test('admin creates a sold-out menu item without a photo', function () {
    signInAsAdmin();
    $category = MenuCategory::factory()->create();

    $this->post(route('admin.menu-items.store'), [
        'menu_category_id' => $category->id,
        'name' => 'Cokelat',
        'price' => 24000,
        'is_available' => '0',
    ])->assertRedirect(route('admin.menu-items.index'));

    $item = MenuItem::query()->where('name', 'Cokelat')->firstOrFail();

    expect($item->is_available)->toBeFalse()
        ->and($item->image_path)->toBeNull();
});

test('creating a menu item requires a category, name and price', function () {
    signInAsAdmin();

    $response = $this->from(route('admin.menu-items.create'))->post(route('admin.menu-items.store'), []);

    $response->assertSessionHasErrors([
        'menu_category_id' => 'Kategori wajib diisi.',
        'name' => 'Nama wajib diisi.',
        'price' => 'Harga wajib diisi.',
    ]);
    expect(MenuItem::query()->count())->toBe(0);
});

test('creating a menu item rejects a negative price and an unknown category', function () {
    signInAsAdmin();

    $response = $this->post(route('admin.menu-items.store'), [
        'menu_category_id' => 9999,
        'name' => 'Kopi Susu',
        'price' => -1,
    ]);

    $response->assertSessionHasErrors([
        'menu_category_id' => 'Kategori yang dipilih tidak valid.',
        'price' => 'Harga minimal 0.',
    ]);
});

test('creating a menu item rejects a file that is not an image', function () {
    Storage::fake('public');
    signInAsAdmin();
    $category = MenuCategory::factory()->create();

    $response = $this->post(route('admin.menu-items.store'), [
        'menu_category_id' => $category->id,
        'name' => 'Kopi Susu',
        'price' => 25000,
        'image' => UploadedFile::fake()->create('menu.pdf', 10, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors(['image' => 'Foto harus berupa gambar (jpg, png, atau webp).']);
    expect(MenuItem::query()->count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

test('admin updates a menu item and replaces its photo, deleting the old file', function () {
    Storage::fake('public');
    signInAsAdmin();
    Storage::disk('public')->put('menu-items/lama.jpg', 'lama');
    $item = MenuItem::factory()->create(['name' => 'Kopi Susu', 'price' => 25000, 'image_path' => 'menu-items/lama.jpg']);
    $otherCategory = MenuCategory::factory()->create();

    $this->put(route('admin.menu-items.update', $item), [
        'menu_category_id' => $otherCategory->id,
        'name' => 'Kopi Susu Gula Aren',
        'price' => 27000,
        'is_available' => '1',
        'image' => UploadedFile::fake()->image('baru.png', 400, 400),
    ])->assertRedirect(route('admin.menu-items.index'));

    $item->refresh();

    expect($item->name)->toBe('Kopi Susu Gula Aren')
        ->and($item->price)->toBe(27000)
        ->and($item->menu_category_id)->toBe($otherCategory->id)
        ->and($item->image_path)->not->toBe('menu-items/lama.jpg');
    Storage::disk('public')->assertMissing('menu-items/lama.jpg');
    Storage::disk('public')->assertExists($item->image_path);
});

test('admin keeps the photo when updating without a new file', function () {
    Storage::fake('public');
    signInAsAdmin();
    Storage::disk('public')->put('menu-items/tetap.jpg', 'tetap');
    $item = MenuItem::factory()->create(['image_path' => 'menu-items/tetap.jpg']);

    $this->put(route('admin.menu-items.update', $item), [
        'menu_category_id' => $item->menu_category_id,
        'name' => 'Nama Baru',
        'price' => $item->price,
        'is_available' => '1',
    ])->assertRedirect(route('admin.menu-items.index'));

    expect($item->refresh()->image_path)->toBe('menu-items/tetap.jpg');
    Storage::disk('public')->assertExists('menu-items/tetap.jpg');
});

test('admin removes the photo with the remove checkbox', function () {
    Storage::fake('public');
    signInAsAdmin();
    Storage::disk('public')->put('menu-items/buang.jpg', 'buang');
    $item = MenuItem::factory()->create(['image_path' => 'menu-items/buang.jpg']);

    $this->put(route('admin.menu-items.update', $item), [
        'menu_category_id' => $item->menu_category_id,
        'name' => $item->name,
        'price' => $item->price,
        'is_available' => '1',
        'remove_image' => '1',
    ])->assertRedirect(route('admin.menu-items.index'));

    expect($item->refresh()->image_path)->toBeNull();
    Storage::disk('public')->assertMissing('menu-items/buang.jpg');
});

test('the availability button flips an item between Tersedia and Habis', function () {
    signInAsAdmin();
    $item = MenuItem::factory()->create(['name' => 'Kopi Susu', 'is_available' => true]);

    $this->from(route('admin.menu-items.index'))
        ->patch(route('admin.menu-items.availability', $item))
        ->assertRedirect(route('admin.menu-items.index'))
        ->assertSessionHas('status', 'Kopi Susu ditandai habis.');

    expect($item->refresh()->is_available)->toBeFalse();

    $this->patch(route('admin.menu-items.availability', $item))
        ->assertSessionHas('status', 'Kopi Susu ditandai tersedia.');

    expect($item->refresh()->is_available)->toBeTrue();
});

test('admin deletes a menu item and its photo', function () {
    Storage::fake('public');
    signInAsAdmin();
    Storage::disk('public')->put('menu-items/hapus.jpg', 'hapus');
    $item = MenuItem::factory()->create(['name' => 'Roti Bakar', 'image_path' => 'menu-items/hapus.jpg']);

    $this->delete(route('admin.menu-items.destroy', $item))
        ->assertRedirect(route('admin.menu-items.index'))
        ->assertSessionHas('status', 'Menu Roti Bakar dihapus.');

    expect(MenuItem::query()->whereKey($item->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing('menu-items/hapus.jpg');
});

test('a menu item used by a voucher cannot be deleted', function () {
    signInAsAdmin();
    $item = MenuItem::factory()->create(['name' => 'Kopi Susu']);
    Voucher::factory()->freeItem($item)->create();

    $response = $this->from(route('admin.menu-items.edit', $item))->delete(route('admin.menu-items.destroy', $item));

    $response->assertRedirect(route('admin.menu-items.edit', $item))
        ->assertSessionHasErrors(['menu_item' => 'Menu Kopi Susu dipakai oleh voucer. Ubah voucernya dulu, atau tandai menu ini Habis.']);
    expect(MenuItem::query()->whereKey($item->id)->exists())->toBeTrue();
});
