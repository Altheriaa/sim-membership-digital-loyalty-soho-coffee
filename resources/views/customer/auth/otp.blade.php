<x-layouts.customer title="Verifikasi OTP" :show-nav="false">
    <div class="flex flex-col gap-6 pt-6">
        <header class="flex flex-col gap-1.5 text-center">
            <h1 class="font-serif text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Verifikasi OTP</h1>
            <p class="text-xs text-muted-foreground">
                Ketik 4-digit kode rahasia yang telah dikirim ke nomor WhatsApp <span
                    class="font-bold text-foreground">{{ $phone }}</span>.
            </p>
        </header>

        <form method="POST" action="{{ route('customer.verify-otp') }}"
            class="flex flex-col gap-5 rounded-lg border border-border bg-card p-5 text-card-foreground shadow-xs">
            @csrf

            <div class="flex flex-col items-center gap-2">
                <label for="otp-input" class="text-xs font-semibold text-foreground">Kode OTP 4-Digit</label>
                <input id="otp-input" type="text" name="otp" maxlength="4" placeholder="••••" required autofocus
                    autocomplete="one-time-code"
                    class="h-14 w-44 rounded-md border border-input bg-background text-center font-mono text-2xl font-bold tracking-[0.5em] text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                @error('otp')
                    <p class="text-xs text-destructive text-center">{{ $message }}</p>
                @enderror
            </div>

            <april:button type="submit" size="none"
                class="flex h-11 w-full items-center justify-center font-medium text-xs">
                Verifikasi dan Masuk
            </april:button>

            <div
                class="flex items-center justify-between border-t border-border/80 pt-3 text-[11px] text-muted-foreground">
                <a href="{{ route('customer.login') }}" class="hover:text-foreground underline underline-offset-2">
                    Ganti nomor HP
                </a>
                <span>Masa aktif 5 menit</span>
            </div>
        </form>

        <!-- Kirim Ulang OTP WhatsApp -->
        <div class="flex flex-col items-center gap-2 rounded-lg border border-dashed border-border/80 bg-muted/30 p-4 text-center text-xs"
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
            <p class="text-muted-foreground">Tidak menerima kode di WhatsApp?</p>

            <div x-show="countdown > 0" class="flex items-center gap-1.5 font-medium text-muted-foreground">
                <x-lucide-clock class="size-3.5 text-primary animate-pulse shrink-0" />
                <span>Kirim ulang dalam <span class="font-mono font-bold text-foreground"
                        x-text="countdown + ' detik'"></span></span>
            </div>

            <form x-show="countdown <= 0" method="POST" action="{{ route('customer.resend-otp') }}">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 font-semibold text-primary hover:underline hover:text-primary/90 transition-colors">
                    <x-lucide-refresh-cw class="size-3.5 shrink-0" />
                    <span>Kirim Ulang Kode OTP</span>
                </button>
            </form>
        </div>
    </div>
</x-layouts.customer>