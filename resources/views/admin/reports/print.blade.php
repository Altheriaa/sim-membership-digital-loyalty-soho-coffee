@use('App\Role')
@use('App\Support\Rupiah')
@use('Carbon\Carbon')

<x-layouts.guest :title="'Cetak Laporan - ' . $periodLabel" max-width="max-w-4xl" :centered="false">
    <div class="flex flex-col gap-4 py-2">
        <!-- Bilah Aksi di Atas (Tidak Dicetak) -->
        <div class="no-print flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border bg-card p-3 text-card-foreground shadow-xs">
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.reports.index', request()->query()) }}"
                    class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md border border-input bg-background px-3 text-xs font-medium text-foreground hover:bg-accent hover:text-accent-foreground transition-colors"
                >
                    <x-lucide-arrow-left class="size-3.5" />
                    <span>Kembali ke Laporan</span>
                </a>
                <span class="text-xs text-muted-foreground hidden sm:inline">|</span>
                <span class="text-xs font-medium text-muted-foreground hidden sm:inline">
                    Periode: <strong class="text-foreground">{{ $periodLabel }}</strong>
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md bg-primary px-3.5 text-xs font-medium text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring shadow-xs transition-colors"
                >
                    <x-lucide-printer class="size-3.5" />
                    <span>Cetak Dokumen</span>
                </button>
                <x-theme-toggle />
            </div>
        </div>

        <!-- Lembar Dokumen Fisik / Printable Paper -->
        <article class="report-paper rounded-lg border border-border bg-card p-6 sm:p-8 text-card-foreground shadow-xs text-xs">
            <!-- Kop Surat / Header Dokumen Ringkas & Resmi -->
            <header class="border-b-2 border-foreground pb-3 mb-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-md border border-foreground/30 bg-muted/60 font-serif font-bold text-base text-foreground">
                            SC
                        </div>
                        <div>
                            <h1 class="font-serif text-lg font-bold tracking-tight text-foreground uppercase leading-none">
                                Soho Caffe
                            </h1>
                            <p class="text-[11px] text-muted-foreground font-medium mt-0.5">Coffee & Eatery · Laporan Penjualan & Pembukuan</p>
                        </div>
                    </div>

                    <div class="text-right text-[10px] text-muted-foreground leading-tight space-y-0.5">
                        <p class="font-mono text-foreground font-medium">Ref: RPT-{{ now()->format('YmdHi') }}</p>
                        <p>Dicetak: <span class="text-foreground font-medium tabular-nums">{{ now()->translatedFormat('d M Y, H:i') }}</span></p>
                        <p>Oleh: <span class="text-foreground font-medium">{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</span></p>
                    </div>
                </div>

                <!-- Baris Informasi Periode & Total -->
                <div class="mt-2.5 pt-2 border-t border-border/80 flex items-center justify-between text-[11px]">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-foreground uppercase tracking-wide">Periode:</span>
                        <span class="text-foreground font-medium">{{ $periodLabel }}</span>
                        <span class="text-muted-foreground tabular-nums">({{ Carbon::parse($startDate)->format('d/m/Y') }} – {{ Carbon::parse($endDate)->format('d/m/Y') }})</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground">Volume Transaksi:</span>
                        <strong class="text-foreground tabular-nums ml-1">{{ number_format($totalOrders) }} Struk</strong>
                    </div>
                </div>
            </header>

            <!-- I. Ringkasan Finansial & Arus Kas (Padat & Rapi) -->
            <section aria-labelledby="summary-heading" class="mb-4 avoid-break">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <!-- Total Omzet -->
                    <div class="rounded border border-border bg-muted/20 p-2.5">
                        <span class="text-[10px] font-medium text-muted-foreground block">Total Omzet (Kotor)</span>
                        <span class="text-base font-serif font-bold text-foreground tabular-nums block mt-0.5 whitespace-nowrap">
                            {{ Rupiah::format($grossSales) }}
                        </span>
                        <span class="text-[10px] text-muted-foreground block mt-0.5 tabular-nums truncate">
                            Subtotal: {{ Rupiah::format($subtotalSales) }}
                        </span>
                    </div>

                    <!-- Transaksi & AOV -->
                    <div class="rounded border border-border bg-muted/20 p-2.5">
                        <span class="text-[10px] font-medium text-muted-foreground block">Jumlah Transaksi</span>
                        <span class="text-base font-serif font-bold text-foreground tabular-nums block mt-0.5 whitespace-nowrap">
                            {{ number_format($totalOrders) }} <span class="text-xs font-normal text-muted-foreground">struk</span>
                        </span>
                        <span class="text-[10px] text-muted-foreground block mt-0.5 tabular-nums truncate">
                            Rata-rata: {{ Rupiah::format($averageOrderValue) }}
                        </span>
                    </div>

                    <!-- Arus Kas Pembayaran -->
                    <div class="rounded border border-border bg-muted/20 p-2.5">
                        <span class="text-[10px] font-medium text-muted-foreground block">Arus Kas (Tunai vs Non-Tunai)</span>
                        <div class="mt-0.5 flex flex-col text-[11px] leading-tight tabular-nums">
                            <span class="text-foreground whitespace-nowrap">Tunai: <strong>{{ Rupiah::format($cashSales) }}</strong></span>
                            <span class="text-muted-foreground whitespace-nowrap mt-0.5">Non-tunai: {{ Rupiah::format($nonCashSales) }}</span>
                        </div>
                    </div>

                    <!-- Diskon & Pajak -->
                    <div class="rounded border border-border bg-muted/20 p-2.5">
                        <span class="text-[10px] font-medium text-muted-foreground block">Potongan & Poin</span>
                        <div class="mt-0.5 flex flex-col text-[11px] leading-tight tabular-nums">
                            <span class="text-foreground whitespace-nowrap">
                                Diskon: {{ $totalDiscount > 0 ? '-' . Rupiah::format($totalDiscount) : 'Rp 0' }}
                            </span>
                            <span class="text-muted-foreground whitespace-nowrap mt-0.5">
                                Poin terbit: +{{ number_format($totalPointsEarned) }} poin
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- II & III. Menu Terlaris & Performa Kasir (Berdampingan, Ringkas) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4 avoid-break">
                <!-- Menu Terlaris -->
                <section aria-labelledby="best-seller-heading" class="rounded border border-border p-2.5">
                    <h3 id="best-seller-heading" class="font-serif text-[11px] font-bold uppercase tracking-wider text-foreground mb-1.5 pb-1 border-b border-border flex justify-between">
                        <span>Menu Terlaris (Top 5)</span>
                        <span class="text-[10px] text-muted-foreground lowercase font-sans font-normal">berdasarkan qty</span>
                    </h3>

                    @if ($bestSellers->isEmpty())
                        <p class="py-2 text-center text-[10px] text-muted-foreground">Belum ada penjualan menu pada periode ini.</p>
                    @else
                        <table class="w-full text-left text-[11px]">
                            <thead>
                                <tr class="border-b border-border text-muted-foreground text-[10px]">
                                    <th scope="col" class="py-0.5 px-1 font-medium w-5">#</th>
                                    <th scope="col" class="py-0.5 px-1 font-medium">Nama Menu</th>
                                    <th scope="col" class="py-0.5 px-1 font-medium text-center w-12">Qty</th>
                                    <th scope="col" class="py-0.5 px-1 font-medium text-right w-24">Omzet</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                @foreach ($bestSellers->take(5) as $index => $item)
                                    <tr>
                                        <td class="py-1 px-1 text-muted-foreground font-mono text-[10px]">{{ $index + 1 }}</td>
                                        <td class="py-1 px-1 font-medium text-foreground truncate max-w-[140px]">{{ $item->name }}</td>
                                        <td class="py-1 px-1 text-center font-medium tabular-nums">{{ number_format($item->total_qty) }}</td>
                                        <td class="py-1 px-1 text-right tabular-nums text-foreground whitespace-nowrap">{{ Rupiah::format($item->total_sales) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>

                <!-- Performa Kasir -->
                <section aria-labelledby="cashier-stat-heading" class="rounded border border-border p-2.5">
                    <h3 id="cashier-stat-heading" class="font-serif text-[11px] font-bold uppercase tracking-wider text-foreground mb-1.5 pb-1 border-b border-border flex justify-between">
                        <span>Performa Transaksi Kasir</span>
                        <span class="text-[10px] text-muted-foreground lowercase font-sans font-normal">rekap kasir</span>
                    </h3>

                    @if ($cashierStats->isEmpty())
                        <p class="py-2 text-center text-[10px] text-muted-foreground">Belum ada transaksi kasir pada periode ini.</p>
                    @else
                        <table class="w-full text-left text-[11px]">
                            <thead>
                                <tr class="border-b border-border text-muted-foreground text-[10px]">
                                    <th scope="col" class="py-0.5 px-1 font-medium">Nama Kasir</th>
                                    <th scope="col" class="py-0.5 px-1 font-medium text-center w-16">Transaksi</th>
                                    <th scope="col" class="py-0.5 px-1 font-medium text-right w-24">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                @foreach ($cashierStats as $stat)
                                    <tr>
                                        <td class="py-1 px-1 font-medium text-foreground truncate max-w-[140px]">{{ $stat->cashier_name }}</td>
                                        <td class="py-1 px-1 text-center font-medium tabular-nums">{{ number_format($stat->total_transactions) }} pesanan</td>
                                        <td class="py-1 px-1 text-right font-medium tabular-nums text-foreground whitespace-nowrap">{{ Rupiah::format($stat->total_sales) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>
            </div>

            <!-- IV. Tabel Rincian Lengkap Transaksi Penjualan -->
            <section aria-labelledby="orders-list-heading" class="mb-4">
                <div class="flex items-baseline justify-between border-b border-border pb-1 mb-1.5">
                    <h3 id="orders-list-heading" class="font-serif text-xs font-bold uppercase tracking-wider text-foreground">
                        Rincian Transaksi Penjualan
                    </h3>
                    <span class="text-[10px] text-muted-foreground tabular-nums">{{ number_format($orders->count()) }} transaksi terdata</span>
                </div>

                @if ($orders->isEmpty())
                    <p class="py-4 text-center text-xs text-muted-foreground">Tidak ada transaksi penjualan pada periode ini.</p>
                @else
                    <table class="w-full text-left text-[11px] border-collapse">
                        <thead>
                            <tr class="border-b-2 border-foreground/40 text-muted-foreground bg-muted/40 text-[10px]">
                                <th scope="col" class="py-1 px-1.5 font-semibold w-6 text-center">#</th>
                                <th scope="col" class="py-1 px-1.5 font-semibold w-32">Struk & Waktu</th>
                                <th scope="col" class="py-1 px-1.5 font-semibold w-24">Kasir</th>
                                <th scope="col" class="py-1 px-1.5 font-semibold w-24">Pelanggan</th>
                                <th scope="col" class="py-1 px-1.5 font-semibold w-16">Metode</th>
                                <th scope="col" class="py-1 px-1.5 font-semibold">Pesanan Menu</th>
                                <th scope="col" class="py-1 px-2 font-semibold text-right w-28">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60">
                            @foreach ($orders as $index => $order)
                                <tr class="align-top hover:bg-muted/10">
                                    <td class="py-1.5 px-1.5 text-center text-muted-foreground font-mono text-[10px]">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-1.5 px-1.5 whitespace-nowrap">
                                        <span class="font-mono font-medium text-foreground block leading-tight">{{ $order->number }}</span>
                                        <span class="text-[10px] text-muted-foreground tabular-nums">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="py-1.5 px-1.5 text-foreground leading-tight">
                                        {{ $order->cashier->name }}
                                    </td>
                                    <td class="py-1.5 px-1.5 leading-tight">
                                        @if ($order->member)
                                            <span class="font-medium text-foreground block">{{ $order->member->name }}</span>
                                            <span class="text-[9px] text-muted-foreground tabular-nums">{{ $order->member->phone }}</span>
                                        @else
                                            <span class="text-muted-foreground">Umum</span>
                                        @endif
                                    </td>
                                    <td class="py-1.5 px-1.5 whitespace-nowrap text-foreground">
                                        {{ $order->payment_method->label() }}
                                    </td>
                                    <td class="py-1.5 px-1.5 text-foreground leading-snug">
                                        <span>{{ $order->items->map(fn ($i) => "{$i->quantity}x {$i->name}")->join(', ') }}</span>
                                        @if ($order->discount_amount > 0)
                                            <span class="block text-[10px] text-muted-foreground tabular-nums">
                                                (Diskon: -{{ Rupiah::format($order->discount_amount) }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-1.5 px-2 text-right whitespace-nowrap tabular-nums font-semibold text-foreground">
                                        {{ Rupiah::format($order->total) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-foreground bg-muted/40 font-semibold text-foreground text-[11px]">
                                <td colspan="5" class="py-2 px-2 text-left">
                                    <span class="uppercase tracking-wider font-bold">Grand Total ({{ number_format($totalOrders) }} Transaksi)</span>
                                    <span class="block text-[10px] font-normal text-muted-foreground tabular-nums">
                                        Subtotal: {{ Rupiah::format($subtotalSales) }}
                                        @if ($totalDiscount > 0) · Diskon: -{{ Rupiah::format($totalDiscount) }} @endif
                                        @if ($totalTax > 0) · Pajak: {{ Rupiah::format($totalTax) }} @endif
                                    </span>
                                </td>
                                <td class="py-2 px-2 text-right text-muted-foreground font-medium text-[10px] uppercase tracking-wider whitespace-nowrap">
                                    Total Bersih:
                                </td>
                                <td class="py-2 px-2 text-right whitespace-nowrap font-bold text-sm tabular-nums text-foreground">
                                    {{ Rupiah::format($grossSales) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </section>

            <!-- Bagian Pengesahan / Tanda Tangan -->
            <section aria-labelledby="signature-heading" class="mt-6 pt-3 border-t border-border avoid-break">
                <h3 id="signature-heading" class="sr-only">Pengesahan Dokumen</h3>
                <div class="flex justify-between items-start text-xs text-foreground px-4">
                    <!-- Kolom Tanggal & Dibuat Oleh -->
                    <div class="flex flex-col items-center w-48 text-center">
                        <p class="text-[10px] text-muted-foreground">Petugas Pembuat Laporan,</p>
                        <div class="h-12 flex items-center justify-center">
                            <!-- Ruang tanda tangan -->
                        </div>
                        <p class="font-medium text-[11px] border-b border-foreground pb-0.5 px-4 min-w-[140px]">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-[9px] text-muted-foreground mt-0.5">{{ auth()->user()->role->label() }}</p>
                    </div>

                    <!-- Kolom Lokasi, Tanggal & Disetujui Oleh -->
                    <div class="flex flex-col items-center w-52 text-center">
                        <p class="text-[10px] text-muted-foreground">
                            Banda Aceh, {{ now()->translatedFormat('d F Y') }}
                        </p>
                        <p class="text-[10px] text-muted-foreground">Mengetahui & Menyetujui,</p>
                        <div class="h-12 flex items-center justify-center">
                            <!-- Ruang tanda tangan -->
                        </div>
                        <p class="font-medium text-[11px] border-b border-foreground pb-0.5 px-4 min-w-[140px]">
                            ( ........................................ )
                        </p>
                        <p class="text-[9px] text-muted-foreground mt-0.5">Owner / Manajer Operasional</p>
                    </div>
                </div>

                <!-- Footer Legalitas -->
                <div class="mt-4 pt-2 border-t border-dashed border-border/80 flex items-center justify-between text-[9px] text-muted-foreground">
                    <span>Dokumen rekapitulasi resmi sistem POS Soho Caffe.</span>
                    <span class="tabular-nums">Hal Cetak Resmi · {{ now()->format('d/m/Y H:i:s') }}</span>
                </div>
            </section>
        </article>
    </div>

    @push('styles')
        <style>
            @media print {
                /* Sembunyikan elemen non-cetak */
                .no-print,
                header.no-print,
                nav,
                aside {
                    display: none !important;
                }

                /* Pengaturan halaman cetak A4 */
                @page {
                    size: A4 portrait;
                    margin: 8mm 10mm 10mm 10mm;
                }

                html, body {
                    background: #ffffff !important;
                    color: #000000 !important;
                    font-size: 10.5px !important;
                    line-height: 1.35 !important;
                }

                main {
                    max-width: 100% !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }

                .report-paper {
                    border: none !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                    background: transparent !important;
                    color: #000000 !important;
                    width: 100% !important;
                    max-width: 100% !important;
                }

                /* Pastikan warna teks & border tajam di kertas */
                .report-paper * {
                    color: #000000 !important;
                    border-color: #cbd5e1 !important;
                }

                /* Pertahankan format angka tanpa pemotongan teks */
                .tabular-nums,
                .whitespace-nowrap {
                    white-space: nowrap !important;
                }

                /* Hindari pemotongan blok di tengah halaman */
                .avoid-break {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                table {
                    width: 100% !important;
                    border-collapse: collapse !important;
                    page-break-inside: auto;
                }

                tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                thead {
                    display: table-header-group;
                }

                tfoot {
                    display: table-footer-group;
                }
            }
        </style>
    @endpush
</x-layouts.guest>
