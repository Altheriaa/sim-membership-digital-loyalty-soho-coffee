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
                @if (! auth()->user()->hasRole(Role::Owner))
                    <april:button-link href="{{ route('pos.index') }}" variant="outline" size="none" class="h-9 px-3 text-xs gap-1.5">
                        &larr; <span>Pesanan baru</span>
                    </april:button-link>
                @else
                    <april:button-link href="{{ route('admin.reports.index') }}" variant="outline" size="none" class="h-9 px-3 text-xs gap-1.5">
                        &larr; <span>Kembali ke laporan</span>
                    </april:button-link>
                @endif
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
                @if ($order->isVoided())
                    <span class="rounded border border-red-300 bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-900 dark:border-red-800 dark:bg-red-950/70 dark:text-red-200">
                        DIBATALKAN (VOID)
                    </span>
                @endif
                <h1 class="font-serif text-xl font-bold tracking-tight text-foreground">{{ config('app.name') }}</h1>
                <p class="text-[11px] text-muted-foreground">Struk Pembayaran Resmi</p>
                <div class="mt-2 flex flex-col items-center text-[11px] text-muted-foreground">
                    <span class="font-semibold text-foreground tabular-nums">{{ $order->number }}</span>
                    <span>{{ $order->created_at->translatedFormat('d F Y, H:i') }}</span>
                    <span>Kasir: {{ $order->cashier->name }}</span>
                    @if ($order->isVoided() && $order->void_reason)
                        <span class="mt-1 font-semibold text-red-900 dark:text-red-200">
                            Alasan batal: {{ $order->void_reason }}
                        </span>
                    @endif
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
                    @if ($order->points_redeemed > 0)
                        <div class="flex justify-between text-muted-foreground">
                            <span>Poin ditukar:</span>
                            <span class="tabular-nums">-{{ number_format($order->points_redeemed) }} poin</span>
                        </div>
                    @endif
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
                    @php
                        $voucherDiscount = max(0, $order->discount_amount - $order->point_discount_amount);
                    @endphp
                    @if ($voucherDiscount > 0)
                        <div class="flex justify-between text-muted-foreground">
                            <span>Potongan voucer ({{ $order->voucher?->code }})</span>
                            <span class="tabular-nums text-foreground">- {{ Rupiah::format($voucherDiscount) }}</span>
                        </div>
                    @endif
                    @if ($order->point_discount_amount > 0)
                        <div class="flex justify-between text-muted-foreground">
                            <span>Tukar {{ number_format($order->points_redeemed) }} poin</span>
                            <span class="tabular-nums text-foreground">- {{ Rupiah::format($order->point_discount_amount) }}</span>
                        </div>
                    @endif
                @endif
                <div class="flex justify-between pt-1 border-t border-border/60 font-serif text-base font-bold text-foreground">
                    <span>Total</span>
                    <span class="tabular-nums {{ $order->isVoided() ? 'line-through text-muted-foreground' : 'text-primary' }}">{{ Rupiah::format($order->total) }}</span>
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

        @if (session('status'))
            <div role="status" class="no-print rounded-md border border-emerald-300 bg-emerald-100 px-3 py-2 text-xs font-medium text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @error('void_reason')
            <div role="alert" class="no-print rounded-md border border-red-300 bg-red-100 px-3 py-2 text-xs font-medium text-red-900 dark:border-red-800 dark:bg-red-950/70 dark:text-red-200">
                {{ $message }}
            </div>
        @enderror
        @error('void')
            <div role="alert" class="no-print rounded-md border border-red-300 bg-red-100 px-3 py-2 text-xs font-medium text-red-900 dark:border-red-800 dark:bg-red-950/70 dark:text-red-200">
                {{ $message }}
            </div>
        @enderror

        @if (auth()->user()->hasRole(Role::Admin) && ! $order->isVoided())
            <details class="no-print rounded-lg border border-border bg-card p-4 text-xs text-card-foreground">
                <summary class="cursor-pointer font-semibold text-red-900 dark:text-red-200">
                    Batalkan Transaksi ini (Void)
                </summary>
                <form method="POST" action="{{ route('admin.orders.void', $order) }}" class="mt-3 flex flex-col gap-2.5">
                    @csrf
                    <label for="receipt_void_reason" class="text-[11px] font-medium text-muted-foreground">
                        Alasan pembatalan (saldo poin dan voucer member akan dipulihkan)
                    </label>
                    <input
                        id="receipt_void_reason"
                        type="text"
                        name="void_reason"
                        required
                        minlength="3"
                        maxlength="200"
                        placeholder="Contoh: Salah input item pesanan"
                        class="h-10 w-full rounded-md border border-input bg-background px-3 text-xs text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                    <button
                        type="submit"
                        class="inline-flex h-10 items-center justify-center rounded-md bg-destructive px-4 text-xs font-semibold text-destructive-foreground hover:bg-destructive/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        Ya, Batalkan Transaksi
                    </button>
                </form>
            </details>
        @endif

        <!-- Link navigasi tambahan -->
        <div class="no-print flex items-center justify-between gap-3 text-xs text-muted-foreground">
            @if (auth()->user()->hasRole(Role::Admin))
                <a href="{{ route('admin.dashboard') }}" class="hover:text-foreground underline underline-offset-4">
                    Ke back office
                </a>
            @else
                <a href="{{ route('admin.reports.index') }}" class="hover:text-foreground underline underline-offset-4">
                    Ke laporan
                </a>
            @endif

            @if (! auth()->user()->hasRole(Role::Owner))
                <a href="{{ route('pos.index') }}" class="hover:text-foreground underline underline-offset-4 font-medium text-primary">
                    Transaksi kasir berikutnya &rarr;
                </a>
            @endif
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
