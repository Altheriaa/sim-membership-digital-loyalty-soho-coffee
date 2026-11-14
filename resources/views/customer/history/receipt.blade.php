@use('App\Support\Rupiah')

@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer :title="'E-Receipt · ' . $order->number">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between gap-2 no-print">
            <a href="{{ route('customer.history.index') }}"
                class="inline-flex min-h-11 items-center rounded-lg border border-border bg-card px-3.5 text-xs font-bold text-foreground transition-colors hover:bg-accent {{ $focus }}">
                &lsaquo; Riwayat
            </a>
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex min-h-11 items-center rounded-lg border border-foreground bg-primary px-4 text-xs font-extrabold text-primary-foreground transition-colors hover:bg-primary/90 dark:border-primary {{ $focus }}"
            >
                Cetak Struk
            </button>
        </div>

        {{-- Perforated Digital E-Receipt Pass --}}
        <article class="receipt-card relative overflow-hidden rounded-2xl border border-border bg-card text-card-foreground text-xs">
            <div class="flex flex-col items-center gap-1.5 p-5 text-center">
                @if ($order->isVoided())
                    <span class="rounded-md border border-red-300 bg-red-100 px-2.5 py-0.5 text-[10px] font-bold text-red-900 dark:border-red-800 dark:bg-red-950/70 dark:text-red-200">
                        DIBATALKAN (VOID)
                    </span>
                @else
                    <span class="rounded-md bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-900 dark:bg-emerald-950/70 dark:text-emerald-200">
                        LUNAS
                    </span>
                @endif

                <h1 class="mt-1 text-xl font-extrabold tracking-tight text-foreground">{{ config('app.name') }}</h1>
                <p class="text-[11px] text-muted-foreground">Nota Transaksi Elektronik (E-Receipt)</p>

                <div class="mt-1.5 flex flex-col items-center gap-0.5 font-mono text-[11px] text-muted-foreground">
                    <span class="font-bold text-foreground">{{ $order->number }}</span>
                    <span class="tabular-nums">{{ $order->created_at->translatedFormat('d F Y, H:i') }}</span>
                    <span>Kasir: {{ $order->cashier->name }}</span>
                    @if ($order->isVoided() && $order->void_reason)
                        <span class="mt-1 font-sans font-semibold text-red-900 dark:text-red-200">Alasan batal: {{ $order->void_reason }}</span>
                    @endif
                </div>
            </div>

            {{-- Perforated Tear Line --}}
            <div class="relative flex items-center justify-between" aria-hidden="true">
                <span class="-ml-3 size-6 rounded-full bg-background"></span>
                <span class="w-full border-t-2 border-dashed border-border"></span>
                <span class="-mr-3 size-6 rounded-full bg-background"></span>
            </div>

            <div class="flex flex-col gap-4 p-5">
                {{-- Order Items --}}
                <div class="flex flex-col gap-2 border-b border-dashed border-border pb-4 font-mono">
                    <div class="grid grid-cols-12 border-b border-border/60 pb-1.5 font-sans text-[11px] font-bold text-muted-foreground">
                        <span class="col-span-6">Item</span>
                        <span class="col-span-2 text-center">Qty</span>
                        <span class="col-span-4 text-right">Total</span>
                    </div>

                    @foreach ($order->items as $item)
                        <div class="grid grid-cols-12 items-baseline py-0.5">
                            <div class="col-span-6 pr-2 font-sans">
                                <p class="font-bold text-foreground">{{ $item->name }}</p>
                                <p class="font-mono text-[10px] text-muted-foreground tabular-nums">{{ Rupiah::format($item->unit_price) }}</p>
                            </div>
                            <span class="col-span-2 text-center text-foreground tabular-nums">{{ $item->quantity }}</span>
                            <span class="col-span-4 text-right font-bold text-foreground tabular-nums">{{ Rupiah::format($item->line_total) }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Totals Breakdown --}}
                <div class="flex flex-col gap-2 border-b border-dashed border-border pb-4">
                    <div class="leader-row text-muted-foreground">
                        <span>Subtotal</span>
                        <span class="leader-fill" aria-hidden="true"></span>
                        <span class="font-mono font-semibold text-foreground tabular-nums">{{ Rupiah::format($order->subtotal) }}</span>
                    </div>

                    @if ($order->tax_amount > 0)
                        <div class="leader-row text-muted-foreground">
                            <span>Pajak ({{ rtrim(rtrim((string) $order->tax_percent, '0'), '.') }}%)</span>
                            <span class="leader-fill" aria-hidden="true"></span>
                            <span class="font-mono font-semibold text-foreground tabular-nums">{{ Rupiah::format($order->tax_amount) }}</span>
                        </div>
                    @endif

                    @if ($order->discount_amount > 0)
                        @php
                            $voucherDiscount = max(0, $order->discount_amount - $order->point_discount_amount);
                        @endphp
                        @if ($voucherDiscount > 0)
                            <div class="leader-row text-muted-foreground">
                                <span>Potongan voucer ({{ $order->voucher?->code }})</span>
                                <span class="leader-fill" aria-hidden="true"></span>
                                <span class="font-mono font-semibold text-foreground tabular-nums">- {{ Rupiah::format($voucherDiscount) }}</span>
                            </div>
                        @endif
                        @if ($order->point_discount_amount > 0)
                            <div class="leader-row text-muted-foreground">
                                <span>Tukar {{ number_format($order->points_redeemed) }} poin</span>
                                <span class="leader-fill" aria-hidden="true"></span>
                                <span class="font-mono font-semibold text-foreground tabular-nums">- {{ Rupiah::format($order->point_discount_amount) }}</span>
                            </div>
                        @endif
                    @endif

                    <div class="flex items-baseline justify-between border-t border-border pt-2.5 text-sm font-extrabold text-foreground">
                        <span>Total Pembayaran</span>
                        <span class="font-mono text-base font-extrabold tabular-nums {{ $order->isVoided() ? 'line-through text-muted-foreground' : 'text-foreground' }}">
                            {{ Rupiah::format($order->total) }}
                        </span>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="flex flex-col gap-1.5 text-[11px]">
                    <div class="leader-row">
                        <span class="text-muted-foreground">Metode Bayar</span>
                        <span class="leader-fill" aria-hidden="true"></span>
                        <span class="font-bold text-foreground">{{ $order->payment_method->label() }}</span>
                    </div>
                    @if ($order->cash_received !== null)
                        <div class="leader-row text-muted-foreground">
                            <span>Tunai Diterima</span>
                            <span class="leader-fill" aria-hidden="true"></span>
                            <span class="font-mono text-foreground tabular-nums">{{ Rupiah::format($order->cash_received) }}</span>
                        </div>
                        <div class="leader-row text-muted-foreground">
                            <span>Kembalian</span>
                            <span class="leader-fill" aria-hidden="true"></span>
                            <span class="font-mono font-bold text-foreground tabular-nums">{{ Rupiah::format($order->change_due) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Espresso Points Stub Footer --}}
            <div class="receipt-points-stub border-t-2 border-dashed border-border bg-[#1F1D19] px-5 py-4 text-[11px] text-[#F5F4EF]">
                @if ($order->points_redeemed > 0)
                    <div class="flex justify-between text-[#D8D6CF]">
                        <span>Poin Ditukar</span>
                        <span class="font-mono font-bold tabular-nums">-{{ number_format($order->points_redeemed) }} poin</span>
                    </div>
                @endif
                <div class="mt-0.5 flex justify-between font-bold">
                    <span class="text-[#F5F4EF]">Poin Belanja Didapat</span>
                    <span class="font-mono text-xs font-extrabold text-[#F5B60A] tabular-nums">+{{ number_format($order->points_earned) }} poin</span>
                </div>
                <div class="mt-1 flex justify-between text-[10px] text-[#D8D6CF]">
                    <span>Saldo Poin Setelahnya</span>
                    <span class="font-mono font-semibold text-[#F5F4EF] tabular-nums">{{ number_format($order->member_points_after) }} poin</span>
                </div>

                <p class="mt-3 border-t border-[#F5F4EF]/15 pt-2.5 text-center text-[10px] text-[#D8D6CF]">
                    Terima kasih telah berkunjung ke {{ config('app.name') }}.
                </p>
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
                    background: #fff !important;
                    color: #000 !important;
                    max-width: 320px;
                    margin: 0 auto;
                }
                .receipt-card,
                .receipt-card * {
                    color: #000 !important;
                    background: #fff !important;
                    border-color: #d4d4d4 !important;
                }
                main {
                    padding: 0 !important;
                }
            }
        </style>
    @endpush
</x-layouts.customer>
