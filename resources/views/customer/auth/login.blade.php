<x-layouts.customer title="Masuk Member" :show-nav="false">
    <div class="flex flex-col gap-6 pt-6">
        <header class="flex flex-col gap-1.5 text-center">
            <h1 class="font-serif text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Selamat Datang</h1>
            <p class="text-xs text-muted-foreground">
                Masuk atau daftar keanggotaan digital cafe cukup dengan nomor WhatsApp aktif Anda tanpa perlu menghafal kata sandi.
            </p>
        </header>

        <form method="POST" action="{{ route('customer.send-otp') }}" class="flex flex-col gap-4 rounded-lg border border-border bg-card p-5 text-card-foreground shadow-xs">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="phone-input" class="text-xs font-semibold text-foreground">Nomor WhatsApp</label>
                <input
                    id="phone-input"
                    type="tel"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="Contoh: 0812 3456 7890"
                    autocomplete="tel"
                    required
                    autofocus
                    class="h-11 w-full rounded-md border border-input bg-background px-3 text-sm tabular-nums text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                >
                @error('phone')
                    <p class="text-xs text-destructive">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-muted-foreground">
                    Kode verifikasi 4-digit akan dikirimkan langsung ke nomor WhatsApp Anda.
                </p>
            </div>

            <april:button type="submit" size="none" class="mt-2 flex h-11 w-full items-center justify-center font-medium text-xs">
                Kirim Kode OTP
            </april:button>
        </form>

        <div class="flex flex-col items-center justify-center gap-1 text-center text-xs text-muted-foreground">
            <p>Belum pernah terdaftar sebelumnya?</p>
            <p class="text-[11px]">Akun akan dibuatkan secara otomatis begitu nomor diverifikasi.</p>
        </div>
    </div>
</x-layouts.customer>
