@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer title="Verifikasi OTP" :show-nav="false">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.login') }}"
                class="inline-flex min-h-11 items-center rounded-lg border border-border bg-card px-3.5 text-xs font-bold text-foreground transition-colors hover:bg-accent {{ $focus }}">
                &lsaquo; Ganti Nomor
            </a>
            <span class="rounded-md bg-secondary px-2.5 py-1 text-[10px] font-bold text-secondary-foreground">
                Masa aktif 5 menit
            </span>
        </div>

        <header class="flex flex-col gap-0.5">
            <h1 class="text-xl font-extrabold tracking-tight text-foreground">Verifikasi OTP</h1>
            <p class="text-xs leading-relaxed text-muted-foreground">
                Masukkan 4-digit kode rahasia yang dikirimkan ke WhatsApp Anda.
            </p>
        </header>

        {{-- Perforated OTP Verification Pass --}}
        <article class="relative overflow-hidden rounded-2xl border border-border bg-card text-card-foreground">
            <div class="bg-[#1F1D19] px-5 py-4 text-center text-[#F5F4EF]">
                <span class="text-[11px] font-semibold text-[#D8D6CF]">Kode dikirim ke WhatsApp</span>
                <p class="mt-0.5 font-mono text-base font-extrabold tracking-wide text-[#F5B60A] tabular-nums">
                    {{ $phone }}</p>
            </div>

            {{-- Perforated Tear Line --}}
            <div class="relative flex items-center justify-between" aria-hidden="true">
                <span class="-ml-3 size-6 rounded-full bg-background"></span>
                <span class="w-full border-t-2 border-dashed border-border"></span>
                <span class="-mr-3 size-6 rounded-full bg-background"></span>
            </div>

            <form method="POST" action="{{ route('customer.verify-otp') }}" class="flex flex-col gap-4 p-5">
                @csrf

                <div class="flex flex-col items-center gap-2">
                    <label for="otp-input" class="text-xs font-bold text-foreground">Kode OTP 4-Digit</label>
                    <input id="otp-input" type="text" name="otp" maxlength="4" inputmode="numeric" pattern="[0-9]*"
                        placeholder="••••" required autofocus autocomplete="one-time-code"
                        class="h-14 w-48 rounded-lg border border-input bg-background text-center font-mono text-2xl font-extrabold tracking-[0.5em] text-foreground tabular-nums {{ $focus }}">
                    @error('otp')
                        <p class="text-center text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="flex min-h-11 w-full items-center justify-center rounded-lg border border-foreground bg-primary px-4 text-xs font-extrabold text-primary-foreground transition-colors hover:bg-primary/90 dark:border-primary {{ $focus }}">
                    Verifikasi dan Masuk
                </button>

                <div class="leader-row border-t border-dashed border-border pt-3 text-[11px]">
                    <span class="text-muted-foreground">Batas waktu kode</span>
                    <span class="leader-fill" aria-hidden="true"></span>
                    <span class="font-bold text-foreground">Masa aktif 1 menit</span>
                </div>
            </form>
        </article>

        {{-- Resend OTP Box --}}
        <div class="flex flex-col items-center gap-2.5 rounded-xl border border-dashed border-input bg-card p-4 text-center text-xs"
            x-data="{
                countdown: {{ $countdown ?? 0 }},
                timer: null,
                init() {
                    if (this.countdown > 0) {
                        this.timer = setInterval(() => {
                            this.countdown--;
                            if (this.countdown <= 0) {
                                clearInterval(this.timer);
                            }
                        }, 1000);
                    }
                }
            }">
            <p class="font-semibold text-muted-foreground">Tidak menerima kode di WhatsApp?</p>

            <p x-show="countdown > 0" class="text-xs text-muted-foreground">
                Kirim ulang tersedia dalam <span class="font-mono font-extrabold text-foreground tabular-nums"
                    x-text="countdown + ' detik'"></span>
            </p>

            <form x-show="countdown <= 0" method="POST" action="{{ route('customer.resend-otp') }}"
                class="w-full sm:w-auto">
                @csrf
                <button type="submit"
                    class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-border bg-background px-4 text-xs font-bold text-foreground transition-colors hover:bg-accent sm:w-auto {{ $focus }}">
                    Kirim Ulang Kode OTP
                </button>
            </form>
        </div>
    </div>
</x-layouts.customer>