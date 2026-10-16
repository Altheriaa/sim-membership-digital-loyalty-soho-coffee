@php
    $isEditing = $item->exists;
    $title = $isEditing ? 'Ubah menu' : 'Tambah menu';
@endphp

<x-layouts.admin :title="$title">
    @if ($categories->isEmpty())
        <section class="flex max-w-xl flex-col gap-2 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6">
            <h2 class="font-serif text-lg font-medium">Buat kategori dulu</h2>
            <p class="text-sm text-muted-foreground">Setiap menu harus masuk ke sebuah kategori.</p>
            <april:button-link href="{{ route('admin.categories.create') }}" size="none" class="mt-2 h-11 w-fit px-4 sm:h-10">Tambah kategori</april:button-link>
        </section>
    @else
        <form method="POST" enctype="multipart/form-data" action="{{ $isEditing ? route('admin.menu-items.update', $item) : route('admin.menu-items.store') }}" class="flex max-w-xl flex-col gap-5 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <x-field name="name" label="Nama menu" :value="$item->name" maxlength="100" required />

            <x-field name="menu_category_id" label="Kategori" as="select" required>
                <option value="" @selected(old('menu_category_id', $item->menu_category_id) === null)>Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('menu_category_id', $item->menu_category_id) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-field>

            <x-field name="price" label="Harga jual (Rp)" type="number" :value="$item->price" min="0" max="100000000" step="500" inputmode="numeric" hint="Dalam rupiah penuh, tanpa titik. Contoh: 25000." required />

            <div class="flex flex-col gap-2">
                <x-field name="image" label="Foto menu" type="file" accept="image/jpeg,image/png,image/webp" optional hint="JPG, PNG, atau WebP. Maksimal 2 MB." />
                @if ($item->image_url)
                    <div class="flex items-center gap-3">
                        <img src="{{ $item->image_url }}" alt="Foto {{ $item->name }} saat ini" width="64" height="64" class="size-16 rounded-md border border-border object-cover">
                        <x-checkbox-field name="remove_image" label="Hapus foto saat ini" />
                    </div>
                @endif
            </div>

            <x-checkbox-field name="is_available" label="Tersedia untuk dijual" :checked="$item->is_available" hint="Hilangkan centang jika stok habis." />

            <div class="flex flex-wrap items-center gap-2">
                <april:button type="submit" size="none" class="h-11 px-4 sm:h-10">Simpan</april:button>
                <april:button-link href="{{ route('admin.menu-items.index') }}" variant="outline" size="none" class="h-11 px-4 sm:h-10">Batal</april:button-link>
            </div>
        </form>

        @if ($isEditing)
            <form method="POST" action="{{ route('admin.menu-items.destroy', $item) }}" x-data x-on:submit="if (! confirm('Hapus menu {{ e($item->name) }}?')) $event.preventDefault()" class="flex max-w-xl flex-col gap-2 rounded-lg border border-destructive p-4 sm:p-6">
                @csrf
                @method('DELETE')
                <h2 class="font-serif text-lg font-medium text-destructive">Hapus menu</h2>
                <p class="text-sm text-muted-foreground">Menu yang dipakai voucer tidak bisa dihapus. Tandai Habis jika hanya tidak dijual sementara.</p>
                <april:button type="submit" variant="destructive" size="none" class="h-11 w-fit px-4 sm:h-10">Hapus menu</april:button>
            </form>
        @endif
    @endif
</x-layouts.admin>
