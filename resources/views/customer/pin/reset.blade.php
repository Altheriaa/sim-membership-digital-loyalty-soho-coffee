<x-layouts.customer title="Reset PIN Keamanan">
    <div class="flex flex-col gap-5">
        <header class="flex flex-col gap-1">
            <h1 class="font-serif text-xl font-bold tracking-tight text-foreground sm:text-2xl">Reset PIN Keamanan</h1>
            <p class="text-xs text-muted-foreground">
                Masukkan kode OTP 4-digit yang telah dikirim ke nomor WhatsApp Anda, lalu buat PIN 6-digit baru.
            </p>
        </header>

        <form method="POST" action="{{ route('customer.pin.reset.confirm') }}"
            class="flex flex-col gap-4 rounded-lg border border-border bg-card p-5 text-card-foreground shadow-xs">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="otp" class="text-xs font-semibold text-foreground">Kode OTP WhatsApp (4-Digit)</label>
                <input id="otp" type="text" name="otp" maxlength="4" placeholder="••••" required autofocus
                    class="h-11 w-full rounded-md border border-input bg-background text-center font-mono text-xl font-bold tracking-widest text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                @error('otp')
                    <p class="text-xs text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="pin" class="text-xs font-semibold text-foreground">PIN 6-Digit Baru</label>
                <input id="pin" type="password" name="pin" maxlength="6" inputmode="numeric" placeholder="••••••"
                    required
                    class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm font-mono tracking-widest text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                @error('pin')
                    <p class="text-xs text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="pin_confirmation" class="text-xs font-semibold text-foreground">Konfirmasi PIN Baru</label>
                <input id="pin_confirmation" type="password" name="pin_confirmation" maxlength="6" inputmode="numeric"
                    placeholder="••••••" required
                    class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm font-mono tracking-widest text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
            </div>

            <div class="mt-2 flex items-center justify-between gap-3">
                <a href="{{ route('customer.pin.show') }}" class="text-xs text-muted-foreground hover:underline">
                    Batal
                </a>
                <april:button type="submit" size="none"
                    class="flex h-10 px-4 items-center justify-center font-medium text-xs">
                    Reset dan Simpan PIN
                </april:button>
            </div>
        </form>
    </div>
</x-layouts.customer>