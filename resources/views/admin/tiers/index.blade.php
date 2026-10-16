@use('App\Support\Rupiah')

<x-layouts.admin title="Tier">
    <x-slot:actions>
        <april:button-link href="{{ route('admin.tiers.create') }}" size="none" class="h-11 px-4 sm:h-10">Tambah tier</april:button-link>
    </x-slot:actions>

    <p class="max-w-2xl text-sm text-muted-foreground">
        Tier ditentukan dari total belanja member sejak bergabung. Pengali poin dikalikan ke poin yang didapat dari setiap transaksi.
    </p>

    <section aria-label="Daftar tier" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        @if ($tiers->isEmpty())
            <div class="px-4 py-12 text-center sm:px-6">
                <p class="font-serif text-lg">Belum ada tier</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-muted-foreground">Mulai dengan tier dasar yang syarat belanjanya Rp 0, lalu tambahkan tingkat di atasnya.</p>
                <april:button-link href="{{ route('admin.tiers.create') }}" size="none" class="mt-4 h-11 px-4 sm:h-10">Tambah tier dasar</april:button-link>
            </div>
        @else
            <table class="w-full text-sm">
                <caption class="sr-only">Daftar tier, diurutkan dari syarat belanja terendah</caption>
                <thead>
                    <tr class="border-b border-border text-left text-muted-foreground">
                        <th scope="col" class="px-4 py-3 font-medium sm:px-6">Tier</th>
                        <th scope="col" class="px-4 py-3 font-medium sm:px-6">Total belanja</th>
                        <th scope="col" class="px-4 py-3 font-medium sm:px-6">Pengali poin</th>
                        <th scope="col" class="px-4 py-3 sm:px-6"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($tiers as $tier)
                        <tr>
                            <td class="px-4 py-3 sm:px-6">
                                <span class="font-medium">{{ $tier->name }}</span>
                                @if ($tier->isBase())
                                    <span class="block text-xs text-muted-foreground">Tier dasar</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 tabular-nums sm:px-6">{{ $tier->isBase() ? 'Semua member' : 'Mulai '.Rupiah::format($tier->min_spend) }}</td>
                            <td class="px-4 py-3 tabular-nums sm:px-6">{{ $tier->multiplierLabel() }}×</td>
                            <td class="px-4 py-3 text-right sm:px-6">
                                <april:button-link href="{{ route('admin.tiers.edit', $tier) }}" variant="outline" size="none" class="h-11 px-3 sm:h-9">
                                    Ubah<span class="sr-only"> {{ $tier->name }}</span>
                                </april:button-link>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</x-layouts.admin>
