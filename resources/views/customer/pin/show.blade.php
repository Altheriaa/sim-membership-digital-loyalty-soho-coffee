<x-layouts.customer title="PIN Keamanan 6-Digit">
    <div class="flex flex-col gap-5">
        <header class="flex flex-col gap-1">
            <h1 class="font-serif text-xl font-bold tracking-tight text-foreground sm:text-2xl">Pengaturan PIN Keamanan</h1>
            <p class="text-xs text-muted-foreground">
                PIN 6-digit ini wajib dimasukkan di kasir saat Anda ingin menukarkan saldo poin agar poin tidak disalahgunakan orang lain.
            </p>
        </header>

        <!-- Status Card -->
        <div class="rounded-lg border border-border bg-card p-4 text-card-foreground shadow-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-full {{ $hasPin ? 'bg-primary/10 text-primary' : 'bg-destructive/10 text-destructive' }}">
                    <x-lucide-user-check class="size-5" />
                </div>
                <div>
                    <h2 class="text-xs font-semibold text-foreground">Status PIN</h2>
                    <p class="text-[11px] text-muted-foreground">
                        {{ $hasPin ? 'PIN 6-digit sudah terpasang dan aktif.' : 'Anda belum membuat PIN pengaman poin.' }}
                    </p>
                </div>
            </div>
            @if ($hasPin)
                <span class="rounded bg-emerald-500/15 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-400">
                    Aktif
                </span>
            @endif
        </div>

        <!-- Form Pembuatan / Pengubahan PIN -->
        <form method="POST" action="{{ route('customer.pin.update') }}" class="flex flex-col gap-4 rounded-lg border border-border bg-card p-5 text-card-foreground shadow-xs">
            @csrf

            @if ($hasPin)
                <div class="flex flex-col gap-1.5">
                    <label for="current_pin" class="text-xs font-semibold text-foreground">PIN Lama Saat Ini</label>
                    <input
                        id="current_pin"
                        type="password"
                        name="current_pin"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        placeholder="••••••"
                        required
                        class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm font-mono tracking-widest text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >
                    @error('current_pin')
                        <p class="text-xs text-destructive">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="flex flex-col gap-1.5">
                <label for="pin" class="text-xs font-semibold text-foreground">{{ $hasPin ? 'PIN Baru (6-Digit Angka)' : 'Buat PIN 6-Digit Angka' }}</label>
                <input
                    id="pin"
                    type="password"
                    name="pin"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    placeholder="••••••"
                    required
                    class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm font-mono tracking-widest text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                >
                @error('pin')
                    <p class="text-xs text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="pin_confirmation" class="text-xs font-semibold text-foreground">Ulangi PIN Baru</label>
                <input
                    id="pin_confirmation"
                    type="password"
                    name="pin_confirmation"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    placeholder="••••••"
                    required
                    class="h-10 w-full rounded-md border border-input bg-background px-3 text-sm font-mono tracking-widest text-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                >
            </div>

            <april:button type="submit" size="none" class="mt-2 flex h-10 w-full items-center justify-center font-medium text-xs">
                {{ $hasPin ? 'Simpan Perubahan PIN' : 'Buat PIN Sekarang' }}
            </april:button>
        </form>

        @if ($hasPin)
            <!-- Pilihan Reset PIN jika Lupa -->
            <div class="rounded-lg border border-dashed border-border p-4 text-center text-xs text-muted-foreground flex flex-col items-center gap-2">
                <p>Lupa dengan 6-digit PIN Keamanan Anda?</p>
                <form method="POST" action="{{ route('customer.pin.reset.request') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-primary hover:underline">
                        Minta Kode OTP WhatsApp untuk Reset PIN
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-layouts.customer>
