@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer title="PIN Keamanan 6-Digit">
    <div class="flex flex-col gap-4">
        <header class="flex flex-col gap-0.5">
            <h1 class="text-xl font-extrabold tracking-tight text-foreground">PIN Keamanan</h1>
            <p class="text-xs leading-relaxed text-muted-foreground">
                PIN 6-digit dipakai saat Anda menukarkan poin atau voucer di kasir supaya saldo poin tetap aman.
            </p>
        </header>

        @error('error')
            <div role="alert" class="rounded-xl border border-red-300 bg-red-100 px-3.5 py-2.5 text-xs font-semibold text-red-900 dark:border-red-800 dark:bg-red-950/70 dark:text-red-200">
                {{ $message }}
            </div>
        @enderror

        {{-- PIN Status Card --}}
        <section aria-label="Status PIN keamanan" class="flex items-center justify-between gap-3 rounded-xl border border-border bg-card p-4 text-card-foreground">
            <div class="min-w-0">
                <h2 class="text-xs font-extrabold text-foreground">Status PIN</h2>
                <p class="mt-0.5 text-[11px] leading-relaxed text-muted-foreground">
                    {{ $hasPin ? 'PIN 6-digit sudah aktif untuk transaksi di kasir.' : 'Anda belum membuat PIN pengaman poin.' }}
                </p>
            </div>
            @if ($hasPin)
                <span class="shrink-0 rounded-md bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-900 dark:bg-emerald-950/70 dark:text-emerald-200">
                    Aktif
                </span>
            @else
                <span class="shrink-0 rounded-md border border-red-300 bg-red-100 px-2.5 py-1 text-[10px] font-bold text-red-900 dark:border-red-800 dark:bg-red-950/70 dark:text-red-200">
                    Belum diatur
                </span>
            @endif
        </section>

        {{-- PIN Setup / Update Form --}}
        <form method="POST" action="{{ route('customer.pin.update') }}" class="flex flex-col gap-4 rounded-xl border border-border bg-card p-5 text-card-foreground">
            @csrf

            @if ($hasPin)
                <div class="flex flex-col gap-1.5">
                    <label for="current_pin" class="text-xs font-bold text-foreground">PIN Lama Saat Ini</label>
                    <input
                        id="current_pin"
                        type="password"
                        name="current_pin"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        autocomplete="current-password"
                        placeholder="••••••"
                        required
                        class="h-11 w-full rounded-lg border border-input bg-background px-3.5 font-mono text-sm tracking-widest text-foreground tabular-nums {{ $focus }}"
                    >
                    @error('current_pin')
                        <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="flex flex-col gap-1.5">
                <label for="pin" class="text-xs font-bold text-foreground">
                    {{ $hasPin ? 'PIN Baru (6-Digit Angka)' : 'Buat PIN 6-Digit Angka' }}
                </label>
                <input
                    id="pin"
                    type="password"
                    name="pin"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="new-password"
                    placeholder="••••••"
                    required
                    class="h-11 w-full rounded-lg border border-input bg-background px-3.5 font-mono text-sm tracking-widest text-foreground tabular-nums {{ $focus }}"
                >
                @error('pin')
                    <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="pin_confirmation" class="text-xs font-bold text-foreground">Ulangi PIN Baru</label>
                <input
                    id="pin_confirmation"
                    type="password"
                    name="pin_confirmation"
                    maxlength="6"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="new-password"
                    placeholder="••••••"
                    required
                    class="h-11 w-full rounded-lg border border-input bg-background px-3.5 font-mono text-sm tracking-widest text-foreground tabular-nums {{ $focus }}"
                >
            </div>

            <button type="submit"
                class="mt-1 flex min-h-11 w-full items-center justify-center rounded-lg border border-foreground bg-primary px-4 text-xs font-extrabold text-primary-foreground transition-colors hover:bg-primary/90 dark:border-primary {{ $focus }}">
                {{ $hasPin ? 'Simpan Perubahan PIN' : 'Buat PIN Sekarang' }}
            </button>
        </form>

        @if ($hasPin)
            {{-- Reset PIN Section --}}
            <div class="flex flex-col items-start gap-3 rounded-xl border border-dashed border-input bg-card p-4 text-xs">
                <div>
                    <p class="font-bold text-foreground">Lupa 6-digit PIN Keamanan Anda?</p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-muted-foreground">
                        Ajukan kode OTP 4-digit ke WhatsApp terdaftar untuk menyetel ulang PIN Anda.
                    </p>
                </div>
                <form method="POST" action="{{ route('customer.pin.reset.request') }}" class="w-full">
                    @csrf
                    <button type="submit"
                        class="flex min-h-11 w-full items-center justify-center rounded-lg border border-border bg-background px-4 text-xs font-bold text-foreground transition-colors hover:bg-accent {{ $focus }}">
                        Minta OTP WhatsApp untuk Reset PIN
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-layouts.customer>
