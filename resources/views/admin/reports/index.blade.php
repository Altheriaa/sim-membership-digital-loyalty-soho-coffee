@use('App\Support\Rupiah')

<x-layouts.admin title="Laporan Penjualan & Pembukuan">
    <x-slot:actions>
        <a
            href="{{ route('admin.reports.print', request()->query()) }}"
            target="_blank"
            class="inline-flex h-10 items-center justify-center gap-2 rounded-md border border-border bg-card px-3.5 text-xs font-medium text-foreground hover:bg-accent transition-colors"
            title="Buka halaman cetak laporan resmi"
        >
            <x-lucide-printer class="size-3.5 text-muted-foreground" />
            <span>Cetak laporan</span>
        </a>
    </x-slot:actions>

    <!-- Bar Filter Rentang Waktu -->
    <div class="no-print rounded-lg border border-border bg-card p-4 text-card-foreground flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <!-- Preset Cepat -->
        <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="Pilihan rentang waktu">
            @php
                $presets = [
                    'today' => 'Hari ini',
                    'yesterday' => 'Kemarin',
                    '7_days' => '7 hari',
                    '30_days' => '30 hari',
                    'this_month' => 'Bulan ini',
                ];
            @endphp
            @foreach ($presets as $key => $title)
                <a
                    href="{{ route('admin.reports.index', ['period' => $key]) }}"
                    class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors {{ $period === $key ? 'bg-primary text-primary-foreground shadow-xs' : 'border border-border bg-background text-muted-foreground hover:bg-accent hover:text-foreground' }}"
                >
                    {{ $title }}
                </a>
            @endforeach
        </div>

        <!-- Filter Tanggal Kustom -->
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="period" value="custom">
            <div class="flex items-center gap-1.5">
                <label for="start_date" class="sr-only">Tanggal Mulai</label>
                <input
                    id="start_date"
                    type="date"
                    name="start_date"
                    value="{{ $startDate }}"
                    class="h-9 rounded-md border border-input bg-background px-2.5 text-xs text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                >
                <span class="text-xs text-muted-foreground">s/d</span>
                <label for="end_date" class="sr-only">Tanggal Akhir</label>
                <input
                    id="end_date"
                    type="date"
                    name="end_date"
                    value="{{ $endDate }}"
                    class="h-9 rounded-md border border-input bg-background px-2.5 text-xs text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                >
            </div>
            <april:button type="submit" variant="outline" size="none" class="h-9 px-3 text-xs">Filter</april:button>
        </form>
    </div>

    <!-- Judul Rentang Aktif (Tampak saat cetak) -->
    <div class="flex items-baseline justify-between border-b border-border pb-2">
        <h2 class="font-serif text-lg font-medium">Periode: {{ $periodLabel }}</h2>
        <span class="text-xs text-muted-foreground tabular-nums">{{ Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
    </div>

    <!-- Metrik Finansial & Penjualan -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <!-- Total Omzet Penjualan -->
        <div class="rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <p class="text-xs font-medium text-muted-foreground">Total Omzet Penjualan + Tax</p>
            <p class="mt-2 font-serif text-2xl sm:text-3xl font-semibold tabular-nums tracking-tight text-primary">{{ Rupiah::format($grossSales) }}</p>
            <p class="mt-1 text-xs text-muted-foreground tabular-nums">
                Subtotal menu: {{ Rupiah::format($subtotalSales) }}
                @if ($totalDiscount > 0)
                    · Diskon: -{{ Rupiah::format($totalDiscount) }}
                @endif
            </p>
        </div>

        <!-- Jumlah Transaksi -->
        <div class="rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <p class="text-xs font-medium text-muted-foreground">Jumlah Transaksi</p>
            <p class="mt-2 font-serif text-2xl sm:text-3xl font-semibold tabular-nums tracking-tight">{{ number_format($totalOrders) }}</p>
            <p class="mt-1 text-xs text-muted-foreground tabular-nums">Rata-rata: {{ Rupiah::format($averageOrderValue) }} / struk</p>
        </div>

        <!-- Rincian Metode Pembayaran -->
        <div class="rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <p class="text-xs font-medium text-muted-foreground">Arus Kas & Pembayaran</p>
            <div class="mt-2 flex flex-col gap-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Tunai:</span>
                    <span class="font-medium tabular-nums text-foreground">{{ Rupiah::format($cashSales) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Non-tunai / QRIS:</span>
                    <span class="font-medium tabular-nums text-foreground">{{ Rupiah::format($nonCashSales) }}</span>
                </div>
            </div>
        </div>

        <!-- Pajak & Poin Loyalty -->
        <div class="rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <p class="text-xs font-medium text-muted-foreground">Pajak & Loyalty</p>
            <div class="mt-2 flex flex-col gap-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Pajak Terkumpul:</span>
                    <span class="font-medium tabular-nums text-foreground">{{ Rupiah::format($totalTax) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Poin Diterbitkan:</span>
                    <span class="font-medium tabular-nums text-primary">+{{ number_format($totalPointsEarned) }} poin</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Dua Kolom: Menu Best Seller & Performa Kasir -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Menu Paling Laris (Best Seller) -->
        <section aria-labelledby="bestseller-heading" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
            <div class="border-b border-border p-4">
                <h3 id="bestseller-heading" class="font-serif text-base font-medium">Menu Terlaris (Best Seller)</h3>
            </div>

            @if ($bestSellers->isEmpty())
                <div class="p-8 text-center text-xs text-muted-foreground">
                    Belum ada menu yang terjual pada rentang waktu ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-border text-left text-muted-foreground">
                                <th scope="col" class="px-4 py-3 font-medium">#</th>
                                <th scope="col" class="px-4 py-3 font-medium">Nama Menu</th>
                                <th scope="col" class="px-4 py-3 font-medium text-center">Terjual</th>
                                <th scope="col" class="px-4 py-3 font-medium text-right">Total Omzet</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($bestSellers as $index => $item)
                                <tr class="hover:bg-accent/40">
                                    <td class="px-4 py-3 font-mono font-medium text-muted-foreground">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-medium text-foreground">{{ $item->name }}</td>
                                    <td class="px-4 py-3 text-center tabular-nums font-semibold text-primary">{{ number_format($item->total_qty) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-medium text-foreground">{{ Rupiah::format($item->total_sales) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- Laporan Performa Kasir -->
        <section aria-labelledby="cashier-heading" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
            <div class="border-b border-border p-4">
                <h3 id="cashier-heading" class="font-serif text-base font-medium">Performa Transaksi Kasir</h3>
            </div>

            @if ($cashierStats->isEmpty())
                <div class="p-8 text-center text-xs text-muted-foreground">
                    Belum ada data kasir pada rentang waktu ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-border text-left text-muted-foreground">
                                <th scope="col" class="px-4 py-3 font-medium">Nama Kasir</th>
                                <th scope="col" class="px-4 py-3 font-medium text-center">Total Transaksi</th>
                                <th scope="col" class="px-4 py-3 font-medium text-right">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($cashierStats as $stat)
                                <tr class="hover:bg-accent/40">
                                    <td class="px-4 py-3 font-medium text-foreground">{{ $stat->cashier_name }}</td>
                                    <td class="px-4 py-3 text-center tabular-nums font-medium text-foreground">{{ number_format($stat->total_transactions) }} pesanan</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-semibold text-foreground">{{ Rupiah::format($stat->total_sales) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <!-- Riwayat Transaksi Penjualan -->
    <section aria-labelledby="transactions-heading" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        <div class="border-b border-border p-4 flex items-center justify-between">
            <h3 id="transactions-heading" class="font-serif text-base font-medium">Transaksi Penjualan</h3>
            <span class="text-xs text-muted-foreground tabular-nums">{{ number_format($totalOrders) }} transaksi</span>
        </div>

        @if ($orders->isEmpty())
            <div class="p-8 text-center text-xs text-muted-foreground">
                Belum ada transaksi penjualan pada rentang waktu ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-border text-left text-muted-foreground">
                            <th scope="col" class="px-4 py-3 font-medium">No. Struk & Waktu</th>
                            <th scope="col" class="px-4 py-3 font-medium">Kasir</th>
                            <th scope="col" class="px-4 py-3 font-medium">Pelanggan</th>
                            <th scope="col" class="px-4 py-3 font-medium">Metode</th>
                            <th scope="col" class="px-4 py-3 font-medium">Menu</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Total</th>
                            <th scope="col" class="no-print px-4 py-3 text-right"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-accent/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a
                                        href="{{ route('pos.orders.show', ['order' => $order, 'from' => 'reports']) }}"
                                        target="_blank"
                                        class="font-mono font-medium text-foreground hover:underline"
                                        title="Buka struk {{ $order->number }}"
                                    >
                                        {{ $order->number }}
                                    </a>
                                    <span class="block text-[11px] text-muted-foreground tabular-nums">
                                        {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-foreground">
                                    {{ $order->cashier->name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($order->member)
                                        <div class="flex flex-col">
                                            <span class="font-medium text-foreground">{{ $order->member->name }}</span>
                                            <span class="text-[11px] text-muted-foreground tabular-nums">{{ $order->member->displayPhone() }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted-foreground">Umum</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                    {{ $order->payment_method->label() }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground max-w-xs truncate" title="{{ $order->items->map(fn ($i) => "{$i->quantity}x {$i->name}")->join(', ') }}">
                                    {{ $order->items->take(2)->map(fn ($i) => "{$i->quantity}x {$i->name}")->join(', ') }}
                                    @if ($order->items->count() > 2)
                                        <span class="text-muted-foreground/60">(+{{ $order->items->count() - 2 }} lainnya)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-semibold text-foreground whitespace-nowrap">
                                    {{ Rupiah::format($order->total) }}
                                </td>
                                <td class="no-print px-4 py-3 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('pos.orders.show', ['order' => $order, 'from' => 'reports']) }}"
                                        target="_blank"
                                        class="inline-flex h-8 items-center justify-center rounded-md border border-input bg-background px-3 text-xs font-medium text-foreground hover:bg-accent hover:text-accent-foreground transition-colors"
                                    >
                                        Cetak struk
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$orders" />
        @endif
    </section>

    <!-- Log Audit Mutasi Poin & Pembukuan Poin -->
    <section aria-labelledby="audit-heading" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        <div class="border-b border-border p-4 flex items-center justify-between">
            <h3 id="audit-heading" class="font-serif text-base font-medium">Log Audit Mutasi Poin Pelanggan</h3>
            <span class="text-xs text-muted-foreground">Rekap untuk penyesuaian pembukuan cafe</span>
        </div>

        @if ($pointTransactions->isEmpty())
            <div class="p-8 text-center text-xs text-muted-foreground">
                Tidak ada mutasi poin yang tercatat pada rentang waktu ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-border text-left text-muted-foreground">
                            <th scope="col" class="px-4 py-3 font-medium">Waktu</th>
                            <th scope="col" class="px-4 py-3 font-medium">Member</th>
                            <th scope="col" class="px-4 py-3 font-medium">Transaksi / Keterangan</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Poin</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($pointTransactions as $pt)
                            <tr class="hover:bg-accent/40">
                                <td class="px-4 py-3 text-muted-foreground">{{ $pt->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">
                                    @if ($pt->member)
                                        <a href="{{ route('admin.members.show', $pt->member) }}" class="hover:underline">
                                            {{ $pt->member->name }}
                                        </a>
                                        <span class="block text-[11px] text-muted-foreground tabular-nums">{{ $pt->member->displayPhone() }}</span>
                                    @else
                                        <span class="text-muted-foreground">Member dihapus</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-foreground">
                                    {{ $pt->description }}
                                    @if ($pt->order)
                                        <span class="block text-[11px] text-muted-foreground">Kasir: {{ $pt->order->cashier->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-semibold {{ $pt->points >= 0 ? 'text-primary' : 'text-destructive' }}">
                                    {{ $pt->points >= 0 ? '+' : '' }}{{ number_format($pt->points) }}
                                </td>
                                <td class="px-4 py-3 text-right tabular-nums font-medium text-muted-foreground">
                                    {{ number_format($pt->balance_after) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$pointTransactions" />
        @endif
    </section>
</x-layouts.admin>
