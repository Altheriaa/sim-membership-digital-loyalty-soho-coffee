<x-layouts.customer title="Masuk Member" :show-nav="false">
    <div class="flex flex-col gap-6 pt-6" x-data="customerLogin">
        <header class="flex flex-col gap-1.5 text-center">
            <h1 class="font-serif text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Selamat Datang</h1>
            <p class="text-xs text-muted-foreground">
                Masuk atau daftar keanggotaan digital cafe cukup dengan nomor WhatsApp aktif Anda.
            </p>
        </header>

        @if (session('status_message'))
            <div class="rounded-lg border border-primary/30 bg-primary/10 p-3 text-xs text-foreground flex items-start gap-2.5">
                <x-lucide-info class="size-4 text-primary shrink-0 mt-0.5" />
                <span>{{ session('status_message') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('customer.send-otp') }}" class="flex flex-col gap-4 rounded-lg border border-border bg-card p-5 text-card-foreground shadow-xs">
            @csrf

            <div class="flex flex-col gap-1.5">
                <div class="flex items-center justify-between">
                    <label for="phone-input" class="text-xs font-semibold text-foreground">Nomor WhatsApp</label>
                    <span x-cloak x-show="checked && !registered" class="rounded bg-primary/15 px-1.5 py-0.5 text-[10px] font-medium text-primary">
                        Nomor Baru
                    </span>
                    <span x-cloak x-show="checked && registered" class="rounded bg-secondary px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">
                        Member Terdaftar
                    </span>
                </div>
                <input
                    id="phone-input"
                    type="tel"
                    name="phone"
                    x-model="phone"
                    @input="onPhoneInput"
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

            <!-- Input Nama Pelanggan (Muncul jika belum terdaftar) -->
            <div
                x-cloak
                x-show="isNew"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="flex flex-col gap-1.5 border-t border-border/60 pt-3"
            >
                <label for="name-input" class="text-xs font-semibold text-foreground">
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
                    class="h-11 w-full rounded-md border border-input bg-background px-3 text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                >
                @error('name')
                    <p class="text-xs text-destructive">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-muted-foreground">
                    Nama ini akan digunakan pada kartu keanggotaan digital dan struk transaksi Anda di kasir.
                </p>
            </div>

            <april:button type="submit" size="none" class="mt-2 flex h-11 w-full items-center justify-center font-medium text-xs">
                <span x-text="isNew ? 'Daftar & Kirim Kode OTP' : 'Kirim Kode OTP'"></span>
            </april:button>
        </form>

        <div class="flex flex-col items-center justify-center gap-1 text-center text-xs text-muted-foreground">
            <p>Belum pernah terdaftar sebelumnya?</p>
            <p class="text-[11px]" x-show="!isNew">Akun akan otomatis dibuatkan begitu nomor dan nama Anda diverifikasi.</p>
            <p class="text-[11px]" x-show="isNew">Cukup isi nama Anda di atas, akun member langsung aktif setelah verifikasi OTP.</p>
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
