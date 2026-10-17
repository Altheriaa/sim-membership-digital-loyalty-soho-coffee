@use('App\Support\Rupiah')

@php
    $canManage = auth()->user()->hasRole(\App\Role::Admin);

    $rows = array_values(array_filter([
        ['label' => 'Layar Kasir (POS)', 'detail' => 'Antarmuka kasir untuk pesanan, pembayaran, dan pencatatan poin member', 'route' => 'pos.index', 'action' => 'Buka kasir', 'allowed' => $canManage],
        ['label' => 'Laporan Penjualan', 'detail' => "Hari ini: {$todayOrders} transaksi (" . Rupiah::format($todaySales) . ')', 'route' => 'admin.reports.index', 'action' => 'Buka laporan', 'allowed' => true],
        ['label' => 'Member', 'detail' => "{$totalMembers} member terdaftar", 'route' => 'admin.members.index', 'action' => 'Kelola member', 'allowed' => $canManage],
        ['label' => 'Voucer', 'detail' => $openVouchers . ' voucer bisa diklaim hari ini', 'route' => 'admin.vouchers.index', 'action' => 'Kelola voucer', 'allowed' => $canManage],
        ['label' => 'Tier', 'detail' => "{$totalTiers} tingkat keanggotaan", 'route' => 'admin.tiers.index', 'action' => 'Kelola tier', 'allowed' => $canManage],
        ['label' => 'Aturan poin', 'detail' => Rupiah::format($settings->spend_per_point) . ' = 1 poin, 1 poin = ' . Rupiah::format($settings->point_value) . ', pajak ' . rtrim(rtrim($settings->tax_percent, '0'), '.') . '%', 'route' => 'admin.loyalty.edit', 'action' => 'Ubah aturan', 'allowed' => $canManage],
        ['label' => 'Staf', 'detail' => "{$activeStaff} akun aktif", 'route' => 'admin.staff.index', 'action' => 'Kelola staf', 'allowed' => $canManage],
    ], fn($r) => $r['allowed']));
@endphp

