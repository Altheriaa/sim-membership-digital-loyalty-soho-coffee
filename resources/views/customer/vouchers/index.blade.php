@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer title="Kupon Saya">
    <div class="flex flex-col gap-4">
        <header class="flex flex-col gap-0.5">
            <h1 class="text-xl font-extrabold tracking-tight text-foreground">Dompet Kupon Saya</h1>
            <p class="text-xs text-muted-foreground">Tunjukkan kode klaim kupon di bawah kepada kasir saat membayar.</p>
        </header>

        {{-- Segmented Control: Siap Dipakai vs Riwayat --}}
        <div class="grid grid-cols-2 gap-1 rounded-xl border border-border bg-secondary p-1 text-xs" role="tablist">
            <a href="{{ route('customer.vouchers.index', ['tab' => 'active']) }}"
                class="flex min-h-11 items-center justify-center gap-1.5 rounded-lg px-3 py-1.5 transition-colors {{ $tab === 'active' ? 'bg-card font-bold text-foreground shadow-2xs' : 'font-medium text-muted-foreground hover:text-foreground' }} {{ $focus }}">
                <span>Siap Dipakai</span>
                <span class="rounded-md px-1.5 py-0.5 font-mono text-[10px] font-bold tabular-nums {{ $tab === 'active' ? 'bg-primary text-primary-foreground' : 'bg-background text-muted-foreground' }}">{{ $activeCount }}</span>
            </a>
            <a href="{{ route('customer.vouchers.index', ['tab' => 'history']) }}"
                class="flex min-h-11 items-center justify-center gap-1.5 rounded-lg px-3 py-1.5 transition-colors {{ $tab === 'history' ? 'bg-card font-bold text-foreground shadow-2xs' : 'font-medium text-muted-foreground hover:text-foreground' }} {{ $focus }}">
                <span>Riwayat</span>
                <span class="rounded-md px-1.5 py-0.5 font-mono text-[10px] font-bold tabular-nums {{ $tab === 'history' ? 'bg-primary text-primary-foreground' : 'bg-background text-muted-foreground' }}">{{ $historyCount }}</span>
            </a>
        </div>

        @if ($vouchers->isEmpty())
            <div class="flex flex-col items-start gap-3 rounded-xl border border-dashed border-input bg-card p-5 text-xs">
                <p class="leading-relaxed text-muted-foreground">
                    {{ $tab === 'active' ? 'Belum ada kupon aktif di dompet Anda saat ini. Tukarkan poin Anda di Katalog Reward untuk mendapatkan kupon potongan harga atau menu gratis.' : 'Belum ada riwayat kupon yang sudah dipakai atau kedaluwarsa.' }}
                </p>
                @if ($tab === 'active')
                    <a href="{{ route('customer.rewards.index') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-lg border border-foreground bg-primary px-4 text-xs font-extrabold text-primary-foreground transition-colors hover:bg-primary/90 dark:border-primary {{ $focus }}">
                        Buka Katalog Reward
                    </a>
                @endif
            </div>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($vouchers as $voucher)
                    <article class="overflow-hidden rounded-xl border border-border bg-card text-card-foreground">
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="rounded-md bg-secondary px-2 py-0.5 text-[10px] font-bold text-secondary-foreground">
                                        {{ $voucher->voucher->type->label() }}
                                    </span>
                                    <h2 class="mt-1.5 text-base font-extrabold tracking-tight text-foreground">{{ $voucher->voucher->name }}</h2>
                                </div>

                                <span class="rounded-md px-2 py-0.5 text-[10px] font-bold {{ $voucher->statusLabel() === 'Siap Digunakan' ? 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300' : 'bg-secondary text-muted-foreground' }}">
                                    {{ $voucher->statusLabel() }}
                                </span>
                            </div>

                            {{-- Cashier Claim Code Ticket Box --}}
                            <div class="mt-3 flex items-center justify-between rounded-lg border border-dashed border-input bg-background px-3.5 py-2.5">
                                <div>
                                    <span class="text-[10px] font-semibold text-muted-foreground">Kode Kasir</span>
                                    <p class="font-mono text-base font-extrabold tracking-widest text-foreground">{{ $voucher->code }}</p>
                                </div>
                                <div class="text-right text-[11px]">
                                    <span class="text-muted-foreground">Masa Berlaku</span>
                                    <p class="font-bold text-foreground">{{ $voucher->remainingTime() }}</p>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs">
                                <span class="font-mono text-[11px] text-muted-foreground tabular-nums">
                                    Ditukar {{ $voucher->points_spent }} poin
                                </span>
                                <a href="{{ route('customer.vouchers.show', $voucher) }}"
                                    class="rounded-md px-1 py-0.5 font-bold text-foreground underline underline-offset-4 hover:text-muted-foreground {{ $focus }}">
                                    Buka kupon &rsaquo;
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <x-pagination :paginator="$vouchers" />
        @endif
    </div>
</x-layouts.customer>
