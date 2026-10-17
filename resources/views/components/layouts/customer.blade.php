@props(['title', 'showNav' => true])

<!DOCTYPE html>
<html lang="id">
    <head>
        <x-head :title="$title" />
    </head>
    <body class="bg-background font-sans text-foreground antialiased selection:bg-primary/20">
        <!-- Container App Mobile-Friendly -->
        <div class="mx-auto flex min-h-svh w-full max-w-md flex-col border-x border-border/40 bg-background shadow-xs">
            <!-- Header Atas Aplikasi -->
            <header class="sticky top-0 z-20 flex min-h-14 items-center justify-between border-b border-border bg-background/95 px-4 backdrop-blur-sm sm:px-6">
                <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2 outline-none focus-visible:ring-2 focus-visible:ring-ring">
                    <span class="font-serif text-lg font-semibold tracking-tight text-foreground">{{ config('app.name') }}</span>
                    <span class="rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-medium text-primary">Member</span>
                </a>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        x-cloak
                        x-show="$store.pwa?.canPrompt"
                        x-on:click="$store.pwa.install()"
                        title="Pasang Aplikasi ke Smartphone"
                        aria-label="Pasang Aplikasi"
                        class="flex h-8 items-center gap-1.5 rounded-md border border-primary/30 bg-primary/10 px-2.5 text-[11px] font-semibold text-primary hover:bg-primary/20 transition-colors"
                    >
                        <x-lucide-download class="size-3.5" />
                        <span>Install</span>
                    </button>
                    <x-theme-toggle />
                    @if (auth('customer')->check())
                        <form method="POST" action="{{ route('customer.logout') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                aria-label="Keluar akun"
                                class="flex size-9 items-center justify-center rounded-md border border-border text-xs text-muted-foreground hover:bg-accent hover:text-foreground transition-colors"
                            >
                                <x-lucide-log-out class="size-4" />
                            </button>
                        </form>
                    @endif
                </div>
            </header>

            <!-- PWA Install Banner (Android & iOS) -->
            <div
                x-cloak
                x-show="$store.pwa?.canPrompt && !$store.pwa?.dismissed"
                class="mx-4 mt-3 flex items-center justify-between gap-3 rounded-xl border border-primary/30 bg-linear-to-r from-primary/15 via-card to-primary/5 p-3 text-xs shadow-xs"
            >
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#1c325c] p-1.5 shadow-xs">
                        <img src="{{ asset('icons/Logo.png') }}" alt="" class="size-full object-contain">
                    </span>
                    <div class="flex flex-col min-w-0">
                        <p class="font-semibold text-foreground leading-tight truncate">Pasang Aplikasi {{ config('app.name') }}</p>
                        <p class="text-[11px] text-muted-foreground leading-snug">
                            <span x-show="$store.pwa?.isIOS">Akses cepat di iPhone tanpa buka Safari.</span>
                            <span x-show="!$store.pwa?.isIOS">Akses langsung dari layar utama smartphone.</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <button
                        type="button"
                        x-on:click="$store.pwa.install()"
                        class="flex h-8 items-center gap-1 rounded-md bg-primary px-3 text-xs font-semibold text-primary-foreground hover:bg-primary/90 shadow-xs transition-colors"
                    >
                        <x-lucide-download class="size-3.5" />
                        <span>Pasang</span>
                    </button>
                    <button
                        type="button"
                        x-on:click="$store.pwa.dismiss()"
                        aria-label="Tutup notifikasi pasang aplikasi"
                        class="flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground transition-colors"
                    >
                        <x-lucide-x class="size-4" />
                    </button>
                </div>
            </div>

            <!-- Flash Alert (Status & OTP Simulated Banner) -->
            @if (session('status'))
                <div class="mx-4 mt-3 rounded-md border border-primary/30 bg-primary/10 px-3.5 py-2.5 text-xs text-primary">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('otp_simulated'))
                <div class="mx-4 mt-3 rounded-md border border-emerald-500/40 bg-emerald-500/10 p-3 text-xs text-emerald-800 dark:text-emerald-300">
                    <p class="font-bold">Simulasi WhatsApp ke {{ session('otp_simulated')['phone'] }}:</p>
                    <p class="mt-0.5">Kode OTP masuk Anda adalah: <span class="font-mono font-bold tracking-widest text-base underline">{{ session('otp_simulated')['code'] }}</span> (berlaku 5 menit).</p>
                </div>
            @endif

            <!-- Konten Utama Halaman -->
            <main class="flex-1 p-4 pb-24 sm:p-6 sm:pb-24">
                {{ $slot }}
            </main>

            <!-- Navigasi Bawah (Bottom App Bar) -->
            @if ($showNav && auth('customer')->check())
                <nav aria-label="Navigasi aplikasi" class="fixed bottom-0 z-30 flex h-16 w-full max-w-md items-center justify-around border-t border-border bg-card/95 backdrop-blur-md px-2 shadow-lg">
                    <a
                        href="{{ route('customer.dashboard') }}"
                        class="flex min-h-[48px] min-w-[56px] flex-col items-center justify-center gap-1 py-1 text-[11px] font-medium transition-colors {{ request()->routeIs('customer.dashboard') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground' }}"
                    >
                        <x-lucide-home class="size-4" />
                        <span>Beranda</span>
                    </a>

                    <a
                        href="{{ route('customer.rewards.index') }}"
                        class="flex min-h-[48px] min-w-[56px] flex-col items-center justify-center gap-1 py-1 text-[11px] font-medium transition-colors {{ request()->routeIs('customer.rewards.*') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground' }}"
                    >
                        <x-lucide-ticket class="size-4" />
                        <span>Reward</span>
                    </a>

                    <a
                        href="{{ route('customer.vouchers.index') }}"
                        class="flex min-h-[48px] min-w-[56px] flex-col items-center justify-center gap-1 py-1 text-[11px] font-medium transition-colors {{ request()->routeIs('customer.vouchers.*') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground' }}"
                    >
                        <x-lucide-tag class="size-4" />
                        <span>Kupon Saya</span>
                    </a>

                    <a
                        href="{{ route('customer.history.index') }}"
                        class="flex min-h-[48px] min-w-[56px] flex-col items-center justify-center gap-1 py-1 text-[11px] font-medium transition-colors {{ request()->routeIs('customer.history.*') || request()->routeIs('customer.receipt') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground' }}"
                    >
                        <x-lucide-bar-chart-3 class="size-4" />
                        <span>Riwayat</span>
                    </a>

                    <a
                        href="{{ route('customer.pin.show') }}"
                        class="flex min-h-[48px] min-w-[56px] flex-col items-center justify-center gap-1 py-1 text-[11px] font-medium transition-colors {{ request()->routeIs('customer.pin.*') ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground' }}"
                    >
                        <x-lucide-user-check class="size-4" />
                        <span>PIN Akun</span>
                    </a>
                </nav>
            @endif
        </div>
        <x-pwa-modals />
        @aprilScripts
    </body>
</html>
