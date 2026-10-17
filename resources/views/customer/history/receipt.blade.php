@use('App\Support\Rupiah')

<x-layouts.customer :title="'E-Receipt · ' . $order->number">
    <div class="flex flex-col gap-5">
        <div class="flex items-center justify-between no-print">
            <a href="{{ route('customer.history.index') }}" class="text-xs text-muted-foreground hover:text-foreground">
                Kembali ke Riwayat Transaksi
            </a>
            <button
                type="button"
                onclick="window.print()"
                class="rounded-md border border-border bg-card px-3 py-1.5 text-xs font-medium text-foreground hover:bg-accent"
            >
                Cetak Struk
            </button>
        </div>

        <!-- Kartu E-Receipt Digital -->
        <article class="receipt-card flex flex-col gap-4 rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm text-xs font-mono">
            <div class="flex flex-col items-center text-center gap-1 border-b border-dashed border-border pb-4">
                <span class="rounded bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400">
                    LUNAS
                </span>
                <h1 class="mt-1 font-serif text-xl font-bold tracking-tight text-foreground">{{ config('app.name') }}</h1>
                <p class="text-[11px] text-muted-foreground">Nota Transaksi Elektronik (E-Receipt)</p>

                <div class="mt-2 flex flex-col items-center text-[11px] text-muted-foreground">
                    <span class="font-bold text-foreground">{{ $order->number }}</span>
                    <span>{{ $order->created_at->translatedFormat('d F Y, H:i') }}</span>
                    <span>Kasir: {{ $order->cashier->name }}</span>
                </div>
            </div>

            <!-- Daftar Menu -->
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

            <!-- Ringkasan Biaya -->
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
                    <span>Total Pembayaran</span>
                    <span class="tabular-nums text-primary">{{ Rupiah::format($order->total) }}</span>
                </div>
            </div>

            <!-- Metode Pembayaran -->
            <div class="flex flex-col gap-1 text-[11px] border-b border-dashed border-border pb-3">
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

            <!-- Poin Member -->
            <div class="flex flex-col gap-1 text-[11px] bg-primary/5 p-3 rounded-md">
                <div class="flex justify-between text-primary font-semibold">
                    <span>Poin Belanja Didapat:</span>
                    <span class="tabular-nums">+{{ number_format($order->points_earned) }} poin</span>
                </div>
                <div class="flex justify-between text-muted-foreground text-[10px]">
                    <span>Saldo Poin Setelahnya:</span>
                    <span class="tabular-nums font-medium text-foreground">{{ number_format($order->member_points_after) }} poin</span>
                </div>
            </div>

            <div class="text-center text-[10px] text-muted-foreground pt-1">
                <p>Terima kasih telah berkunjung ke {{ config('app.name') }}!</p>
            </div>
        </article>
    </div>

    @push('styles')
        <style>
            @media print {
                header,
                nav,
                [data-print-hide],
                .no-print {
                    display: none !important;
                }
                body {
                    background: #fff !important;
                    color: #000 !important;
                }
                .receipt-card {
                    border: none !important;
                    box-shadow: none !important;
                    padding: 0 !important;
                    background: #fff !important;
                    color: #000 !important;
                    max-width: 320px;
                    margin: 0 auto;
                }
                .receipt-card,
                .receipt-card * {
                    color: #000 !important;
                    border-color: #e5e5e5 !important;
                }
                main {
                    padding: 0 !important;
                }
            }
        </style>
    @endpush
</x-layouts.customer>