<x-layouts.admin title="Dashboard">
    @if ($canManage)
        <x-slot:actions>
            <april:button-link href="{{ route('pos.index') }}" variant="outline" size="sm" class="h-10 px-3 text-xs gap-2">
                <x-lucide-monitor class="size-4 shrink-0" />
                <span>Buka Layar Kasir</span>
            </april:button-link>
        </x-slot:actions>
    @endif

    {{-- Executive Summary KPI Cards --}}
    <section aria-label="Ringkasan Performa Utama" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Card 1: Pendapatan --}}
        <div
            class="flex flex-col justify-between rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-muted-foreground">Pendapatan</p>
                    <p
                        class="mt-2 font-serif text-2xl font-semibold tracking-tight tabular-nums sm:text-3xl text-card-foreground">
                        {{ $metrics['revenue']['value'] }}
                    </p>
                </div>
                <div
                    class="flex size-9 shrink-0 items-center justify-center rounded-md border border-border bg-background text-muted-foreground">
                    <x-lucide-bar-chart-2 class="size-4" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-border/60 pt-2.5 text-xs">
                <span class="text-muted-foreground">Hari ini:</span>
                <span class="font-semibold text-foreground tabular-nums">{{ $metrics['revenue']['today'] }}</span>
            </div>
        </div>

        {{-- Card 2: Total transaksi --}}
        <div
            class="flex flex-col justify-between rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-muted-foreground">Total transaksi</p>
                    <p
                        class="mt-2 font-serif text-2xl font-semibold tracking-tight tabular-nums sm:text-3xl text-card-foreground">
                        {{ $metrics['orders']['value'] }}
                    </p>
                </div>
                <div
                    class="flex size-9 shrink-0 items-center justify-center rounded-md border border-border bg-background text-muted-foreground">
                    <x-lucide-receipt class="size-4" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-border/60 pt-2.5 text-xs">
                <span class="text-muted-foreground">Hari ini:</span>
                <span class="font-semibold text-foreground tabular-nums">{{ $metrics['orders']['today'] }}</span>
            </div>
        </div>

        {{-- Card 3: Member baru --}}
        <div
            class="flex flex-col justify-between rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-muted-foreground">Total Member</p>
                    <p
                        class="mt-2 font-serif text-2xl font-semibold tracking-tight tabular-nums sm:text-3xl text-card-foreground">
                        {{ $metrics['new_members']['value'] }}
                    </p>
                </div>
                <div
                    class="flex size-9 shrink-0 items-center justify-center rounded-md border border-border bg-background text-muted-foreground">
                    <x-lucide-users class="size-4" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-border/60 pt-2.5 text-xs">
                <span class="text-muted-foreground">Hari ini:</span>
                <span class="font-semibold text-foreground tabular-nums">{{ $metrics['new_members']['today'] }}</span>
            </div>
        </div>

        {{-- Card 4: Retensi kunjungan --}}
        <div
            class="flex flex-col justify-between rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-xs font-medium text-muted-foreground">Retensi kunjungan</p>
                    <p
                        class="mt-2 font-serif text-2xl font-semibold tracking-tight tabular-nums sm:text-3xl text-card-foreground">
                        {{ $metrics['retention']['value'] }}
                    </p>
                </div>
                <div
                    class="flex size-9 shrink-0 items-center justify-center rounded-md border border-border bg-background text-muted-foreground">
                    <x-lucide-star class="size-4" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between border-t border-border/60 pt-2.5 text-xs">
                <span class="text-muted-foreground">Pelanggan Setia:</span>
                <span class="font-semibold text-foreground tabular-nums">{{ $metrics['retention']['detail'] }}</span>
            </div>
        </div>
    </section>

    {{-- Visual Analytics: Tren Pendapatan & Sebaran Tier --}}
    <section aria-label="Grafik Analitik" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left: Tren Pendapatan --}}
        <div x-data="{ activePoint: null }"
            class="flex flex-col justify-between rounded-lg border border-border bg-card p-4 sm:p-6 text-card-foreground lg:col-span-2">
            <div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border pb-3 mb-3">
                    <div>
                        <h2 class="font-serif text-lg font-medium text-card-foreground">Tren Pendapatan</h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">Performa omzet bulan berjalan dibanding bulan
                            lalu</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5 font-medium text-card-foreground">
                            <span class="size-2 rounded-full bg-primary"></span>
                            <span>Bulan ini</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-muted-foreground">
                            <span class="size-2 rounded-full bg-border"></span>
                            <span>Bulan lalu</span>
                        </div>
                    </div>
                </div>

                {{-- Interactive Tooltip Box --}}
                <div class="h-6 flex items-center">
                    <template x-if="activePoint">
                        <div
                            class="inline-flex items-center gap-1.5 rounded-md bg-secondary/80 px-2 py-0.5 text-xs text-secondary-foreground border border-border/60">
                            <span class="font-medium" x-text="activePoint.date"></span>
                            <span>:</span>
                            <span class="font-semibold text-primary" x-text="activePoint.amount"></span>
                        </div>
                    </template>
                    <template x-if="!activePoint">
                        <span class="text-xs text-muted-foreground">Arahkan kursor ke titik tanggal untuk melihat detail
                            omzet harian</span>
                    </template>
                </div>

                {{-- Responsive Smooth Chart SVG --}}
                <div class="relative mt-2 w-full overflow-hidden">
                    <svg viewBox="0 0 640 260" class="h-auto w-full overflow-visible" role="img"
                        aria-label="Grafik garis tren pendapatan bulan ini dibandingkan bulan lalu">
                        <defs>
                            <linearGradient id="sohoRevenueGradient" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.22" />
                                <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>

                        {{-- Horizontal Grid Lines & Y Axis Labels --}}
                        <g class="text-[11px] font-medium fill-muted-foreground">
                            @foreach ($chartData['yAxisLabels'] as $yGrid)
                                <text x="10" y="{{ $yGrid['y'] + 4 }}"
                                    class="fill-muted-foreground">{{ $yGrid['label'] }}</text>
                                <line x1="45" y1="{{ $yGrid['y'] }}" x2="630" y2="{{ $yGrid['y'] }}" stroke="currentColor"
                                    stroke-opacity="0.1" stroke-width="1" />
                            @endforeach
                        </g>

                        {{-- Area fill for current month curve --}}
                        @if ($chartData['areaThisMonth'])
                            <path d="{{ $chartData['areaThisMonth'] }}" fill="url(#sohoRevenueGradient)" />
                        @endif

                        {{-- Previous Month Comparison (Dashed Curve) --}}
                        @if ($chartData['pathLastMonth'])
                            <path d="{{ $chartData['pathLastMonth'] }}" fill="none" stroke="currentColor"
                                stroke-opacity="0.3" stroke-width="2" stroke-dasharray="4 4" stroke-linecap="round" />
                        @endif

                        {{-- Current Month (Solid Primary Brand Amber Curve) --}}
                        @if ($chartData['pathThisMonth'])
                            <path d="{{ $chartData['pathThisMonth'] }}" fill="none" class="stroke-primary"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        @endif

                        {{-- Interactive Data Point Markers on Current Month Curve --}}
                        @foreach ($chartData['points'] as $pt)
                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}"
                                r="{{ $pt['day'] === ($chartData['lastPoint']['day'] ?? null) ? 4.5 : ($pt['amount'] > 0 ? 3.5 : 2) }}"
                                class="{{ $pt['amount'] > 0 ? 'fill-card stroke-primary' : 'fill-border stroke-border' }}"
                                stroke-width="2" />

                            {{-- Hover Target --}}
                            <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="10"
                                class="fill-transparent cursor-pointer hover:stroke-primary hover:stroke-2"
                                @mouseenter="activePoint = { date: '{{ $pt['date'] }}', amount: '{{ $pt['amountFormatted'] }}' }"
                                @mouseleave="activePoint = null" />
                        @endforeach

                        {{-- X Axis Date Labels --}}
                        <g class="text-[11px] font-medium fill-muted-foreground">
                            @foreach ($chartData['xAxisDates'] as $xDate)
                                <text x="{{ $xDate['x'] }}" y="235" text-anchor="{{ $xDate['anchor'] }}"
                                    class="fill-muted-foreground">{{ $xDate['label'] }}</text>
                            @endforeach
                        </g>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Right: Sebaran Tier --}}
        <div
            class="flex flex-col justify-between rounded-lg border border-border bg-card p-4 sm:p-6 text-card-foreground lg:col-span-1">
            <div>
                <div class="flex items-center justify-between border-b border-border pb-3 mb-4">
                    <div>
                        <h2 class="font-serif text-lg font-medium text-card-foreground">Sebaran Tier</h2>
                        <p class="mt-0.5 text-xs text-muted-foreground">Komposisi level member Soho Coffee</p>
                    </div>
                    <april:button-link href="{{ route('admin.tiers.index') }}" variant="outline" size="none"
                        class="h-8 px-2.5 text-xs">
                        Detail
                    </april:button-link>
                </div>

                {{-- Donut Chart with Center Cutout --}}
                <div class="relative mx-auto mt-4 flex size-44 items-center justify-center">
                    <svg viewBox="0 0 160 160" class="size-full -rotate-90" role="img"
                        aria-label="Diagram lingkaran sebaran tier member Soho Coffee">
                        {{-- Background Track --}}
                        <circle cx="80" cy="80" r="54" fill="none" stroke="currentColor" stroke-opacity="0.1"
                            stroke-width="24" />

                        @if ($totalMembers > 0)
                            {{-- Silver Arc (Base Tier) --}}
                            @if (($tierBreakdown['Silver']['percentage'] ?? 0) > 0)
                                <circle cx="80" cy="80" r="54" fill="none" class="stroke-slate-400 dark:stroke-stone-500"
                                    stroke-width="24" stroke-dasharray="{{ $tierBreakdown['Silver']['dasharray'] }}"
                                    stroke-dashoffset="{{ $tierBreakdown['Silver']['dashoffset'] }}" />
                            @endif

                            {{-- Gold Arc (Mid Tier) --}}
                            @if (($tierBreakdown['Gold']['percentage'] ?? 0) > 0)
                                <circle cx="80" cy="80" r="54" fill="none" class="stroke-primary" stroke-width="24"
                                    stroke-dasharray="{{ $tierBreakdown['Gold']['dasharray'] }}"
                                    stroke-dashoffset="{{ $tierBreakdown['Gold']['dashoffset'] }}" />
                            @endif

                            {{-- Platinum Arc (Top Tier) --}}
                            @if (($tierBreakdown['Platinum']['percentage'] ?? 0) > 0)
                                <circle cx="80" cy="80" r="54" fill="none" stroke="#1c325c" class="dark:stroke-sky-400"
                                    stroke-width="24" stroke-dasharray="{{ $tierBreakdown['Platinum']['dasharray'] }}"
                                    stroke-dashoffset="{{ $tierBreakdown['Platinum']['dashoffset'] }}" />
                            @endif
                        @endif
                    </svg>

                    {{-- Centered Text inside Donut Hole --}}
                    <div
                        class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span
                            class="font-serif text-3xl font-semibold tracking-tight text-card-foreground tabular-nums">
                            {{ $displayTotalMembers }}
                        </span>
                        <span class="text-xs font-medium text-muted-foreground">member</span>
                    </div>
                </div>

                {{-- Tier Legend List --}}
                <div class="mt-4 flex flex-col divide-y divide-border/60 border-t border-border/60 text-xs sm:text-sm">
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center gap-2">
                            <span class="size-2 shrink-0 rounded-full bg-[#1c325c] dark:bg-sky-400"></span>
                            <span class="font-medium text-card-foreground">Platinum</span>
                        </div>
                        <span
                            class="font-medium tabular-nums text-card-foreground">{{ $tierBreakdown['Platinum']['percentage'] ?? 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center gap-2">
                            <span class="size-2 shrink-0 rounded-full bg-primary"></span>
                            <span class="font-medium text-card-foreground">Gold</span>
                        </div>
                        <span
                            class="font-medium tabular-nums text-card-foreground">{{ $tierBreakdown['Gold']['percentage'] ?? 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center gap-2">
                            <span class="size-2 shrink-0 rounded-full bg-slate-400 dark:bg-stone-500"></span>
                            <span class="font-medium text-card-foreground">Silver</span>
                        </div>
                        <span
                            class="font-medium tabular-nums text-card-foreground">{{ $tierBreakdown['Silver']['percentage'] ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Menu Availability Overview --}}
    {{-- <section aria-labelledby="ready-heading" class="flex flex-col gap-1 border-t border-border pt-6 sm:pt-8">
        <h2 id="ready-heading" class="text-sm text-muted-foreground">Menu yang bisa dijual sekarang</h2>
        <p class="font-serif text-6xl font-medium tabular-nums tracking-tight sm:text-7xl">{{ $availableItems }}</p>
        @if ($availableItems === 0)
        <p class="text-sm text-muted-foreground">
            Belum ada menu yang tersedia.
            @if ($canManage)
            <a href="{{ route('admin.menu-items.create') }}" class="text-primary underline underline-offset-4">Tambah
                menu pertama</a>.
            @endif
        </p>
        @endif
    </section> --}}

    {{-- Fitur & Data Master --}}
    <section aria-labelledby="setup-heading" class="rounded-lg border border-border bg-card text-card-foreground">
        <h2 id="setup-heading" class="border-b border-border px-4 py-4 font-serif text-xl font-medium sm:px-6">Fitur &
            Data Master</h2>
        <ul class="divide-y divide-border">
            @foreach ($rows as $row)
                <li class="flex flex-wrap items-center justify-between gap-x-4 gap-y-3 px-4 py-4 sm:px-6">
                    <div class="min-w-0">
                        <h3 class="font-serif text-lg">{{ $row['label'] }}</h3>
                        <p class="text-sm text-muted-foreground">{{ $row['detail'] }}</p>
                    </div>
                    @if ($canManage || $row['route'] === 'admin.reports.index')
                        <april:button-link href="{{ route($row['route']) }}" variant="outline" size="sm"
                            class="h-11 shrink-0 sm:h-9">{{ $row['action'] }}</april:button-link>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Laporan Penjualan & Pembukuan --}}
    <section aria-labelledby="analytics-heading"
        class="rounded-lg border border-border bg-card p-4 sm:p-6 text-card-foreground flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 id="analytics-heading" class="font-serif text-lg font-medium">Laporan Penjualan & Pembukuan Cafe</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Pantau rekapitulasi omzet per rentang waktu, menu best seller, performa kasir, dan audit perolehan poin.
            </p>
        </div>
        <april:button-link href="{{ route('admin.reports.index') }}" size="none"
            class="h-10 px-4 text-xs font-medium shrink-0">
            Lihat laporan lengkap
        </april:button-link>
    </section>
</x-layouts.admin>