@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<x-layouts.customer title="Masuk Member" :show-nav="false">
    <div class="flex flex-col gap-4" x-data="customerLogin">
        <div class="flex items-center justify-between">
            <a href="{{ route('home') }}"
                class="inline-flex min-h-11 items-center rounded-lg border border-border bg-card px-3.5 text-xs font-bold text-foreground transition-colors hover:bg-accent {{ $focus }}">
                &lsaquo; Beranda Soho
            </a>
            <span class="rounded-md bg-secondary px-2.5 py-1 text-[10px] font-bold text-secondary-foreground">
                Verifikasi WhatsApp
            </span>
        </div>

        <header class="flex flex-col gap-0.5">
            <h1 class="text-xl font-extrabold tracking-tight text-foreground">Masuk Portal Member</h1>
            <p class="text-xs leading-relaxed text-muted-foreground">
                Masuk atau daftar keanggotaan digital cukup dengan nomor WhatsApp aktif Anda.
            </p>
        </header>

        @if (session('status_message'))
            <div role="status" class="rounded-xl border border-border bg-card px-3.5 py-2.5 text-xs font-semibold text-foreground">
                {{ session('status_message') }}
            </div>
        @endif

        {{-- Perforated Member Login Pass --}}
        <article class="relative overflow-hidden rounded-2xl border border-border bg-card text-card-foreground">
            <div class="bg-[#1F1D19] px-5 py-4 text-[#F5F4EF]">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="text-[11px] font-semibold text-[#D8D6CF]">Kartu Member Digital</span>
                        <p class="mt-0.5 truncate text-sm font-extrabold text-[#F5F4EF]">{{ config('app.name') }}</p>
                    </div>

                    <div class="shrink-0">
                        <span x-cloak x-show="checked && !registered"
                            class="inline-block rounded-md bg-[#F5B60A] px-2.5 py-1 text-[10px] font-extrabold text-[#1D1B16]">
                            Nomor Baru
                        </span>
                        <span x-cloak x-show="checked && registered"
                            class="inline-block rounded-md border border-[#F5F4EF]/20 bg-[#27241F] px-2.5 py-1 text-[10px] font-bold text-[#F5F4EF]">
                            Member Terdaftar
                        </span>
                        <span x-show="!checked"
                            class="inline-block rounded-md border border-[#F5F4EF]/20 bg-[#27241F] px-2.5 py-1 text-[10px] font-semibold text-[#D8D6CF]">
                            Login / Daftar
                        </span>
                    </div>
                </div>
            </div>

            {{-- Perforated Tear Line --}}
            <div class="relative flex items-center justify-between" aria-hidden="true">
                <span class="-ml-3 size-6 rounded-full bg-background"></span>
                <span class="w-full border-t-2 border-dashed border-border"></span>
                <span class="-mr-3 size-6 rounded-full bg-background"></span>
            </div>

            <form method="POST" action="{{ route('customer.send-otp') }}" class="flex flex-col gap-4 p-5">
                @csrf

                <div class="flex flex-col gap-1.5">
                    <label for="phone-input" class="text-xs font-bold text-foreground">Nomor WhatsApp</label>
                    <input
                        id="phone-input"
                        type="tel"
                        name="phone"
                        x-model="phone"
                        @input="onPhoneInput"
                        placeholder="Contoh: 0812 3456 7890"
                        inputmode="tel"
                        autocomplete="tel"
                        required
                        autofocus
                        class="h-11 w-full rounded-lg border border-input bg-background px-3.5 font-mono text-sm tabular-nums text-foreground placeholder:font-sans placeholder:text-muted-foreground {{ $focus }}"
                    >
                    @error('phone')
                        <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] leading-relaxed text-muted-foreground">
                        Kode OTP 4-digit akan dikirimkan ke WhatsApp Anda.
                    </p>
                </div>

                <div
                    x-cloak
                    x-show="isNew"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="flex flex-col gap-1.5 border-t border-dashed border-border pt-4"
                >
                    <label for="name-input" class="text-xs font-bold text-foreground">
                        Nama Pelanggan <span class="text-destructive">*</span>
                    </label>
                    <input
                        id="name-input"
                        type="text"
                        name="name"
                        x-model="name"
                        :required="isNew"
                        placeholder="Nama panggilan Anda, misal: Budi"
                        maxlength="50"
                        class="h-11 w-full rounded-lg border border-input bg-background px-3.5 text-sm text-foreground placeholder:text-muted-foreground {{ $focus }}"
                    >
                    @error('name')
                        <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] leading-relaxed text-muted-foreground">
                        Nama ini tampil di kartu member digital dan struk belanja Anda.
                    </p>
                </div>

                <button type="submit"
                    class="mt-1 flex min-h-11 w-full items-center justify-center rounded-lg border border-foreground bg-primary px-4 text-xs font-extrabold text-primary-foreground transition-colors hover:bg-primary/90 dark:border-primary {{ $focus }}">
                    <span x-text="isNew ? 'Daftar & Kirim Kode OTP' : 'Kirim Kode OTP'"></span>
                </button>
            </form>
        </article>

        <div class="rounded-xl border border-dashed border-input bg-card p-4 text-xs leading-relaxed text-muted-foreground">
            <p class="font-bold text-foreground">Belum pernah terdaftar sebelumnya?</p>
            <p class="mt-0.5 text-[11px]" x-show="!isNew">Ketik nomor WhatsApp aktif Anda di atas. Jika nomor belum tercatat, kolom nama akan muncul otomatis.</p>
            <p class="mt-0.5 text-[11px]" x-show="isNew">Lengkapi nama Anda di atas. Kartu member langsung aktif setelah verifikasi OTP 4-digit.</p>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('customerLogin', () => ({
                phone: @json(old('phone', '')),
                name: @json(old('name', '')),
                checked: false,
                registered: false,
                isNew: {{ session('is_new_member') || old('name') ? 'true' : 'false' }},
                timer: null,

                init() {
                    if (this.phone.trim().length >= 10) {
                        this.onPhoneInput();
                    }
                },

                onPhoneInput() {
                    clearTimeout(this.timer);
                    const clean = this.phone.replace(/[^0-9]/g, '');
                    if (clean.length < 10) {
                        this.checked = false;
                        if (!{{ session('is_new_member') ? 'true' : 'false' }}) {
                            this.isNew = false;
                        }
                        return;
                    }

                    this.timer = setTimeout(async () => {
                        try {
                            const res = await fetch(`{{ route('customer.check-phone') }}?phone=${encodeURIComponent(this.phone)}`);
                            if (res.ok) {
                                const data = await res.json();
                                if (data.valid) {
                                    this.checked = true;
                                    this.registered = data.registered;
                                    this.isNew = !data.registered;
                                    if (this.isNew && !this.name) {
                                        this.$nextTick(() => {
                                            document.getElementById('name-input')?.focus();
                                        });
                                    }
                                }
                            }
                        } catch (e) {
                            // Fallback to server check
                        }
                    }, 350);
                }
            }));
        });
    </script>
</x-layouts.customer>
