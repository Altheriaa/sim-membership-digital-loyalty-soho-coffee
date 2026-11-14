@use('App\Support\Rupiah')

@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer title="Riwayat Transaksi">
    <div class="flex flex-col gap-4">
        <header class="flex flex-col gap-0.5">
            <h1 class="text-xl font-extrabold tracking-tight text-foreground">Riwayat Transaksi</h1>
            <p class="text-xs text-muted-foreground">Arsip struk belanja kasir dan catatan keluar-masuk poin.</p>
        </header>

        {{-- Segmented Control: Struk Belanja vs Mutasi Poin --}}
        <div class="grid grid-cols-2 gap-1 rounded-xl border border-border bg-secondary p-1 text-xs" role="tablist">
            <a href="{{ route('customer.history.index', ['tab' => 'orders']) }}"
                class="flex min-h-11 items-center justify-center rounded-lg px-3 py-1.5 transition-colors {{ $tab === 'orders' ? 'bg-card font-bold text-foreground shadow-2xs' : 'font-medium text-muted-foreground hover:text-foreground' }} {{ $focus }}">
                Struk Belanja
            </a>
            <a href="{{ route('customer.history.index', ['tab' => 'points']) }}"
                class="flex min-h-11 items-center justify-center rounded-lg px-3 py-1.5 transition-colors {{ $tab === 'points' ? 'bg-card font-bold text-foreground shadow-2xs' : 'font-medium text-muted-foreground hover:text-foreground' }} {{ $focus }}">
                Mutasi Poin
            </a>
        </div>

        @if ($tab === 'orders')
            @if ($orders->isEmpty())
                <div class="rounded-xl border border-dashed border-input bg-card p-5 text-xs leading-relaxed text-muted-foreground">
                    Belum ada struk belanja yang tercatat. Sebutkan nomor WhatsApp Anda di kasir saat memesan supaya struk dan poin masuk ke akun ini.
                </div>
            @else
                <div class="flex flex-col gap-2.5">
                    @foreach ($orders as $order)
                        <article class="rounded-xl border border-border bg-card p-4 text-card-foreground">
                            <div class="leader-row text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-extrabold text-foreground">{{ $order->number }}</span>
                                    @if ($order->isVoided())
                                        <span class="rounded border border-red-300 bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-900 dark:border-red-800 dark:bg-red-950/70 dark:text-red-200">
                                            Dibatalkan
                                        </span>
                                    @endif
                                </div>
                                <span class="leader-fill" aria-hidden="true"></span>
                                <span class="font-mono text-sm font-extrabold tabular-nums {{ $order->isVoided() ? 'line-through text-muted-foreground' : 'text-foreground' }}">{{ Rupiah::format($order->total) }}</span>
                            </div>

                            <div class="mt-1.5 flex items-center justify-between text-[11px] text-muted-foreground">
                                <span>{{ $order->created_at->translatedFormat('d M Y, H:i') }} · {{ $order->cashier->name }}</span>
                                <span class="rounded-md bg-secondary px-2 py-0.5 text-[10px] font-bold text-secondary-foreground">
                                    {{ $order->payment_method->label() }}
                                </span>
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-border/70 pt-2.5 text-xs">
                                <span class="font-mono text-[11px] font-bold text-foreground tabular-nums">
                                    +{{ number_format($order->points_earned) }} poin
                                </span>
                                <a href="{{ route('customer.receipt', $order) }}"
                                    class="rounded-md px-1 py-0.5 font-bold text-foreground underline underline-offset-4 hover:text-muted-foreground {{ $focus }}">
                                    Buka E-Receipt &rsaquo;
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$orders" />
            @endif
        @else
            @if ($pointTransactions->isEmpty())
                <div class="rounded-xl border border-dashed border-input bg-card p-5 text-xs leading-relaxed text-muted-foreground">
                    Belum ada catatan mutasi poin. Riwayat perolehan poin dari belanja maupun penukaran voucer akan tampil di sini.
                </div>
            @else
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    <ul class="divide-y divide-border">
                        @foreach ($pointTransactions as $tx)
                            <li class="flex items-center justify-between gap-3 p-4 text-xs">
                                <div class="min-w-0">
                                    <p class="truncate font-bold text-foreground">{{ $tx->description }}</p>
                                    <p class="mt-0.5 text-[11px] text-muted-foreground">{{ $tx->created_at->translatedFormat('d M Y, H:i') }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="font-mono text-sm font-extrabold tabular-nums {{ $tx->points >= 0 ? 'text-foreground' : 'text-destructive' }}">
                                        {{ $tx->points >= 0 ? '+' : '' }}{{ number_format($tx->points) }} poin
                                    </p>
                                    <p class="font-mono text-[10px] text-muted-foreground tabular-nums">Saldo: {{ number_format($tx->balance_after) }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <x-pagination :paginator="$pointTransactions" />
            @endif
        @endif
    </div>
</x-layouts.customer>
