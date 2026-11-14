@use('App\Support\Rupiah')

@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer :title="'Kupon · ' . $voucher->code">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.vouchers.index') }}"
                class="inline-flex min-h-11 items-center rounded-lg border border-border bg-card px-3.5 text-xs font-bold text-foreground transition-colors hover:bg-accent {{ $focus }}">
                &lsaquo; Dompet Kupon
            </a>
            <span class="rounded-md px-2.5 py-1 text-[11px] font-bold {{ $voucher->statusLabel() === 'Siap Digunakan' ? 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300' : 'bg-secondary text-muted-foreground' }}">
                {{ $voucher->statusLabel() }}
            </span>
        </div>

        {{-- Perforated Voucher Pass --}}
        <article class="relative overflow-hidden rounded-2xl border border-border bg-card text-card-foreground">
            <div class="p-5 text-center">
                <span class="inline-block rounded-md bg-secondary px-2.5 py-0.5 text-[10px] font-bold text-secondary-foreground">
                    {{ $voucher->voucher->type->label() }}
                </span>
                <h1 class="mt-2 text-xl font-extrabold tracking-tight text-foreground">
                    {{ $voucher->voucher->name }}
                </h1>
                @if ($voucher->voucher->menuItem)
                    <p class="mt-1 text-xs text-muted-foreground">Gratis 1 porsi <strong class="font-bold text-foreground">{{ $voucher->voucher->menuItem->name }}</strong></p>
                @elseif ($voucher->voucher->discount_amount > 0)
                    <p class="mt-1 text-xs text-muted-foreground">Potongan harga <strong class="font-mono font-bold text-foreground">{{ Rupiah::format($voucher->voucher->discount_amount) }}</strong></p>
                @endif

                {{-- High-Contrast Claim Code Box --}}
                <div class="mt-4 rounded-xl bg-[#1F1D19] p-5 text-[#F5F4EF]">
                    <span class="text-[11px] font-semibold text-[#D8D6CF]">Sebutkan atau tunjukkan kode ini di kasir</span>
                    <p class="mt-1 font-mono text-3xl font-extrabold tracking-widest text-[#F5B60A]">{{ $voucher->code }}</p>
                </div>
            </div>

            {{-- Perforated Tear Line --}}
            <div class="relative flex items-center justify-between" aria-hidden="true">
                <span class="-ml-3 size-6 rounded-full bg-background"></span>
                <span class="w-full border-t-2 border-dashed border-border"></span>
                <span class="-mr-3 size-6 rounded-full bg-background"></span>
            </div>

            <div class="flex flex-col gap-2.5 p-5 text-xs">
                <div class="leader-row">
                    <span class="text-muted-foreground">Sisa Waktu</span>
                    <span class="leader-fill" aria-hidden="true"></span>
                    <span class="font-bold text-foreground">{{ $voucher->remainingTime() }}</span>
                </div>
                <div class="leader-row">
                    <span class="text-muted-foreground">Berlaku Sampai</span>
                    <span class="leader-fill" aria-hidden="true"></span>
                    <span class="font-mono text-foreground tabular-nums">{{ $voucher->expires_at->translatedFormat('d M Y, H:i') }}</span>
                </div>
                <div class="leader-row">
                    <span class="text-muted-foreground">Poin Ditukarkan</span>
                    <span class="leader-fill" aria-hidden="true"></span>
                    <span class="font-mono font-bold text-foreground tabular-nums">{{ $voucher->points_spent }} poin</span>
                </div>
                @if ($voucher->status === 'used' && $voucher->order)
                    <div class="leader-row">
                        <span class="text-muted-foreground">Dipakai pada Struk</span>
                        <span class="leader-fill" aria-hidden="true"></span>
                        <a href="{{ route('customer.receipt', $voucher->order) }}" class="font-mono font-bold text-foreground underline">
                            {{ $voucher->order->number }}
                        </a>
                    </div>
                @endif

                <div class="mt-2 rounded-lg bg-secondary/70 p-3 text-[11px] leading-relaxed text-muted-foreground">
                    <p class="font-bold text-foreground">Ketentuan Pemakaian di Kasir:</p>
                    <p class="mt-0.5">Kupon berlaku untuk 1 kali transaksi dan memerlukan 6-digit PIN Keamanan Anda saat kasir memasukkan kode klaim.</p>
                </div>
            </div>
        </article>
    </div>
</x-layouts.customer>
