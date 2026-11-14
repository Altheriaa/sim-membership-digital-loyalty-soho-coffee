@use('App\Support\Rupiah')

@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer title="Katalog Reward">
    <div class="flex flex-col gap-4">
        {{-- Top Summary Strip --}}
        <header class="flex items-end justify-between gap-3 border-b border-border pb-3.5">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-foreground">Katalog Reward</h1>
                <p class="mt-0.5 text-xs text-muted-foreground">Tukarkan saldo poin Anda menjadi kupon kasir.</p>
            </div>
            <div class="shrink-0 rounded-xl bg-[#1F1D19] px-3.5 py-2 text-right text-[#F5F4EF]">
                <span class="block text-[10px] text-[#D8D6CF]">Saldo Poin</span>
                <p class="font-mono text-base font-extrabold text-[#F5B60A] tabular-nums">
                    {{ number_format($member->points_balance) }} <span class="font-sans text-[11px] font-semibold text-[#D8D6CF]">poin</span>
                </p>
            </div>
        </header>

        @error('claim')
            <div role="alert" class="rounded-xl border border-destructive/40 bg-destructive/10 px-3.5 py-2.5 text-xs font-semibold text-destructive">
                {{ $message }}
            </div>
        @enderror

        @if ($vouchers->isEmpty())
            <div class="rounded-xl border border-dashed border-input bg-card p-6 text-xs leading-relaxed text-muted-foreground">
                Belum ada voucer promosi yang dibuka saat ini. Poin dari setiap transaksi Anda di kasir tetap tersimpan dan bisa ditukar begitu reward baru tersedia.
            </div>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($vouchers as $voucher)
                    @php
                        $canClaim = $member->points_balance >= $voucher->points_cost && ($voucher->quota === null || $voucher->quota > 0);
                        $shortfall = max(0, $voucher->points_cost - $member->points_balance);
                    @endphp
                    <article class="overflow-hidden rounded-xl border border-border bg-card text-card-foreground">
                        <div class="flex items-stretch">
                            {{-- Left Ticket Stub with Point Cost --}}
                            <div class="flex w-24 shrink-0 flex-col items-center justify-center border-r-2 border-dashed border-border bg-[#1F1D19] px-2.5 py-4 text-center text-[#F5F4EF]">
                                <span class="text-[10px] font-semibold text-[#D8D6CF]">Tukar</span>
                                <span class="mt-0.5 font-mono text-2xl font-extrabold text-[#F5B60A] tabular-nums">
                                    {{ number_format($voucher->points_cost) }}
                                </span>
                                <span class="text-[10px] font-semibold text-[#D8D6CF]">poin</span>
                            </div>

                            {{-- Right Voucher Details --}}
                            <div class="flex flex-1 flex-col justify-between gap-3 p-3.5">
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-1.5">
                                        <span class="rounded-md bg-secondary px-2 py-0.5 text-[10px] font-bold text-secondary-foreground">
                                            {{ $voucher->type->label() }}
                                        </span>
                                        <span class="font-mono text-[11px] text-muted-foreground tabular-nums">
                                            Berlaku {{ $voucher->valid_days ?? 30 }} hari
                                        </span>
                                    </div>

                                    <h2 class="mt-1.5 text-base font-extrabold tracking-tight text-foreground">{{ $voucher->name }}</h2>

                                    @if ($voucher->menuItem)
                                        <p class="mt-0.5 text-xs text-muted-foreground">
                                            Gratis 1 porsi <strong class="font-semibold text-foreground">{{ $voucher->menuItem->name }}</strong>
                                        </p>
                                    @elseif ($voucher->discount_amount > 0)
                                        <p class="mt-0.5 text-xs text-muted-foreground">
                                            Potongan harga <strong class="font-mono font-semibold text-foreground">{{ Rupiah::format($voucher->discount_amount) }}</strong>
                                        </p>
                                    @endif

                                    @if ($voucher->quota !== null)
                                        <p class="mt-1 font-mono text-[11px] font-semibold text-muted-foreground tabular-nums">
                                            Sisa kuota: {{ $voucher->quota }} kupon
                                        </p>
                                    @endif
                                </div>

                                <div>
                                    @if ($canClaim)
                                        <form method="POST" action="{{ route('customer.rewards.claim', $voucher) }}"
                                            onsubmit="return confirm('Tukarkan {{ number_format($voucher->points_cost) }} poin untuk voucer {{ $voucher->name }}?');">
                                            @csrf
                                            <button type="submit"
                                                class="flex min-h-11 w-full items-center justify-center rounded-lg border border-foreground bg-primary px-4 text-xs font-extrabold text-primary-foreground transition-colors hover:bg-primary/90 dark:border-primary {{ $focus }}">
                                                Tukarkan {{ number_format($voucher->points_cost) }} Poin
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" disabled
                                            class="flex min-h-11 w-full cursor-not-allowed items-center justify-center rounded-lg border border-border bg-secondary/70 px-3 text-xs font-semibold text-muted-foreground">
                                            @if ($voucher->quota !== null && $voucher->quota <= 0)
                                                Kuota Habis
                                            @else
                                                Kurang {{ number_format($shortfall) }} poin lagi
                                            @endif
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.customer>
