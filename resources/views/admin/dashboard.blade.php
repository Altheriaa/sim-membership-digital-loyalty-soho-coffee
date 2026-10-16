@use('App\Support\Rupiah')

@php
    $canManage = auth()->user()->hasRole(\App\Role::Admin);

    $rows = [
        ['label' => 'Menu', 'detail' => "{$availableItems} tersedia dari {$totalItems} menu, di {$totalCategories} kategori", 'route' => 'admin.menu-items.index', 'action' => 'Kelola menu'],
        ['label' => 'Voucer', 'detail' => $openVouchers.' voucer bisa diklaim hari ini', 'route' => 'admin.vouchers.index', 'action' => 'Kelola voucer'],
        ['label' => 'Tier', 'detail' => "{$totalTiers} tingkat keanggotaan", 'route' => 'admin.tiers.index', 'action' => 'Kelola tier'],
        ['label' => 'Aturan poin', 'detail' => Rupiah::format($settings->spend_per_point).' = 1 poin, 1 poin = '.Rupiah::format($settings->point_value).', pajak '.rtrim(rtrim($settings->tax_percent, '0'), '.').'%', 'route' => 'admin.loyalty.edit', 'action' => 'Ubah aturan'],
        ['label' => 'Staf', 'detail' => "{$activeStaff} akun aktif", 'route' => 'admin.staff.index', 'action' => 'Kelola staf'],
    ];
@endphp

<x-layouts.admin title="Dashboard">
    <section aria-labelledby="ready-heading" class="flex flex-col gap-1 border-b border-border pb-6 sm:pb-8">
        <h2 id="ready-heading" class="text-sm text-muted-foreground">Menu yang bisa dijual sekarang</h2>
        <p class="font-serif text-6xl font-medium tabular-nums tracking-tight sm:text-7xl">{{ $availableItems }}</p>
        @if ($availableItems === 0)
            <p class="text-sm text-muted-foreground">
                Belum ada menu yang tersedia.
                @if ($canManage)
                    <a href="{{ route('admin.menu-items.create') }}" class="text-primary underline underline-offset-4">Tambah menu pertama</a>.
                @endif
            </p>
        @endif
    </section>

    <section aria-labelledby="setup-heading" class="rounded-lg border border-border bg-card text-card-foreground">
        <h2 id="setup-heading" class="border-b border-border px-4 py-4 font-serif text-xl font-medium sm:px-6">Data master</h2>
        <ul class="divide-y divide-border">
            @foreach ($rows as $row)
                <li class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 px-4 py-4 sm:px-6">
                    <div class="min-w-0">
                        <h3 class="font-serif text-lg">{{ $row['label'] }}</h3>
                        <p class="text-sm text-muted-foreground">{{ $row['detail'] }}</p>
                    </div>
                    @if ($canManage)
                        <april:button-link href="{{ route($row['route']) }}" variant="outline" size="sm" class="h-11 shrink-0 sm:h-9">{{ $row['action'] }}</april:button-link>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    <section aria-labelledby="analytics-heading" class="rounded-lg border border-dashed border-border px-4 py-6 sm:px-6">
        <h2 id="analytics-heading" class="font-serif text-lg font-medium">Analitik penjualan belum tersedia</h2>
        <p class="mt-1 text-sm text-muted-foreground">
            Grafik pendapatan, jumlah transaksi, member baru, dan retensi muncul setelah kasir (Tahap 2) dan aplikasi pelanggan (Tahap 3) berjalan. Dashboard ini tidak menampilkan angka contoh.
        </p>
    </section>
</x-layouts.admin>
