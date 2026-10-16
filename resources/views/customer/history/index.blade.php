@use('App\Support\Rupiah')

<x-layouts.customer title="Riwayat Transaksi">
    <div class="flex flex-col gap-5">
        <header class="flex flex-col gap-1">
            <h1 class="font-serif text-xl font-bold tracking-tight text-foreground sm:text-2xl">Riwayat Transaksi</h1>
            <p class="text-xs text-muted-foreground">Catatan struk belanja kasir dan mutasi poin keanggotaan Anda.</p>
        </header>

        <!-- Pilihan Tab: Belanja vs Poin -->
        <div class="flex rounded-lg border border-border bg-card p-1 text-xs" role="tablist">
            <a
                href="{{ route('customer.history.index', ['tab' => 'orders']) }}"
                class="flex flex-1 items-center justify-center py-2 font-medium transition-colors {{ $tab === 'orders' ? 'bg-primary text-primary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground' }}"
            >
                Struk Belanja
            </a>
            <a
                href="{{ route('customer.history.index', ['tab' => 'points']) }}"
                class="flex flex-1 items-center justify-center py-2 font-medium transition-colors {{ $tab === 'points' ? 'bg-primary text-primary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground' }}"
            >
                Mutasi Poin
            </a>
        </div>

        @if ($tab === 'orders')
            <!-- Daftar Pesanan / Struk Belanja -->
            @if ($orders->isEmpty())
                <div class="rounded-lg border border-dashed border-border p-8 text-center text-xs text-muted-foreground">
                    Belum ada riwayat transaksi belanja.
                </div>
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($orders as $order)
                        <article class="flex flex-col justify-between gap-3 rounded-lg border border-border bg-card p-4 text-card-foreground shadow-xs">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="font-mono text-xs font-bold text-foreground">{{ $order->number }}</span>
                                    <p class="text-[11px] text-muted-foreground">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                                    <p class="text-[11px] text-muted-foreground">Kasir: {{ $order->cashier->name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-serif text-sm font-bold text-foreground tabular-nums">{{ Rupiah::format($order->total) }}</p>
                                    <span class="rounded bg-secondary px-1.5 py-0.5 text-[10px] font-medium text-secondary-foreground">
                                        {{ $order->payment_method->label() }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between border-t border-border/60 pt-2 text-xs">
                                <span class="font-medium text-primary text-[11px] tabular-nums">
                                    +{{ number_format($order->points_earned) }} poin
                                </span>
                                <a href="{{ route('customer.receipt', $order) }}" class="font-medium text-primary hover:underline">
                                    Lihat E-Receipt
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <x-pagination :paginator="$orders" />
            @endif
        @else
            <!-- Daftar Mutasi Poin -->
            @if ($pointTransactions->isEmpty())
                <div class="rounded-lg border border-dashed border-border p-8 text-center text-xs text-muted-foreground">
                    Belum ada riwayat mutasi poin.
                </div>
            @else
                <div class="overflow-hidden rounded-lg border border-border bg-card shadow-xs">
                    <ul class="divide-y divide-border">
                        @foreach ($pointTransactions as $tx)
                            <li class="flex items-center justify-between p-3.5 text-xs">
                                <div class="min-w-0 pr-3">
                                    <p class="font-medium text-foreground truncate">{{ $tx->description }}</p>
                                    <p class="text-[11px] text-muted-foreground">{{ $tx->created_at->translatedFormat('d M Y, H:i') }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="font-bold tabular-nums {{ $tx->points >= 0 ? 'text-primary' : 'text-destructive' }}">
                                        {{ $tx->points >= 0 ? '+' : '' }}{{ number_format($tx->points) }}
                                    </p>
                                    <p class="text-[10px] text-muted-foreground tabular-nums">Saldo: {{ number_format($tx->balance_after) }}</p>
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
