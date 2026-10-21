@use('App\Role')
@use('App\Support\Rupiah')
@use('Carbon\Carbon')

<x-layouts.guest :title="'Cetak Laporan - ' . $periodLabel" max-width="max-w-5xl" :centered="false">
    <div class="flex flex-col gap-6 py-4">
        <!-- Bilah Aksi di Atas (Tidak Dicetak) -->
        <div class="no-print flex flex-wrap items-center justify-between gap-3 rounded-lg border border-border bg-card p-4 text-card-foreground shadow-xs">
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.reports.index', request()->query()) }}"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md border border-input bg-background px-3 text-xs font-medium text-foreground hover:bg-accent hover:text-accent-foreground transition-colors"
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
                    class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-primary px-4 text-xs font-medium text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring shadow-xs transition-colors"
                >
                    <x-lucide-printer class="size-4" />
                    <span>Cetak Sekarang</span>
                </button>
                <x-theme-toggle />
            </div>
        </div>

        <!-- Lembar Laporan Fisik / Printable Paper -->
        <article class="report-paper rounded-lg border border-border bg-card p-6 sm:p-10 text-card-foreground shadow-xs text-xs">
            <!-- Kop Surat / Header Bisnis -->
            <header class="border-b-2 border-foreground pb-4 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-lg border border-border bg-muted/60 text-foreground font-serif font-bold text-lg">
                            SC
                        </div>
                        <div>
                            <h1 class="font-serif text-xl sm:text-2xl font-bold tracking-tight text-foreground uppercase">
                                Soho Caffe
                            </h1>
                            <p class="text-xs text-muted-foreground font-medium">Coffee & Eatery</p>
                            <p class="text-[11px] text-muted-foreground mt-0.5">Sistem Kasir (POS) & Rekapitulasi Pembukuan Usaha</p>
                        </div>
                    </div>

                    <div class="text-left sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-border text-[11px] text-muted-foreground space-y-0.5">
                        <p class="font-semibold text-foreground text-xs uppercase tracking-wide">Laporan Penjualan Resmi</p>
                        <p>Nomor Ref: <span class="font-mono text-foreground font-medium">RPT-{{ now()->format('YmdHi') }}</span></p>
                        <p>Waktu Cetak: <span class="text-foreground tabular-nums">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span></p>
                        <p>Dicetak Oleh: <span class="text-foreground font-medium">{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</span></p>
                    </div>
                </div>

                <!-- Judul Dokumen & Periode -->
                <div class="mt-4 pt-4 border-t border-dashed border-border/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h2 class="font-serif text-base sm:text-lg font-semibold text-foreground uppercase tracking-wide">
                            Laporan Penjualan & Pembukuan
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            Periode: <strong class="text-foreground">{{ $periodLabel }}</strong>
                            <span class="tabular-nums">({{ Carbon::parse($startDate)->translatedFormat('d M Y') }} s/d {{ Carbon::parse($endDate)->translatedFormat('d M Y') }})</span>
                        </p>
                    </div>
                    <div class="text-xs sm:text-right">
                        <span class="inline-flex items-center rounded-md border border-border px-2.5 py-1 text-[11px] font-medium text-foreground bg-muted/40">
                            Total Transaksi: <strong class="ml-1 tabular-nums">{{ number_format($totalOrders) }} Struk</strong>
                        </span>
                    </div>
                </div>
            </header>

            <!-- Ikhtisar Finansial & Operasional -->
            <section aria-labelledby="summary-heading" class="mb-6 avoid-break">
                <h3 id="summary-heading" class="font-serif text-sm font-semibold uppercase tracking-wider text-foreground mb-3 pb-1 border-b border-border">
                    I. Ringkasan Finansial & Arus Pembayaran
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="rounded border border-border p-3 bg-muted/20">
                        <span class="text-[11px] text-muted-foreground block">Total Omzet (Gross)</span>
                        <span class="text-base sm:text-lg font-serif font-bold text-foreground tabular-nums block mt-1">
                            {{ Rupiah::format($grossSales) }}
                        </span>
                        <span class="text-[10px] text-muted-foreground block mt-0.5">Penjualan + Pajak</span>
                    </div>

                    <div class="rounded border border-border p-3 bg-muted/20">
                        <span class="text-[11px] text-muted-foreground block">Subtotal Penjualan Menu</span>
                        <span class="text-base sm:text-lg font-serif font-semibold text-foreground tabular-nums block mt-1">
                            {{ Rupiah::format($subtotalSales) }}
                        </span>
                        <span class="text-[10px] text-muted-foreground block mt-0.5">
                            @if ($totalDiscount > 0)
                                Diskon Voucer: -{{ Rupiah::format($totalDiscount) }}
                            @else
                                Tanpa potongan diskon
                            @endif
                        </span>
                    </div>

                    <div class="rounded border border-border p-3 bg-muted/20">
                        <span class="text-[11px] text-muted-foreground block">Arus Kas Tunai (Cash)</span>
                        <span class="text-base sm:text-lg font-serif font-semibold text-foreground tabular-nums block mt-1">
                            {{ Rupiah::format($cashSales) }}
                        </span>
                        <span class="text-[10px] text-muted-foreground block mt-0.5">Diterima di laci kasir</span>
                    </div>

                    <div class="rounded border border-border p-3 bg-muted/20">
                        <span class="text-[11px] text-muted-foreground block">Arus Non-Tunai / QRIS</span>
                        <span class="text-base sm:text-lg font-serif font-semibold text-foreground tabular-nums block mt-1">
                            {{ Rupiah::format($nonCashSales) }}
                        </span>
                        <span class="text-[10px] text-muted-foreground block mt-0.5">Transfer / QRIS / EDC</span>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3 text-[11px]">
                    <div class="flex justify-between border-b border-border/60 py-1">
                        <span class="text-muted-foreground">Total Transaksi:</span>
                        <span class="font-medium text-foreground tabular-nums">{{ number_format($totalOrders) }} struk</span>
                    </div>
                    <div class="flex justify-between border-b border-border/60 py-1">
                        <span class="text-muted-foreground">Rata-rata/Struk (AOV):</span>
                        <span class="font-medium text-foreground tabular-nums">{{ Rupiah::format($averageOrderValue) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-border/60 py-1">
                        <span class="text-muted-foreground">Pajak Terkumpul:</span>
                        <span class="font-medium text-foreground tabular-nums">{{ Rupiah::format($totalTax) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-border/60 py-1">
                        <span class="text-muted-foreground">Poin Diterbitkan:</span>
                        <span class="font-medium text-foreground tabular-nums">+{{ number_format($totalPointsEarned) }} poin</span>
                    </div>
                </div>
            </section>

            <!-- Grid 2 Kolom: Menu Terlaris & Performa Kasir -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 avoid-break">
                <!-- Tabel Menu Terlaris -->
                <section aria-labelledby="best-seller-heading" class="rounded border border-border p-3">
                    <h3 id="best-seller-heading" class="font-serif text-xs font-semibold uppercase tracking-wider text-foreground mb-2 pb-1 border-b border-border flex justify-between">
                        <span>II. Menu Terlaris (Best Seller)</span>
                        <span class="text-[10px] text-muted-foreground lowercase font-sans">top menu</span>
                    </h3>

                    @if ($bestSellers->isEmpty())
                        <p class="py-4 text-center text-[11px] text-muted-foreground">Belum ada penjualan menu pada periode ini.</p>
                    @else
                        <table class="w-full text-left text-[11px]">
                            <thead>
                                <tr class="border-b border-border text-muted-foreground">
                                    <th scope="col" class="py-1 px-1 font-medium w-6">#</th>
                                    <th scope="col" class="py-1 px-1 font-medium">Menu</th>
                                    <th scope="col" class="py-1 px-1 font-medium text-center">Qty</th>
                                    <th scope="col" class="py-1 px-1 font-medium text-right">Omzet</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                @foreach ($bestSellers->take(8) as $index => $item)
                                    <tr>
                                        <td class="py-1 px-1 text-muted-foreground font-mono">{{ $index + 1 }}</td>
                                        <td class="py-1 px-1 font-medium text-foreground">{{ $item->name }}</td>
                                        <td class="py-1 px-1 text-center font-medium tabular-nums">{{ number_format($item->total_qty) }}</td>
                                        <td class="py-1 px-1 text-right tabular-nums text-foreground">{{ Rupiah::format($item->total_sales) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>

                <!-- Tabel Performa Kasir -->
                <section aria-labelledby="cashier-stat-heading" class="rounded border border-border p-3">
                    <h3 id="cashier-stat-heading" class="font-serif text-xs font-semibold uppercase tracking-wider text-foreground mb-2 pb-1 border-b border-border flex justify-between">
                        <span>III. Performa Kasir</span>
                        <span class="text-[10px] text-muted-foreground lowercase font-sans">produktivitas</span>
                    </h3>

                    @if ($cashierStats->isEmpty())
                        <p class="py-4 text-center text-[11px] text-muted-foreground">Belum ada data kasir pada periode ini.</p>
                    @else
                        <table class="w-full text-left text-[11px]">
                            <thead>
                                <tr class="border-b border-border text-muted-foreground">
                                    <th scope="col" class="py-1 px-1 font-medium">Nama Kasir</th>
                                    <th scope="col" class="py-1 px-1 font-medium text-center">Transaksi</th>
                                    <th scope="col" class="py-1 px-1 font-medium text-right">Total Penjualan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                @foreach ($cashierStats as $stat)
                                    <tr>
                                        <td class="py-1 px-1 font-medium text-foreground">{{ $stat->cashier_name }}</td>
                                        <td class="py-1 px-1 text-center font-medium tabular-nums">{{ number_format($stat->total_transactions) }} pesanan</td>
                                        <td class="py-1 px-1 text-right font-medium tabular-nums text-foreground">{{ Rupiah::format($stat->total_sales) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </section>
            </div>

            <!-- Tabel Rincian Lengkap Transaksi Penjualan -->
            <section aria-labelledby="orders-list-heading" class="mb-6">
                <div class="flex items-baseline justify-between border-b border-border pb-1 mb-2">
                    <h3 id="orders-list-heading" class="font-serif text-sm font-semibold uppercase tracking-wider text-foreground">
                        IV. Rincian Lengkap Transaksi Penjualan (Audit Trail)
                    </h3>
                    <span class="text-[11px] text-muted-foreground tabular-nums">{{ number_format($orders->count()) }} transaksi terdata</span>
                </div>

                @if ($orders->isEmpty())
                    <p class="py-6 text-center text-xs text-muted-foreground">Tidak ada transaksi penjualan pada periode ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[11px]">
                            <thead>
                                <tr class="border-b-2 border-border text-muted-foreground bg-muted/30">
                                    <th scope="col" class="py-1.5 px-2 font-medium">No. Struk & Waktu</th>
                                    <th scope="col" class="py-1.5 px-2 font-medium">Kasir</th>
                                    <th scope="col" class="py-1.5 px-2 font-medium">Pelanggan</th>
                                    <th scope="col" class="py-1.5 px-2 font-medium">Metode</th>
                                    <th scope="col" class="py-1.5 px-2 font-medium">Menu Dipesan</th>
                                    <th scope="col" class="py-1.5 px-2 font-medium text-right">Subtotal</th>
                                    <th scope="col" class="py-1.5 px-2 font-medium text-right">Diskon</th>
                                    <th scope="col" class="py-1.5 px-2 font-medium text-right">Pajak</th>
                                    <th scope="col" class="py-1.5 px-2 font-medium text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/60">
                                @foreach ($orders as $order)
                                    <tr class="align-top hover:bg-muted/20">
                                        <td class="py-2 px-2 whitespace-nowrap">
                                            <span class="font-mono font-medium text-foreground block">{{ $order->number }}</span>
                                            <span class="text-[10px] text-muted-foreground tabular-nums">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="py-2 px-2 whitespace-nowrap text-foreground">
                                            {{ $order->cashier->name }}
                                        </td>
                                        <td class="py-2 px-2 whitespace-nowrap">
                                            @if ($order->member)
                                                <span class="font-medium text-foreground block">{{ $order->member->name }}</span>
                                                <span class="text-[10px] text-muted-foreground tabular-nums">{{ $order->member->phone }}</span>
                                            @else
                                                <span class="text-muted-foreground">Umum</span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-2 whitespace-nowrap text-foreground">
                                            {{ $order->payment_method->label() }}
                                        </td>
                                        <td class="py-2 px-2 max-w-xs text-foreground leading-relaxed">
                                            {{ $order->items->map(fn ($i) => "{$i->quantity}x {$i->name}")->join(', ') }}
                                        </td>
                                        <td class="py-2 px-2 text-right tabular-nums text-muted-foreground whitespace-nowrap">
                                            {{ Rupiah::format($order->subtotal) }}
                                        </td>
                                        <td class="py-2 px-2 text-right tabular-nums text-muted-foreground whitespace-nowrap">
                                            {{ $order->discount_amount > 0 ? '-' . Rupiah::format($order->discount_amount) : '-' }}
                                        </td>
                                        <td class="py-2 px-2 text-right tabular-nums text-muted-foreground whitespace-nowrap">
                                            {{ $order->tax_amount > 0 ? Rupiah::format($order->tax_amount) : '-' }}
                                        </td>
                                        <td class="py-2 px-2 text-right tabular-nums font-semibold text-foreground whitespace-nowrap">
                                            {{ Rupiah::format($order->total) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-foreground font-semibold text-foreground bg-muted/40">
                                    <td colspan="5" class="py-2 px-2 text-right uppercase tracking-wider text-[11px]">
                                        Grand Total ({{ number_format($totalOrders) }} Transaksi)
                                    </td>
                                    <td class="py-2 px-2 text-right tabular-nums">
                                        {{ Rupiah::format($subtotalSales) }}
                                    </td>
                                    <td class="py-2 px-2 text-right tabular-nums text-muted-foreground">
                                        {{ $totalDiscount > 0 ? '-' . Rupiah::format($totalDiscount) : '-' }}
                                    </td>
                                    <td class="py-2 px-2 text-right tabular-nums">
                                        {{ Rupiah::format($totalTax) }}
                                    </td>
                                    <td class="py-2 px-2 text-right tabular-nums text-base font-serif">
                                        {{ Rupiah::format($grossSales) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </section>

            <!-- Bagian Pengesahan / Tanda Tangan -->
            <section aria-labelledby="signature-heading" class="mt-10 pt-6 border-t border-border avoid-break">
                <h3 id="signature-heading" class="sr-only">Pengesahan Laporan</h3>
                <div class="flex justify-between items-start text-xs text-foreground">
                    <!-- Kolom Tanggal & Dibuat Oleh -->
                    <div class="flex flex-col items-center w-48 text-center">
                        <p class="text-[11px] text-muted-foreground">Petugas Pembuat Laporan,</p>
                        <div class="h-20 flex items-center justify-center">
                            <!-- Tempat tanda tangan fisik / stempel -->
                        </div>
                        <p class="font-medium border-b border-foreground pb-1 px-4 min-w-[160px]">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-[10px] text-muted-foreground mt-0.5">{{ auth()->user()->role->label() }}</p>
                    </div>

                    <!-- Kolom Lokasi, Tanggal & Disetujui Oleh -->
                    <div class="flex flex-col items-center w-52 text-center">
                        <p class="text-[11px] text-muted-foreground">
                            Banda Aceh, {{ now()->translatedFormat('d F Y') }}
                        </p>
                        <p class="text-[11px] text-muted-foreground">Mengetahui & Menyetujui,</p>
                        <div class="h-16 flex items-center justify-center">
                            <!-- Tempat tanda tangan fisik / stempel -->
                        </div>
                        <p class="font-medium border-b border-foreground pb-1 px-4 min-w-[160px]">
                            ( ........................................ )
                        </p>
                        <p class="text-[10px] text-muted-foreground mt-0.5">Owner / Manajer Operasional</p>
                    </div>
                </div>

                <!-- Footer Legalitas -->
                <div class="mt-8 pt-3 border-t border-dashed border-border/80 flex flex-col sm:flex-row items-center justify-between gap-1 text-[10px] text-muted-foreground">
                    <span>Dokumen ini dihasilkan secara otomatis oleh Sistem Soho Caffe POS.</span>
                    <span class="tabular-nums">Halaman Cetak Resmi · {{ now()->format('d/m/Y H:i:s') }}</span>
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
                    margin: 12mm 15mm 15mm 15mm;
                }

                html, body {
                    background: #ffffff !important;
                    color: #000000 !important;
                    font-size: 11px !important;
                    line-height: 1.4 !important;
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
                    border-color: #d1d5db !important;
                }

                /* Jangan potong baris tabel atau blok tanda tangan di tengah halaman */
                .avoid-break {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }

                table {
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
