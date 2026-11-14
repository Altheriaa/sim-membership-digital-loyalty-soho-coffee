@props(['title', 'showNav' => true])

@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <x-head :title="$title" />
</head>

<body class="bg-background font-sans text-foreground antialiased selection:bg-primary/25">
    <div class="mx-auto flex min-h-svh w-full max-w-md flex-col border-x border-border/60 bg-background">
        {{-- Top Navigation Bar: single line, no icon clutter --}}
        <header class="sticky top-0 z-20 flex min-h-14 items-center justify-between gap-2 border-b border-border bg-background/95 px-4 py-1.5 backdrop-blur-md">
            <a href="{{ auth('customer')->check() ? route('customer.dashboard') : route('home') }}"
                class="flex min-h-11 min-w-0 items-center gap-2.5 rounded-lg py-1 {{ $focus }}">
                <img src="{{ asset('icons/logo-black.png') }}" alt="{{ config('app.name') }}"
                    class="size-6 shrink-0 object-contain dark:invert">
                <span class="truncate text-sm font-extrabold tracking-tight text-foreground">{{ config('app.name') }}</span>
            </a>

            <div class="flex shrink-0 items-center gap-1.5">
                <button type="button" x-cloak x-show="$store.pwa?.canPrompt" x-on:click="$store.pwa.install()"
                    class="inline-flex min-h-11 items-center rounded-lg border border-border bg-card px-2.5 text-[11px] font-bold text-foreground transition-colors hover:bg-accent {{ $focus }}">
                    Pasang App
                </button>

                <button type="button" x-data
                    x-on:click="$store.theme.set(document.documentElement.classList.contains('dark') ? 'light' : 'dark')"
                    aria-label="Ganti tema terang atau gelap"
                    title="Ganti tema"
                    class="inline-flex size-11 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:bg-accent hover:text-foreground {{ $focus }}">
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="size-4.5 shrink-0 dark:hidden">
                        <path fill-rule="evenodd" d="M7.455 2.004a.75.75 0 0 1 .26.77 7 7 0 0 0 9.958 7.967.75.75 0 0 1 1.067.853A8.5 8.5 0 1 1 6.647 1.921a.75.75 0 0 1 .808.083Z" clip-rule="evenodd" />
                    </svg>
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="hidden size-4.5 shrink-0 dark:block">
                        <path d="M10 2a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 2ZM10 15a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-1.5 0v-1.5A.75.75 0 0 1 10 15ZM10 7a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM15.657 5.404a.75.75 0 1 0-1.06-1.06l-1.061 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM6.464 14.596a.75.75 0 1 0-1.06-1.06l-1.06 1.06a.75.75 0 0 0 1.06 1.06l1.06-1.06ZM18 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 18 10ZM5 10a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1 0-1.5h1.5A.75.75 0 0 1 5 10ZM14.596 15.657a.75.75 0 0 0 1.06-1.06l-1.06-1.061a.75.75 0 1 0-1.06 1.06l1.06 1.06ZM5.404 6.464a.75.75 0 0 0 1.06-1.06l-1.06-1.06a.75.75 0 1 0-1.061 1.06l1.06 1.06Z" />
                    </svg>
                    <span class="sr-only dark:hidden">Aktifkan mode gelap</span>
                    <span class="hidden sr-only dark:inline">Aktifkan mode terang</span>
                </button>

                @if (auth('customer')->check())
                    <form method="POST" action="{{ route('customer.logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                            aria-label="Keluar dari akun member"
                            title="Keluar"
                            class="inline-flex size-11 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:bg-accent hover:text-foreground {{ $focus }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="size-4.5 shrink-0">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" x2="9" y1="12" y2="12" />
                            </svg>
                            <span class="sr-only">Keluar</span>
                        </button>
                    </form>
                @endif
            </div>
        </header>

        {{-- PWA Install Prompt Bar --}}
        <div x-cloak x-show="$store.pwa?.canPrompt && !$store.pwa?.dismissed" data-print-hide
            class="mx-4 mt-3 flex items-center justify-between gap-3 rounded-xl border border-border bg-card px-3.5 py-2.5 text-xs">
            <div class="min-w-0">
                <p class="truncate font-bold text-foreground">Simpan ke layar utama HP</p>
                <p class="text-[11px] text-muted-foreground">
                    <span x-show="$store.pwa?.isIOS">Buka kartu member langsung tanpa ketik alamat web.</span>
                    <span x-show="!$store.pwa?.isIOS">Buka kartu member langsung dari layar utama HP.</span>
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-1.5">
                <button type="button" x-on:click="$store.pwa.install()"
                    class="inline-flex min-h-11 items-center rounded-lg bg-primary px-3 text-xs font-bold text-primary-foreground transition-colors hover:bg-primary/90 {{ $focus }}">
                    Pasang
                </button>
                <button type="button" x-on:click="$store.pwa.dismiss()" aria-label="Tutup"
                    class="inline-flex min-h-11 items-center rounded-lg px-2.5 text-xs font-semibold text-muted-foreground hover:bg-accent hover:text-foreground {{ $focus }}">
                    Tutup
                </button>
            </div>
        </div>

        {{-- Flash Status Banner --}}
        @if (session('status'))
            <div role="status" class="mx-4 mt-3 rounded-xl border border-border bg-card px-3.5 py-2.5 text-xs font-semibold text-foreground">
                {{ session('status') }}
            </div>
        @endif

        {{-- Main View Content --}}
        <main @class([
            'flex-1 px-4 pt-4',
            $showNav && auth('customer')->check() ? 'pb-28' : 'pb-10',
        ])>
            {{ $slot }}
        </main>

        {{-- Sticky Bottom iOS Tab Bar with Bespoke Filled/Duotone SVG Glyphs (No generic Lucide icons) --}}
        @if ($showNav && auth('customer')->check())
            @php
                $isDashboard = request()->routeIs('customer.dashboard');
                $isRewards = request()->routeIs('customer.rewards.*');
                $isVouchers = request()->routeIs('customer.vouchers.*');
                $isHistory = request()->routeIs('customer.history.*') || request()->routeIs('customer.receipt');
                $isPin = request()->routeIs('customer.pin.*');
            @endphp
            <nav aria-label="Navigasi utama member"
                class="fixed inset-x-0 bottom-0 z-30 mx-auto w-full max-w-md border-t border-border bg-card/95 px-2 pt-1.5 pb-[max(0.5rem,env(safe-area-inset-bottom))] backdrop-blur-md">
                <div class="grid grid-cols-5 gap-1">
                    {{-- Tab 1: Kartu Member (Pass Card Glyph) --}}
                    <a href="{{ route('customer.dashboard') }}"
                        @if ($isDashboard) aria-current="page" @endif
                        class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 py-1 text-[11px] transition-colors {{ $isDashboard ? 'bg-foreground font-bold text-background' : 'font-medium text-muted-foreground hover:text-foreground' }} {{ $focus }}">
                        <svg viewBox="0 0 20 20" class="size-4 shrink-0" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 0 0 1 5.5v2h18v-2A1.5 1.5 0 0 0 17.5 4h-15ZM19 9.5H1v5A1.5 1.5 0 0 0 2.5 16h15a1.5 1.5 0 0 0 1.5-1.5v-5ZM4 12.25a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 0 1.5h-3.5a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                        </svg>
                        <span class="truncate">Member</span>
                    </a>

                    {{-- Tab 2: Tukar Poin / Reward (Coffee Cup Stamp Glyph) --}}
                    <a href="{{ route('customer.rewards.index') }}"
                        @if ($isRewards) aria-current="page" @endif
                        class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 py-1 text-[11px] transition-colors {{ $isRewards ? 'bg-foreground font-bold text-background' : 'font-medium text-muted-foreground hover:text-foreground' }} {{ $focus }}">
                        <svg viewBox="0 0 20 20" class="size-4 shrink-0" fill="currentColor" aria-hidden="true">
                            <path d="M3 4.75A.75.75 0 0 1 3.75 4h10.5a.75.75 0 0 1 .75.75v1.5h1.25a2.75 2.75 0 1 1 0 5.5H14.8a5.5 5.5 0 0 1-5.3 4.25H8.5A5.5 5.5 0 0 1 3 10.5V4.75Zm12 3v2.5h1.25a1.25 1.25 0 1 0 0-2.5H15ZM2.75 16.5a.75.75 0 0 0 0 1.5h12.5a.75.75 0 0 0 0-1.5H2.75Z" />
                        </svg>
                        <span class="truncate">Reward</span>
                    </a>

                    {{-- Tab 3: Kupon Saya (Perforated Ticket Notch Glyph) --}}
                    <a href="{{ route('customer.vouchers.index') }}"
                        @if ($isVouchers) aria-current="page" @endif
                        class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 py-1 text-[11px] transition-colors {{ $isVouchers ? 'bg-foreground font-bold text-background' : 'font-medium text-muted-foreground hover:text-foreground' }} {{ $focus }}">
                        <svg viewBox="0 0 20 20" class="size-4 shrink-0" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 0 0 1 5.5v2.05a2 2 0 0 1 0 3.9v2.05A1.5 1.5 0 0 0 2.5 15h15a1.5 1.5 0 0 0 1.5-1.5v-2.05a2 2 0 0 1 0-3.9V5.5A1.5 1.5 0 0 0 17.5 4h-15ZM7.75 6.75a.75.75 0 0 0-1.5 0v1a.75.75 0 0 0 1.5 0v-1Zm0 4.5a.75.75 0 0 0-1.5 0v1a.75.75 0 0 0 1.5 0v-1Z" clip-rule="evenodd" />
                        </svg>
                        <span class="truncate">Kupon</span>
                    </a>

                    {{-- Tab 4: Riwayat Struk (Receipt Slip Glyph) --}}
                    <a href="{{ route('customer.history.index') }}"
                        @if ($isHistory) aria-current="page" @endif
                        class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 py-1 text-[11px] transition-colors {{ $isHistory ? 'bg-foreground font-bold text-background' : 'font-medium text-muted-foreground hover:text-foreground' }} {{ $focus }}">
                        <svg viewBox="0 0 20 20" class="size-4 shrink-0" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 0 0-1 1v14.3l2.1-1.4 2.1 1.4 2.1-1.4 2.1 1.4 2.1-1.4 2.5 1.4V3a1 1 0 0 0-1-1H4Zm2.75 4a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3.5a.75.75 0 0 0 0 1.5h6.5a.75.75 0 0 0 0-1.5h-6.5Zm0 3.5a.75.75 0 0 0 0 1.5h4a.75.75 0 0 0 0-1.5h-4Z" clip-rule="evenodd" />
                        </svg>
                        <span class="truncate">Riwayat</span>
                    </a>

                    {{-- Tab 5: PIN 6-Digit (Keypad Lock Glyph instead of generic user-check) --}}
                    <a href="{{ route('customer.pin.show') }}"
                        @if ($isPin) aria-current="page" @endif
                        class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-xl px-1 py-1 text-[11px] transition-colors {{ $isPin ? 'bg-foreground font-bold text-background' : 'font-medium text-muted-foreground hover:text-foreground' }} {{ $focus }}">
                        <svg viewBox="0 0 20 20" class="size-4 shrink-0" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 1.5a4.25 4.25 0 0 0-4.25 4.25V8H4.5A1.5 1.5 0 0 0 3 9.5v7A1.5 1.5 0 0 0 4.5 18h11a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 15.5 8h-1.25V5.75A4.25 4.25 0 0 0 10 1.5Zm2.75 6.5V5.75a2.75 2.75 0 1 0-5.5 0V8h5.5ZM7 12.25a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm4-1a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z" clip-rule="evenodd" />
                        </svg>
                        <span class="truncate">PIN</span>
                    </a>
                </div>
            </nav>
        @endif
    </div>
    <x-pwa-modals />
</body>

</html>
