@use('App\Role')
@use('App\Support\Rupiah')

<x-layouts.guest title="Struk {{ $order->number }}" max-width="max-w-md">
    <div class="flex flex-col gap-6">
        <!-- Aksi di atas struk (tidak dicetak) -->
        <div class="no-print flex items-center justify-between gap-3">
            @if (request('from') === 'reports' || (url()->previous() && str_contains(url()->previous(), 'reports')))
                <april:button-link href="{{ route('admin.reports.index') }}" variant="outline" size="none" class="h-9 px-3 text-xs gap-1.5">
                    &larr; <span>Kembali ke laporan</span>
                </april:button-link>
            @else
                <april:button-link href="{{ route('pos.index') }}" variant="outline" size="none" class="h-9 px-3 text-xs gap-1.5">
                    &larr; <span>Pesanan baru</span>
                </april:button-link>
            @endif

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-3 text-xs font-medium text-primary-foreground hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring shadow-xs"
                >
                    Cetak Struk
                </button>
                <x-theme-toggle />
            </div>
        </div>

        <!-- Kartu Struk Pembayaran -->
        <article class="receipt-paper flex flex-col gap-4 rounded-lg border border-border bg-card p-6 text-card-foreground shadow-xs text-xs font-mono">
            <!-- Header Toko -->
            <div class="flex flex-col items-center text-center gap-1 border-b border-dashed border-border pb-4">
                <h1 class="font-serif text-xl font-bold tracking-tight text-foreground">{{ config('app.name') }}</h1>
                <p class="text-[11px] text-muted-foreground">Struk Pembayaran Resmi</p>
                <div class="mt-2 flex flex-col items-center text-[11px] text-muted-foreground">
                    <span class="font-semibold text-foreground tabular-nums">{{ $order->number }}</span>
                    <span>{{ $order->created_at->translatedFormat('d F Y, H:i') }}</span>
                    <span>Kasir: {{ $order->cashier->name }}</span>
                </div>
            </div>

            <!-- Informasi Member (jika ada) -->
            @if ($order->member)
                <div class="flex flex-col gap-1 border-b border-dashed border-border pb-3 text-[11px]">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Member:</span>
                        <span class="font-medium text-foreground">{{ $order->member->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">WhatsApp:</span>
                        <span class="tabular-nums text-foreground">{{ $order->member->displayPhone() }}</span>
                    </div>
                    <div class="flex justify-between text-primary font-medium">
                        <span>Poin didapat:</span>
                        <span class="tabular-nums">+{{ number_format($order->points_earned) }} poin</span>
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <span>Total saldo poin:</span>
                        <span class="tabular-nums font-medium text-foreground">{{ number_format($order->member_points_after) }} poin</span>
                    </div>
                </div>
            @endif

            <!-- Rincian Menu -->
            <div class="flex flex-col gap-2 border-b border-dashed border-border pb-4">
                <div class="grid grid-cols-12 font-semibold text-muted-foreground pb-1 border-b border-border/40 text-[11px]">
                    <span class="col-span-6">Item</span>
                    <span class="col-span-2 text-center">Qty</span>
                    <span class="col-span-4 text-right">Total</span>
                </div>

                @foreach ($order->items as $item)
                    <div class="grid grid-cols-12 items-baseline py-1">
                        <div class="col-span-6 pr-1">
                            <p class="font-medium text-foreground">{{ $item->name }}</p>
                            <p class="text-[10px] text-muted-foreground tabular-nums">{{ Rupiah::format($item->unit_price) }}</p>
                        </div>
                        <span class="col-span-2 text-center tabular-nums text-foreground">{{ $item->quantity }}</span>
                        <span class="col-span-4 text-right font-medium tabular-nums text-foreground">{{ Rupiah::format($item->line_total) }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Perhitungan Total -->
            <div class="flex flex-col gap-1.5 border-b border-dashed border-border pb-4">
                <div class="flex justify-between text-muted-foreground">
                    <span>Subtotal</span>
                    <span class="tabular-nums text-foreground">{{ Rupiah::format($order->subtotal) }}</span>
                </div>
                @if ($order->tax_amount > 0)
                    <div class="flex justify-between text-muted-foreground">
                        <span>Pajak ({{ rtrim(rtrim((string) $order->tax_percent, '0'), '.') }}%)</span>
                        <span class="tabular-nums text-foreground">{{ Rupiah::format($order->tax_amount) }}</span>
                    </div>
                @endif
                @if ($order->discount_amount > 0)
                    <div class="flex justify-between text-muted-foreground">
                        <span>Potongan voucer ({{ $order->voucher?->code }})</span>
                        <span class="tabular-nums text-foreground">- {{ Rupiah::format($order->discount_amount) }}</span>
                    </div>
                @endif
                <div class="flex justify-between pt-1 border-t border-border/60 font-serif text-base font-bold text-foreground">
                    <span>Total</span>
                    <span class="tabular-nums text-primary">{{ Rupiah::format($order->total) }}</span>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="flex flex-col gap-1 text-[11px]">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Metode Bayar:</span>
                    <span class="font-medium text-foreground">{{ $order->payment_method->label() }}</span>
                </div>
                @if ($order->cash_received !== null)
                    <div class="flex justify-between text-muted-foreground">
                        <span>Tunai Diterima:</span>
                        <span class="tabular-nums text-foreground">{{ Rupiah::format($order->cash_received) }}</span>
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <span>Kembalian:</span>
                        <span class="tabular-nums font-semibold text-foreground">{{ Rupiah::format($order->change_due) }}</span>
                    </div>
                @endif
            </div>

            <!-- Footer Struk -->
            <div class="pt-3 text-center text-[11px] text-muted-foreground border-t border-dashed border-border">
                <p>Terima kasih atas kunjungan Anda!</p>
                <p class="text-[10px] mt-0.5">Simpan struk ini sebagai bukti pembayaran yang sah.</p>
            </div>
        </article>

        <!-- Link navigasi tambahan -->
        <div class="no-print flex items-center justify-between gap-3 text-xs text-muted-foreground">
            @if (auth()->user()->hasRole(Role::Admin))
                <a href="{{ route('admin.dashboard') }}" class="hover:text-foreground underline underline-offset-4">
                    Ke back office
                </a>
            @else
                <span></span>
            @endif

            <a href="{{ route('pos.index') }}" class="hover:text-foreground underline underline-offset-4 font-medium text-primary">
                Transaksi kasir berikutnya &rarr;
            </a>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {
                body {
                    background: #fff !important;
                    color: #000 !important;
                }
                .no-print {
                    display: none !important;
                }
                main {
                    max-width: 100% !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }
                .receipt-paper {
                    border: none !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                    background: transparent !important;
                    color: #000 !important;
                    max-width: 320px;
                    margin: 0 auto;
                }
                .receipt-paper,
                .receipt-paper * {
                    color: #000 !important;
                    border-color: #e5e5e5 !important;
                }
            }
        </style>
    @endpush
</x-layouts.guest>
