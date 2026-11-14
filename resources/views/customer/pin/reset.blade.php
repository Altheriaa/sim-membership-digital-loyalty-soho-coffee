@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer title="Reset PIN Keamanan">
    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('customer.pin.show') }}"
                class="inline-flex min-h-11 items-center rounded-lg border border-border bg-card px-3.5 text-xs font-bold text-foreground transition-colors hover:bg-accent {{ $focus }}">
                &lsaquo; PIN Keamanan
            </a>
            <span class="rounded-md bg-secondary px-2.5 py-1 text-[10px] font-bold text-secondary-foreground">
                Masa aktif 5 menit
            </span>
        </div>

        <header class="flex flex-col gap-0.5">
            <h1 class="text-xl font-extrabold tracking-tight text-foreground">Reset PIN Keamanan</h1>
            <p class="text-xs leading-relaxed text-muted-foreground">
                Masukkan kode OTP 4-digit yang dikirim ke WhatsApp <strong class="font-mono text-foreground">{{ $member->displayPhone() }}</strong>, lalu buat PIN 6-digit baru.
            </p>
        </header>

        <form method="POST" action="{{ route('customer.pin.reset.confirm') }}"
            class="flex flex-col gap-4 rounded-xl border border-border bg-card p-5 text-card-foreground">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="otp" class="text-xs font-bold text-foreground">Kode OTP WhatsApp (4-Digit)</label>
                <input id="otp" type="text" name="otp" maxlength="4" inputmode="numeric" pattern="[0-9]*"
                    placeholder="••••" required autofocus autocomplete="one-time-code"
                    class="h-12 w-full rounded-lg border border-input bg-background text-center font-mono text-xl font-extrabold tracking-[0.45em] text-foreground tabular-nums {{ $focus }}">
                @error('otp')
                    <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5 border-t border-dashed border-border pt-4">
                <label for="pin" class="text-xs font-bold text-foreground">PIN 6-Digit Baru</label>
                <input id="pin" type="password" name="pin" maxlength="6" inputmode="numeric" pattern="[0-9]*"
                    autocomplete="new-password" placeholder="••••••" required
                    class="h-11 w-full rounded-lg border border-input bg-background px-3.5 font-mono text-sm tracking-widest text-foreground tabular-nums {{ $focus }}">
                @error('pin')
                    <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="pin_confirmation" class="text-xs font-bold text-foreground">Konfirmasi PIN Baru</label>
                <input id="pin_confirmation" type="password" name="pin_confirmation" maxlength="6" inputmode="numeric"
                    pattern="[0-9]*" autocomplete="new-password" placeholder="••••••" required
                    class="h-11 w-full rounded-lg border border-input bg-background px-3.5 font-mono text-sm tracking-widest text-foreground tabular-nums {{ $focus }}">
            </div>

            <div class="mt-1 flex flex-col-reverse gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('customer.pin.show') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-border bg-background px-4 text-xs font-bold text-foreground transition-colors hover:bg-accent {{ $focus }}">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex min-h-11 flex-1 items-center justify-center rounded-lg border border-foreground bg-primary px-5 text-xs font-extrabold text-primary-foreground transition-colors hover:bg-primary/90 dark:border-primary {{ $focus }}">
                    Reset dan Simpan PIN
                </button>
            </div>
        </form>
    </div>
</x-layouts.customer>
