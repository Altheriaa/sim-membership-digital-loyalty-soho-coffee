@php
    $isEditing = $category->exists;
    $title = $isEditing ? 'Ubah kategori' : 'Tambah kategori';
@endphp

<x-layouts.admin :title="$title">
    <form method="POST" action="{{ $isEditing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="flex max-w-xl flex-col gap-5 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <x-field name="name" label="Nama kategori" :value="$category->name" maxlength="60" required />
        <x-field name="sort_order" label="Urutan tampil" type="number" :value="$category->sort_order" min="0" max="999" inputmode="numeric" hint="Angka kecil tampil lebih dulu di layar kasir." required />

        <div class="flex flex-wrap items-center gap-2">
            <april:button type="submit" size="none" class="h-11 px-4 sm:h-10">Simpan</april:button>
            <april:button-link href="{{ route('admin.categories.index') }}" variant="outline" size="none" class="h-11 px-4 sm:h-10">Batal</april:button-link>
        </div>
    </form>

    @if ($isEditing)
        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" x-data x-on:submit="if (! confirm('Hapus kategori {{ e($category->name) }}?')) $event.preventDefault()" class="flex max-w-xl flex-col gap-2 rounded-lg border border-destructive p-4 sm:p-6">
            @csrf
            @method('DELETE')
            <h2 class="font-serif text-lg font-medium text-destructive">Hapus kategori</h2>
            <p class="text-sm text-muted-foreground">Kategori yang masih berisi menu tidak bisa dihapus.</p>
            <april:button type="submit" variant="destructive" size="none" class="h-11 w-fit px-4 sm:h-10">Hapus kategori</april:button>
        </form>
    @endif
</x-layouts.admin>
