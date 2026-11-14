@use('App\Support\Rupiah')

@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer title="Kartu Member">
    <div class="flex flex-col gap-5">
        {{-- Unified Perforated Coffee-House Pass (Focal Point) --}}
        <article aria-label="Kartu member digital" class="relative overflow-hidden rounded-2xl border border-foreground/15 bg-[#1F1D19] text-[#F5F4EF] shadow-sm">
            {{-- Upper Pass: Identity + Points Balance --}}
            <div class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-mono text-[11px] font-semibold text-[#D8D6CF]">
                            #MBR-{{ str_pad((string) $member->id, 5, '0', STR_PAD_LEFT) }}
                        </p>
                        <h1 class="mt-0.5 truncate text-xl font-extrabold tracking-tight text-[#F5F4EF]">
                            {{ $member->name }}
                        </h1>
                        <p class="font-mono text-xs text-[#D8D6CF] tabular-nums">{{ $member->displayPhone() }}</p>
                    </div>

                    <div class="shrink-0 text-right">
                        @if ($tier)
                            <span class="inline-block rounded-md bg-[#F5B60A] px-2.5 py-1 text-xs font-extrabold text-[#1D1B16]">
                                Tier {{ $tier->name }}
                            </span>
                            <p class="mt-1 font-mono text-[11px] font-semibold text-[#D8D6CF] tabular-nums">
                                {{ $tier->multiplierLabel() }}x poin
                            </p>
                        @else
                            <span class="inline-block rounded-md border border-[#F5F4EF]/20 bg-[#2B2721] px-2.5 py-1 text-xs font-semibold text-[#F5F4EF]">
                                Member
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex items-baseline justify-between gap-4">
                    <div>
                        <span class="text-xs text-[#D8D6CF]">Saldo Poin Aktif</span>
                        <p class="mt-0.5 font-mono text-4xl font-extrabold tracking-tight text-[#F5B60A] tabular-nums">
                            {{ number_format($member->points_balance) }}<span class="ml-1.5 font-sans text-sm font-semibold text-[#D8D6CF]">poin</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-[#D8D6CF]">Nilai Tukar</span>
                        <p class="mt-0.5 font-mono text-base font-bold text-[#F5F4EF] tabular-nums">
                            {{ Rupiah::format($member->points_balance * $settings->point_value) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Perforated Tear Line with Physical Cutout Notches --}}
            <div class="relative flex items-center justify-between" aria-hidden="true">
                <span class="-ml-3 size-6 rounded-full bg-background"></span>
                <span class="w-full border-t-2 border-dashed border-[#F5F4EF]/20"></span>
                <span class="-mr-3 size-6 rounded-full bg-background"></span>
            </div>

            {{-- Lower Pass Stub: Tier Progress & Cashier Instruction --}}
            <div class="bg-[#27241F] px-5 pt-3.5 pb-4">
                <div class="flex items-center justify-between text-xs">
                    @if ($nextTier)
                        <span class="text-[#D8D6CF]">Menuju <strong class="font-bold text-[#F5F4EF]">{{ $nextTier->name }}</strong></span>
                        <span class="font-mono font-bold text-[#F5B60A] tabular-nums">{{ $progressPct }}%</span>
                    @else
                        <span class="font-bold text-[#F5B60A]">Tingkat Member Tertinggi</span>
                        <span class="font-mono font-bold text-[#F5F4EF] tabular-nums">100%</span>
                    @endif
                </div>

                <div class="mt-2 h-2 w-full overflow-hidden rounded-sm bg-[#1F1D19]">
                    <div class="h-full bg-[#F5B60A] transition-all duration-500" style="width: {{ $progressPct }}%"></div>
                </div>

                <div class="mt-2.5 flex items-center justify-between text-[11px] text-[#D8D6CF]">
                    @if ($nextTier)
                        <span>Kurang {{ Rupiah::format($spendToNext) }} belanja lagi</span>
                    @else
                        <span>Pengganda poin maksimal aktif</span>
                    @endif
                    <span class="font-semibold text-[#F5F4EF]">Sebutkan no. WA di kasir</span>
                </div>
            </div>
        </article>

        {{-- Primary Action Split: Reward Redemption vs Active Voucher Wallet (No generic icon boxes) --}}
        <div class="grid grid-cols-5 gap-2.5">
            <a href="{{ route('customer.rewards.index') }}"
                class="col-span-3 flex flex-col justify-between rounded-xl border border-foreground bg-primary p-4 text-primary-foreground transition-colors hover:bg-primary/90 dark:border-primary {{ $focus }}">
                <span class="text-[11px] font-bold opacity-85">Katalog Reward</span>
                <div class="mt-3">
                    <p class="text-base font-extrabold tracking-tight leading-snug">Tukar Poin Jadi Voucher</p>
                    <p class="mt-1 text-xs font-semibold underline underline-offset-4">Pilih reward sekarang</p>
                </div>
            </a>

            <a href="{{ route('customer.vouchers.index') }}"
                class="col-span-2 flex flex-col justify-between rounded-xl border border-border bg-card p-4 text-card-foreground transition-colors hover:border-foreground {{ $focus }}">
                <span class="text-[11px] font-semibold text-muted-foreground">Kupon Saya</span>
                <div class="mt-2">
                    <p class="font-mono text-2xl font-extrabold tracking-tight text-foreground tabular-nums">
                        {{ $activeVouchersCount }}
                    </p>
                    <p class="mt-0.5 text-xs font-semibold text-muted-foreground">siap dipakai</p>
                </div>
            </a>
        </div>

        {{-- iOS Inset Grouped List for Account & Menu Navigation --}}
        <section aria-label="Menu akun dan keamanan" class="overflow-hidden rounded-xl border border-border bg-card">
            <ul class="divide-y divide-border text-xs">
                <li>
                    <a href="{{ route('customer.history.index') }}"
                        class="flex min-h-13 items-center justify-between gap-3 px-4 py-3 transition-colors hover:bg-accent/50 {{ $focus }}">
                        <div>
                            <p class="font-bold text-foreground">Riwayat Belanja & Mutasi Poin</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Lihat struk digital (E-Receipt) dan keluar-masuk poin</p>
                        </div>
                        <span class="shrink-0 font-mono text-xs font-bold text-muted-foreground" aria-hidden="true">&rsaquo;</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer.pin.show') }}"
                        class="flex min-h-13 items-center justify-between gap-3 px-4 py-3 transition-colors hover:bg-accent/50 {{ $focus }}">
                        <div>
                            <p class="font-bold text-foreground">PIN Keamanan 6-Digit</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Kunci pengaman saat menukar poin di kasir</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if ($member->hasPin())
                                <span class="rounded-md bg-secondary px-2 py-0.5 text-[10px] font-bold text-foreground">Aktif</span>
                            @else
                                <span class="rounded-md bg-destructive/15 px-2 py-0.5 text-[10px] font-bold text-destructive">Belum diatur</span>
                            @endif
                            <span class="font-mono text-xs font-bold text-muted-foreground" aria-hidden="true">&rsaquo;</span>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('home') }}#menu"
                        class="flex min-h-13 items-center justify-between gap-3 px-4 py-3 transition-colors hover:bg-accent/50 {{ $focus }}">
                        <div>
                            <p class="font-bold text-foreground">Lihat Menu & Harga Kedai</p>
                            <p class="mt-0.5 text-[11px] text-muted-foreground">Buka daftar menu kopi, non-kopi, dan camilan Soho</p>
                        </div>
                        <span class="shrink-0 font-mono text-xs font-bold text-muted-foreground" aria-hidden="true">&rsaquo;</span>
                    </a>
                </li>
            </ul>
        </section>

        {{-- Recent Receipt Ledger with Dotted Leaders --}}
        <section aria-labelledby="recent-heading" class="rounded-xl border border-border bg-card p-4 text-card-foreground">
            <div class="flex items-baseline justify-between pb-3">
                <h2 id="recent-heading" class="text-sm font-extrabold tracking-tight text-foreground">Struk Belanja Terakhir</h2>
                <a href="{{ route('customer.history.index') }}"
                    class="rounded-md px-1 py-0.5 text-xs font-semibold text-muted-foreground underline underline-offset-4 hover:text-foreground {{ $focus }}">
                    Semua struk
                </a>
            </div>

            @if ($recentOrders->isEmpty())
                <div class="rounded-lg border border-dashed border-input bg-background/60 p-4 text-xs leading-relaxed text-muted-foreground">
                    Belum ada struk tercatat di akun ini. Saat memesan di kasir, sebutkan nomor WhatsApp <strong class="font-mono text-foreground">{{ $member->displayPhone() }}</strong> agar struk dan poin masuk otomatis.
                </div>
            @else
                <ul class="divide-y divide-border/70 border-t border-border">
                    @foreach ($recentOrders as $order)
                        <li>
                            <a href="{{ route('customer.receipt', $order) }}"
                                class="block py-3 text-xs transition-colors hover:bg-accent/40 {{ $focus }}">
                                <div class="leader-row">
                                    <span class="font-mono font-bold text-foreground">{{ $order->number }}</span>
                                    <span class="leader-fill" aria-hidden="true"></span>
                                    <span class="font-mono font-extrabold text-foreground tabular-nums">{{ Rupiah::format($order->total) }}</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-[11px] text-muted-foreground">
                                    <span>{{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                                    <span class="font-mono font-bold text-foreground tabular-nums">+{{ number_format($order->points_earned) }} poin</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.customer>
