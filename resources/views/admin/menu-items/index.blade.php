@use('App\Support\Rupiah')

<x-layouts.admin title="Menu">
    <x-slot:actions>
        <april:button-link href="{{ route('admin.menu-items.create') }}" size="none" class="h-11 px-4 sm:h-10">Tambah menu</april:button-link>
    </x-slot:actions>

    <section aria-label="Daftar menu" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        <form method="GET" action="{{ route('admin.menu-items.index') }}" role="search" class="flex flex-col gap-2 border-b border-border p-3 sm:flex-row sm:items-center">
            <div class="relative min-w-0 flex-1">
                <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground" />
                <april:input type="search" name="search" value="{{ $search }}" maxlength="100" placeholder="Cari nama menu" aria-label="Cari menu" class="h-11 pl-9 sm:h-10" />
            </div>
            <april:native-select name="category" aria-label="Filter kategori" class="h-11 sm:h-10 sm:w-52">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                @endforeach
            </april:native-select>
            <april:button type="submit" variant="outline" size="none" class="h-11 shrink-0 px-4 sm:h-10">Terapkan</april:button>
        </form>

        @if ($items->isEmpty())
            <div class="px-4 py-12 text-center sm:px-6">
                @if ($totalItems === 0)
                    <p class="font-serif text-lg">Belum ada menu</p>
                    <p class="mx-auto mt-1 max-w-md text-sm text-muted-foreground">
                        @if ($categories->isEmpty())
                            Menu harus masuk ke sebuah kategori. Buat kategori dulu, lalu tambahkan menu pertama.
                        @else
                            Tambahkan menu pertama supaya kasir bisa mulai menjual.
                        @endif
                    </p>
                    <april:button-link href="{{ $categories->isEmpty() ? route('admin.categories.create') : route('admin.menu-items.create') }}" size="none" class="mt-4 h-11 px-4 sm:h-10">
                        {{ $categories->isEmpty() ? 'Tambah kategori' : 'Tambah menu pertama' }}
                    </april:button-link>
                @else
                    <p class="font-serif text-lg">
                        {{ $search ? "Tidak ada menu yang cocok dengan “{$search}”" : 'Tidak ada menu di kategori ini' }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">Ubah kata kunci atau pilih kategori lain.</p>
                    <april:button-link href="{{ route('admin.menu-items.index') }}" variant="outline" size="none" class="mt-4 h-11 px-4 sm:h-10">Hapus filter</april:button-link>
                @endif
            </div>
        @else
            <table class="w-full text-sm">
                <caption class="sr-only">Daftar menu, diurutkan menurut nama</caption>
                <thead>
                    <tr class="border-b border-border text-left text-muted-foreground">
                        <th scope="col" class="w-1/2 px-4 py-3 font-medium sm:px-6">Menu</th>
                        <th scope="col" class="hidden px-4 py-3 font-medium sm:table-cell sm:px-6">Harga</th>
                        <th scope="col" class="px-4 py-3 font-medium sm:px-6">Status</th>
                        <th scope="col" class="px-4 py-3 sm:px-6"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($items as $item)
                        <tr>
                            <td class="max-w-0 px-4 py-3 sm:px-6">
                                <div class="flex min-w-0 items-center gap-3">
                                    @if ($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="" width="40" height="40" loading="lazy" class="hidden size-10 shrink-0 rounded-md border border-border object-cover sm:block">
                                    @else
                                        <span class="hidden size-10 shrink-0 items-center justify-center rounded-md border border-border bg-muted text-muted-foreground sm:flex" aria-hidden="true"><x-icon name="coffee" /></span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ $item->name }}</p>
                                        <p class="truncate text-xs text-muted-foreground">
                                            {{ $item->category->name }}<span class="sm:hidden"> · {{ Rupiah::format($item->price) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-4 py-3 tabular-nums sm:table-cell sm:px-6">{{ Rupiah::format($item->price) }}</td>
                            <td class="px-4 py-3 sm:px-6">
                                <form method="POST" action="{{ route('admin.menu-items.availability', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Klik untuk menandai {{ $item->is_available ? 'habis' : 'tersedia' }}" class="inline-flex min-h-11 items-center rounded-md border border-input px-3 text-xs font-medium transition-colors hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring sm:min-h-8 {{ $item->is_available ? 'text-foreground' : 'bg-muted text-muted-foreground' }}">
                                        {{ $item->is_available ? 'Tersedia' : 'Habis' }}<span class="sr-only">. Tandai {{ $item->is_available ? 'habis' : 'tersedia' }}: {{ $item->name }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right sm:px-6">
                                <april:button-link href="{{ route('admin.menu-items.edit', $item) }}" variant="outline" size="none" class="h-11 px-3 sm:h-9">
                                    Ubah<span class="sr-only"> {{ $item->name }}</span>
                                </april:button-link>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <x-pagination :paginator="$items" />
        @endif
    </section>
</x-layouts.admin>
