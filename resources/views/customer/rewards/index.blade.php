@use('App\Support\Rupiah')

<x-layouts.customer title="Katalog Reward & Klaim Voucer">
    <div class="flex flex-col gap-5">
        <!-- Header & Saldo Poin -->
        <header class="flex items-center justify-between border-b border-border pb-3">
            <div>
                <h1 class="font-serif text-xl font-bold tracking-tight text-foreground sm:text-2xl">Katalog Reward</h1>
                <p class="text-xs text-muted-foreground">Tukarkan poin belanja Anda menjadi voucer digital.</p>
            </div>
            <div class="rounded-lg border border-primary/20 bg-primary/5 px-3 py-1.5 text-right">
                <span class="text-[10px] text-muted-foreground">Saldo Anda</span>
                <p class="font-serif text-sm font-bold text-primary tabular-nums">{{ number_format($member->points_balance) }} poin</p>
            </div>
        </header>

        @error('claim')
            <div class="rounded-md border border-destructive/30 bg-destructive/10 px-3 py-2 text-xs text-destructive">
                {{ $message }}
            </div>
        @enderror

        <!-- Daftar Voucer & Promosi -->
        @if ($vouchers->isEmpty())
            <div class="rounded-lg border border-dashed border-border p-8 text-center text-xs text-muted-foreground">
                Saat ini belum ada voucer promosi yang tersedia untuk ditukarkan. Silakan cek kembali nanti!
            </div>
        @else
            <div class="flex flex-col gap-3.5">
                @foreach ($vouchers as $voucher)
                    @php
                        $canClaim = $member->points_balance >= $voucher->points_cost && ($voucher->quota === null || $voucher->quota > 0);
                    @endphp
                    <article class="flex flex-col justify-between gap-3 rounded-lg border border-border bg-card p-4 text-card-foreground shadow-xs">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="rounded bg-secondary px-2 py-0.5 text-[10px] font-medium text-secondary-foreground">
                                    {{ $voucher->type->label() }}
                                </span>
                                <h2 class="mt-1.5 font-serif text-base font-semibold leading-snug text-foreground">{{ $voucher->name }}</h2>

                                @if ($voucher->menuItem)
                                    <p class="text-xs text-muted-foreground">Berlaku untuk menu: <strong class="text-foreground">{{ $voucher->menuItem->name }}</strong></p>
                                @elseif ($voucher->discount_amount > 0)
                                    <p class="text-xs text-muted-foreground">Potongan langsung senilai <strong class="text-foreground">{{ Rupiah::format($voucher->discount_amount) }}</strong></p>
                                @endif

                                <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground">
                                    <span>Masa berlaku {{ $voucher->valid_days ?? 30 }} hari</span>
                                    @if ($voucher->quota !== null)
                                        <span>· Sisa kuota: {{ $voucher->quota }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="font-serif text-lg font-bold text-primary tabular-nums">
                                    {{ number_format($voucher->points_cost) }}
                                </span>
                                <span class="block text-[10px] text-muted-foreground">poin</span>
                            </div>
                        </div>

                        <div class="border-t border-border/60 pt-3">
                            @if ($canClaim)
                                <form method="POST" action="{{ route('customer.rewards.claim', $voucher) }}" onsubmit="return confirm('Tukarkan {{ number_format($voucher->points_cost) }} poin untuk voucer {{ $voucher->name }}?');">
                                    @csrf
                                    <april:button type="submit" size="none" class="flex h-10 w-full items-center justify-center font-medium text-xs">
                                        Tukarkan {{ number_format($voucher->points_cost) }} Poin
                                    </april:button>
                                </form>
                            @else
                                <button
                                    type="button"
                                    disabled
                                    class="flex h-10 w-full cursor-not-allowed items-center justify-center rounded-md border border-border bg-muted/60 text-xs font-medium text-muted-foreground opacity-70"
                                >
                                    {{ $voucher->quota !== null && $voucher->quota <= 0 ? 'Kuota Habis' : 'Poin Belum Cukup' }}
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.customer>
