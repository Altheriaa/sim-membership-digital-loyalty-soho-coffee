<x-layouts.customer title="Kupon Saya">
    <div class="flex flex-col gap-5">
        <header class="flex flex-col gap-1">
            <h1 class="font-serif text-xl font-bold tracking-tight text-foreground sm:text-2xl">Dompet Kupon Saya</h1>
            <p class="text-xs text-muted-foreground">Voucer digital yang telah Anda klaim dan siap digunakan di kasir.</p>
        </header>

        <!-- Filter Tab Aktif / Riwayat -->
        <div class="flex rounded-lg border border-border bg-card p-1 text-xs" role="tablist">
            <a
                href="{{ route('customer.vouchers.index', ['tab' => 'active']) }}"
                class="flex flex-1 items-center justify-center gap-1.5 rounded-md py-2 font-medium transition-colors {{ $tab === 'active' ? 'bg-primary text-primary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground' }}"
            >
                <span>Siap Dipakai</span>
                <span class="rounded-full px-1.5 py-0.2 text-[10px] {{ $tab === 'active' ? 'bg-primary-foreground/20 text-primary-foreground' : 'bg-secondary text-secondary-foreground' }}">{{ $activeCount }}</span>
            </a>
            <a
                href="{{ route('customer.vouchers.index', ['tab' => 'history']) }}"
                class="flex flex-1 items-center justify-center gap-1.5 rounded-md py-2 font-medium transition-colors {{ $tab === 'history' ? 'bg-primary text-primary-foreground font-semibold shadow-xs' : 'text-muted-foreground hover:text-foreground' }}"
            >
                <span>Riwayat / Terpakai</span>
                <span class="rounded-full px-1.5 py-0.2 text-[10px] {{ $tab === 'history' ? 'bg-primary-foreground/20 text-primary-foreground' : 'bg-secondary text-secondary-foreground' }}">{{ $historyCount }}</span>
            </a>
        </div>

        @if ($vouchers->isEmpty())
            <div class="rounded-lg border border-dashed border-border p-8 text-center text-xs text-muted-foreground flex flex-col items-center gap-3">
                <p>
                    {{ $tab === 'active' ? 'Anda belum memiliki kupon aktif saat ini.' : 'Belum ada riwayat kupon yang terpakai atau kedaluwarsa.' }}
                </p>
                @if ($tab === 'active')
                    <a href="{{ route('customer.rewards.index') }}" class="text-xs font-semibold text-primary underline underline-offset-4">
                        Buka Katalog Reward
                    </a>
                @endif
            </div>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($vouchers as $voucher)
                    <article class="flex flex-col justify-between gap-3 rounded-lg border border-border bg-card p-4 text-card-foreground shadow-xs">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="rounded bg-secondary px-2 py-0.5 text-[10px] font-medium text-secondary-foreground">
                                    {{ $voucher->voucher->type->label() }}
                                </span>
                                <h2 class="mt-1 font-serif text-base font-semibold leading-snug text-foreground">{{ $voucher->voucher->name }}</h2>
                            </div>

                            <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $voucher->statusLabel() === 'Siap Digunakan' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400' : 'bg-muted text-muted-foreground' }}">
                                {{ $voucher->statusLabel() }}
                            </span>
                        </div>

                        <!-- Barcode & Kode Klaim -->
                        <div class="flex items-center justify-between rounded-md border border-dashed border-border bg-background p-2.5">
                            <div>
                                <span class="text-[10px] text-muted-foreground">Kode Klaim:</span>
                                <p class="font-mono text-base font-bold tracking-widest text-primary">{{ $voucher->code }}</p>
                            </div>
                            <div class="text-right text-[11px]">
                                <span class="text-muted-foreground">Berlaku:</span>
                                <p class="font-semibold text-foreground">{{ $voucher->remainingTime() }}</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1 text-xs">
                            <span class="text-[11px] text-muted-foreground">
                                {{ $voucher->points_spent }} poin ditukarkan
                            </span>
                            <a href="{{ route('customer.vouchers.show', $voucher) }}" class="font-semibold text-primary hover:underline">
                                Lihat detail kupon
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <x-pagination :paginator="$vouchers" />
        @endif
    </div>
</x-layouts.customer>
