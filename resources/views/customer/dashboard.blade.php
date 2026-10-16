@use('App\Support\Rupiah')

<x-layouts.customer title="Keanggotaan Saya">
    <div class="flex flex-col gap-5">
        <!-- Kartu Keanggotaan Digital (Digital Member Card) -->
        <article class="relative overflow-hidden rounded-xl border border-primary/20 bg-linear-to-br from-card via-card to-primary/5 p-5 text-card-foreground shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">Kartu Digital Member</span>
                    <h1 class="font-serif text-xl font-bold tracking-tight text-foreground">{{ $member->name }}</h1>
                    <p class="text-xs text-muted-foreground tabular-nums">{{ $member->displayPhone() }}</p>
                </div>
                <div class="text-right">
                    @if ($tier)
                        <span class="inline-flex items-center rounded-full bg-primary/15 px-2.5 py-1 text-xs font-bold text-primary">
                            {{ $tier->name }}
                        </span>
                        <p class="mt-0.5 text-[10px] text-muted-foreground font-medium">{{ $tier->multiplierLabel() }}x poin belanja</p>
                    @else
                        <span class="inline-flex items-center rounded-full bg-secondary px-2.5 py-1 text-xs font-semibold text-secondary-foreground">
                            Standar
                        </span>
                    @endif
                </div>
            </div>

            <!-- Total Poin Belanja & Saldo -->
            <div class="mt-6 flex items-baseline justify-between border-t border-border/60 pt-4">
                <div>
                    <span class="text-[11px] text-muted-foreground">Saldo Poin Aktif</span>
                    <p class="font-serif text-3xl font-extrabold tracking-tight text-primary tabular-nums">
                        {{ number_format($member->points_balance) }} <span class="text-xs font-mono font-medium text-muted-foreground">poin</span>
                    </p>
                </div>
                <div class="text-right text-xs">
                    <span class="text-muted-foreground">Nilai Poin</span>
                    <p class="font-semibold text-foreground tabular-nums">
                        {{ Rupiah::format($member->points_balance * $settings->point_value) }}
                    </p>
                </div>
            </div>

            <!-- Kode Barcode / Nomor Member Visual -->
            <div class="mt-4 flex items-center justify-between rounded-md bg-background/60 p-2 text-[11px] border border-border/60">
                <span class="text-muted-foreground font-mono">ID: #MBR-{{ str_pad((string)$member->id, 5, '0', STR_PAD_LEFT) }}</span>
                <span class="text-[10px] text-primary font-medium">Tunjukkan ke kasir saat pesan</span>
            </div>
        </article>

        <!-- Progres Menuju Tier Berikutnya -->
        <section aria-label="Progres tingkat keanggotaan" class="rounded-lg border border-border bg-card p-4 text-card-foreground shadow-xs">
            <div class="flex items-center justify-between text-xs">
                <span class="font-medium text-foreground">Status Tingkat (Tier)</span>
                @if ($nextTier)
                    <span class="text-muted-foreground">Target: <strong class="text-foreground">{{ $nextTier->name }}</strong></span>
                @else
                    <span class="text-primary font-semibold">Tingkat Tertinggi</span>
                @endif
            </div>

            <div class="mt-2.5 h-2 w-full overflow-hidden rounded-full bg-secondary">
                <div class="h-full bg-primary transition-all duration-500" style="width: {{ $progressPct }}%"></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-[11px] text-muted-foreground">
                @if ($nextTier)
                    <span>Belanja {{ Rupiah::format($spendToNext) }} lagi</span>
                    <span class="tabular-nums font-medium text-foreground">{{ $progressPct }}%</span>
                @else
                    <span>Selamat! Anda telah menikmati keuntungan poin tertinggi.</span>
                @endif
            </div>
        </section>

        <!-- Menu Pintas Cepat (Quick Access Grid) -->
        <div class="grid grid-cols-2 gap-3">
            <a
                href="{{ route('customer.rewards.index') }}"
                class="flex flex-col justify-between rounded-lg border border-border bg-card p-3.5 text-card-foreground hover:border-primary/50 transition-colors shadow-xs"
            >
                <div class="flex items-center justify-between">
                    <div class="flex size-8 items-center justify-center rounded-md bg-primary/10 text-primary">
                        <x-lucide-ticket class="size-4" />
                    </div>
                </div>
                <div class="mt-3">
                    <h2 class="font-serif text-sm font-semibold text-foreground">Katalog Reward</h2>
                    <p class="text-[11px] text-muted-foreground">Tukar poin jadi voucer</p>
                </div>
            </a>

            <a
                href="{{ route('customer.vouchers.index') }}"
                class="flex flex-col justify-between rounded-lg border border-border bg-card p-3.5 text-card-foreground hover:border-primary/50 transition-colors shadow-xs"
            >
                <div class="flex items-center justify-between">
                    <div class="flex size-8 items-center justify-center rounded-md bg-secondary text-foreground">
                        <x-lucide-tag class="size-4" />
                    </div>
                    @if ($activeVouchersCount > 0)
                        <span class="rounded-full bg-primary px-2 py-0.5 text-[10px] font-bold text-primary-foreground">
                            {{ $activeVouchersCount }}
                        </span>
                    @endif
                </div>
                <div class="mt-3">
                    <h2 class="font-serif text-sm font-semibold text-foreground">Kupon Saya</h2>
                    <p class="text-[11px] text-muted-foreground">{{ $activeVouchersCount }} voucer aktif</p>
                </div>
            </a>

            <a
                href="{{ route('customer.history.index') }}"
                class="flex flex-col justify-between rounded-lg border border-border bg-card p-3.5 text-card-foreground hover:border-primary/50 transition-colors shadow-xs"
            >
                <div class="flex items-center justify-between">
                    <div class="flex size-8 items-center justify-center rounded-md bg-secondary text-foreground">
                        <x-lucide-bar-chart-3 class="size-4" />
                    </div>
                </div>
                <div class="mt-3">
                    <h2 class="font-serif text-sm font-semibold text-foreground">Riwayat Belanja</h2>
                    <p class="text-[11px] text-muted-foreground">Struk digital E-Receipt</p>
                </div>
            </a>

            <a
                href="{{ route('customer.pin.show') }}"
                class="flex flex-col justify-between rounded-lg border border-border bg-card p-3.5 text-card-foreground hover:border-primary/50 transition-colors shadow-xs"
            >
                <div class="flex items-center justify-between">
                    <div class="flex size-8 items-center justify-center rounded-md bg-secondary text-foreground">
                        <x-lucide-user-check class="size-4" />
                    </div>
                    @if (! $member->hasPin())
                        <span class="rounded bg-destructive/15 px-1.5 py-0.5 text-[10px] font-medium text-destructive">
                            Belum set
                        </span>
                    @endif
                </div>
                <div class="mt-3">
                    <h2 class="font-serif text-sm font-semibold text-foreground">PIN Keamanan</h2>
                    <p class="text-[11px] text-muted-foreground">Lindungi saldo poin</p>
                </div>
            </a>
        </div>

        <!-- Transaksi Terakhir -->
        <section aria-labelledby="recent-heading" class="rounded-lg border border-border bg-card p-4 text-card-foreground shadow-xs">
            <div class="flex items-center justify-between border-b border-border pb-2.5">
                <h2 id="recent-heading" class="font-serif text-sm font-semibold text-foreground">Transaksi Terakhir</h2>
                <a href="{{ route('customer.history.index') }}" class="text-[11px] text-primary hover:underline">Lihat semua</a>
            </div>

            @if ($recentOrders->isEmpty())
                <p class="py-6 text-center text-xs text-muted-foreground">
                    Belum ada riwayat transaksi. Tunjukkan nomor WhatsApp ke kasir saat memesan!
                </p>
            @else
                <ul class="divide-y divide-border/60">
                    @foreach ($recentOrders as $order)
                        <li class="flex items-center justify-between py-2.5 text-xs">
                            <div>
                                <a href="{{ route('customer.receipt', $order) }}" class="font-semibold text-foreground hover:underline">
                                    {{ $order->number }}
                                </a>
                                <p class="text-[11px] text-muted-foreground">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-foreground tabular-nums">{{ Rupiah::format($order->total) }}</p>
                                <p class="text-[11px] text-primary font-medium tabular-nums">+{{ number_format($order->points_earned) }} poin</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.customer>
