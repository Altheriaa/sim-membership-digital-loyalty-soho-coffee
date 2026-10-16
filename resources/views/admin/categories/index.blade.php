<x-layouts.admin title="Kategori">
    <x-slot:actions>
        <april:button-link href="{{ route('admin.categories.create') }}" size="none" class="h-11 px-4 sm:h-10">Tambah kategori</april:button-link>
    </x-slot:actions>

    <section aria-label="Daftar kategori" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        @if ($categories->isEmpty())
            <div class="px-4 py-12 text-center sm:px-6">
                <p class="font-serif text-lg">Belum ada kategori</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-muted-foreground">Kategori mengelompokkan menu di layar kasir, misalnya Kopi atau Makanan. Buat satu sebelum menambah menu.</p>
                <april:button-link href="{{ route('admin.categories.create') }}" size="none" class="mt-4 h-11 px-4 sm:h-10">Tambah kategori pertama</april:button-link>
            </div>
        @else
            <table class="w-full text-sm">
                <caption class="sr-only">Daftar kategori menu, diurutkan menurut urutan tampil</caption>
                <thead>
                    <tr class="border-b border-border text-left text-muted-foreground">
                        <th scope="col" class="px-4 py-3 font-medium sm:px-6">Nama</th>
                        <th scope="col" class="px-4 py-3 font-medium sm:px-6">Menu</th>
                        <th scope="col" class="hidden px-4 py-3 font-medium sm:table-cell sm:px-6">Urutan</th>
                        <th scope="col" class="px-4 py-3 sm:px-6"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($categories as $category)
                        <tr>
                            <td class="px-4 py-3 font-medium sm:px-6">{{ $category->name }}</td>
                            <td class="px-4 py-3 tabular-nums sm:px-6">{{ $category->items_count }}</td>
                            <td class="hidden px-4 py-3 tabular-nums text-muted-foreground sm:table-cell sm:px-6">{{ $category->sort_order }}</td>
                            <td class="px-4 py-3 text-right sm:px-6">
                                <april:button-link href="{{ route('admin.categories.edit', $category) }}" variant="outline" size="none" class="h-11 px-3 sm:h-9">
                                    Ubah<span class="sr-only"> {{ $category->name }}</span>
                                </april:button-link>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</x-layouts.admin>
