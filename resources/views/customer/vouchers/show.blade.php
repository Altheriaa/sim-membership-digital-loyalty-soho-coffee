@use('App\Support\Rupiah')

<x-layouts.customer :title="'Kupon · ' . $voucher->code">
    <div class="flex flex-col gap-5">
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.vouchers.index') }}" class="text-xs text-muted-foreground hover:text-foreground">
                Kembali ke Kupon Saya
            </a>
            <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $voucher->statusLabel() === 'Siap Digunakan' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-400' : 'bg-muted text-muted-foreground' }}">
                {{ $voucher->statusLabel() }}
            </span>
        </div>

        <!-- Tiket Digital Kupon -->
        <article class="relative overflow-hidden rounded-xl border border-border bg-card p-6 text-card-foreground shadow-md flex flex-col gap-5">
            <div class="flex flex-col items-center text-center gap-1 border-b border-dashed border-border pb-4">
                <span class="rounded-full bg-secondary px-2.5 py-0.5 text-[10px] font-semibold text-secondary-foreground">
                    {{ $voucher->voucher->type->label() }}
                </span>
                <h1 class="mt-2 font-serif text-xl font-bold tracking-tight text-foreground sm:text-2xl">
                    {{ $voucher->voucher->name }}
                </h1>
                @if ($voucher->voucher->menuItem)
                    <p class="text-xs text-muted-foreground">Berlaku untuk 1 porsi <strong class="text-foreground">{{ $voucher->voucher->menuItem->name }}</strong></p>
                @elseif ($voucher->voucher->discount_amount > 0)
                    <p class="text-xs text-muted-foreground">Diskon potongan harga senilai <strong class="text-foreground">{{ Rupiah::format($voucher->voucher->discount_amount) }}</strong></p>
                @endif
            </div>

            <!-- Kode Klaim Besar -->
            <div class="flex flex-col items-center justify-center gap-2 rounded-lg bg-background p-5 border border-border">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Kode Klaim Kasir</span>
                <p class="font-mono text-3xl font-extrabold tracking-widest text-primary">{{ $voucher->code }}</p>

                <!-- Barcode Simulation -->
                <div class="mt-2 flex h-10 w-48 items-center justify-center gap-1" aria-hidden="true">
                    @for ($i = 0; $i < 30; $i++)
                        <div class="h-full bg-foreground" style="width: {{ ($i % 3 === 0) ? '3px' : (($i % 2 === 0) ? '1.5px' : '2px') }}"></div>
                    @endfor
                </div>
                <span class="text-[10px] text-muted-foreground">Scan / ketik kode ini di mesin kasir</span>
            </div>

            <!-- Info Kedaluwarsa & Waktu -->
            <div class="flex flex-col gap-2 border-t border-dashed border-border pt-3 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-muted-foreground">Sisa Waktu:</span>
                    <span class="font-semibold text-foreground">{{ $voucher->remainingTime() }}</span>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-muted-foreground">Berlaku Sampai:</span>
                    <span class="tabular-nums text-foreground">{{ $voucher->expires_at->translatedFormat('d F Y, H:i') }}</span>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-muted-foreground">Poin Terpakai:</span>
                    <span class="tabular-nums text-primary font-medium">{{ $voucher->points_spent }} poin</span>
                </div>
                @if ($voucher->status === 'used' && $voucher->order)
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="text-muted-foreground">Digunakan Pada Transaksi:</span>
                        <a href="{{ route('customer.receipt', $voucher->order) }}" class="font-mono text-primary underline">
                            {{ $voucher->order->number }}
                        </a>
                    </div>
                @endif
            </div>

            <!-- Petunjuk Pemakaian -->
            <div class="rounded bg-secondary/50 p-3 text-[11px] text-muted-foreground">
                <p class="font-semibold text-foreground">Syarat & Ketentuan:</p>
                <ul class="mt-1 list-disc list-inside space-y-0.5">
                    <li>Kupon hanya dapat digunakan 1 kali di kasir cafe.</li>
                    <li>Wajib memasukkan 6-digit PIN Keamanan saat kasir memproses voucer.</li>
                    <li>Kupon yang kedaluwarsa tidak dapat ditukarkan kembali menjadi poin.</li>
                </ul>
            </div>
        </article>
    </div>
</x-layouts.customer>
