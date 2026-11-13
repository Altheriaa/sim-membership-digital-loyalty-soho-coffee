@use('App\Support\Rupiah')

@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
    $focusOnDark = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-background';
    $navLinks = [
        '#galeri' => 'Galeri',
        '#menu' => 'Menu',
        '#promo' => 'Promo',
        '#lokasi' => 'Lokasi',
    ];
    $memberCta = $isMember
        ? ['href' => route('customer.dashboard'), 'label' => 'Buka Kartu Member', 'short' => 'Kartu Saya']
        : ['href' => route('customer.login'), 'label' => 'Gabung Member Gratis', 'short' => 'Member'];
    $spendStep = Rupiah::format($settings->spend_per_point);
    $slides = [
        [
            'tab' => '01 · Kopi Seduh',
            'title' => 'Espresso, Americano, & Kopi Susu Racikan Harian',
            'caption' => 'Diekstraksi langsung saat Anda memesan. Dari Espresso pekat, Americano dingin, hingga Kopi Susu yang lembut.',
            'price' => 'Mulai Rp 18.000',
        ],
        [
            'tab' => '02 · Suasana Kedai',
            'title' => 'Meja Nyaman untuk Kerja Fokus maupun Ngobrol Sore',
            'caption' => 'Ruang hangat dengan pencahayaan teduh untuk membuka laptop, rapat santai, atau rehat sejenak dari rutinitas.',
            'price' => 'Buka Setiap Hari',
        ],
        [
            'tab' => '03 · Non-Kopi',
            'title' => 'Cokelat Pekat & Teh Lemon Segar',
            'caption' => 'Pilihan segar bagi yang sedang tidak minum kafein. Manis-asam Teh Lemon atau lembutnya Cokelat dingin dan hangat.',
            'price' => 'Mulai Rp 18.000',
        ],
        [
            'tab' => '04 · Camilan Hangat',
            'title' => 'Roti Bakar Hangat Teman Secangkir Kopi',
            'caption' => 'Dipanggang garing di luar dan lembut di dalam saat dipesan, cocok disantap bersama minuman favorit Anda.',
            'price' => 'Rp 22.000',
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <x-head title="Kopi Segar, Camilan Hangat, & Ruang Nyaman" />
    @push('styles')
        <meta name="description" content="{{ config('app.name') }}: kedai kopi harian dengan menu Espresso, Kopi Susu, Cokelat, Teh Lemon, dan Roti Bakar hangat.">
    @endpush
</head>

<body x-data="pointsEstimator(@js([
        'items' => $menuItems,
        'spendPerPoint' => $settings->spend_per_point,
        'tiers' => $tiers->map(fn ($tier) => ['name' => $tier->name, 'multiplierHundredths' => $tier->multiplierHundredths()])->values(),
        'checkPhoneUrl' => route('customer.check-phone'),
        'slideCount' => count($slides),
    ]))"
    class="bg-background font-sans text-foreground antialiased selection:bg-primary/30">
    <a href="#isi"
        class="sr-only z-50 rounded-md bg-foreground px-4 py-3 text-sm font-semibold text-background focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
        Langsung ke isi halaman
    </a>

    {{-- Single-row sticky header that stays on one line across all screen widths. --}}
    <header x-data="{ open: false }" x-on:keydown.escape.window="open = false"
        class="sticky top-0 z-30 border-b border-border bg-background/95 backdrop-blur-sm">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-h-11 shrink-0 items-center gap-2.5 rounded-md px-1 {{ $focus }}">
                <img src="{{ asset('icons/logo-black.png') }}" alt="{{ config('app.name') }}"
                    class="size-8 shrink-0 object-contain dark:invert">
                <span class="text-base font-extrabold tracking-tight whitespace-nowrap">{{ config('app.name') }}</span>
            </a>

            <nav aria-label="Navigasi utama" class="hidden lg:block">
                <ul class="flex items-center gap-1 text-sm font-medium whitespace-nowrap">
                    @foreach ($navLinks as $href => $label)
                        <li>
                            <a href="{{ $href }}"
                                class="inline-flex h-11 items-center rounded-md px-3 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground {{ $focus }}">{{ $label }}</a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('guide') }}"
                            class="inline-flex h-11 items-center rounded-md px-3 font-bold text-foreground underline decoration-primary decoration-2 underline-offset-4 transition-colors hover:bg-accent {{ $focus }}">
                            Petunjuk Member
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="flex shrink-0 items-center gap-2">
                <button type="button"
                    x-on:click="$store.theme.set(document.documentElement.classList.contains('dark') ? 'light' : 'dark')"
                    aria-label="Ganti tema tampilan terang atau gelap"
                    class="inline-flex h-11 items-center justify-center rounded-md border border-input bg-background px-3 text-xs font-semibold whitespace-nowrap text-muted-foreground transition-colors hover:bg-accent hover:text-foreground {{ $focus }}">
                    <span class="dark:hidden">Mode Gelap</span>
                    <span class="hidden dark:inline">Mode Terang</span>
                </button>

                <a href="{{ $memberCta['href'] }}"
                    class="hidden h-11 items-center rounded-md border border-foreground bg-primary px-4 text-sm font-bold whitespace-nowrap text-primary-foreground transition-colors hover:bg-primary/85 sm:inline-flex dark:border-primary {{ $focus }}">
                    {{ $memberCta['short'] }}
                </a>

                <button type="button" x-on:click="open = !open" x-bind:aria-expanded="open.toString()"
                    aria-controls="menu-navigasi"
                    class="inline-flex h-11 items-center justify-center rounded-md border border-input bg-background px-3.5 text-sm font-semibold transition-colors hover:bg-accent lg:hidden {{ $focus }}">
                    <span x-show="!open">Menu</span>
                    <span x-cloak x-show="open">Tutup</span>
                </button>
            </div>
        </div>

        <div id="menu-navigasi" x-cloak x-show="open" class="border-t border-border bg-background lg:hidden">
            <nav aria-label="Navigasi seluler" class="mx-auto max-w-6xl px-4 py-3 sm:px-6">
                <ul class="grid gap-1 sm:grid-cols-2">
                    @foreach ($navLinks as $href => $label)
                        <li>
                            <a href="{{ $href }}" x-on:click="open = false"
                                class="flex h-11 items-center rounded-md px-3 text-base font-medium hover:bg-accent {{ $focus }}">{{ $label }}</a>
                        </li>
                    @endforeach
                    <li class="sm:col-span-2">
                        <a href="{{ route('guide') }}"
                            class="flex h-11 items-center rounded-md border border-input bg-card px-3 text-base font-bold hover:bg-accent {{ $focus }}">
                            Buka Petunjuk Member & Kalkulator Poin
                        </a>
                    </li>
                </ul>
                <div class="mt-3 border-t border-border pt-3 sm:hidden">
                    <a href="{{ $memberCta['href'] }}"
                        class="flex h-11 w-full items-center justify-center rounded-md border border-foreground bg-primary px-4 text-sm font-bold text-primary-foreground dark:border-primary {{ $focus }}">
                        {{ $memberCta['label'] }}
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main id="isi">
        {{-- Cafe promotional hero paired with the interactive 4-slide coffee-house gallery slider. --}}
        <section id="galeri" class="scroll-mt-20 mx-auto max-w-6xl px-4 pt-6 pb-12 sm:px-6 sm:pt-10 sm:pb-16 lg:px-8 lg:pt-12 lg:pb-24">
            <div class="grid gap-10 lg:grid-cols-12 lg:items-center lg:gap-12">
                <div class="flex flex-col gap-6 lg:col-span-5">
                    <h1 class="text-[clamp(2.15rem,5vw,3.75rem)] leading-[1.06] font-extrabold tracking-tight text-balance">
                        Seduhan kopi segar dan ruang santai untuk hari Anda.
                    </h1>
                    <p class="text-base leading-relaxed text-muted-foreground sm:text-lg">
                        Dari <strong class="font-bold text-foreground">Espresso</strong> dan <strong class="font-bold text-foreground">Kopi Susu</strong> racikan harian, hingga <strong class="font-bold text-foreground">Teh Lemon</strong> dingin dan <strong class="font-bold text-foreground">Roti Bakar</strong> hangat. Mampir ke Soho Coffee & Eatery untuk kerja fokus, rehat siang, atau ngobrol sore.
                    </p>

                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a href="#menu"
                            class="inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-primary px-6 py-2.5 text-base font-extrabold text-primary-foreground transition-colors hover:bg-primary/85 dark:border-primary {{ $focus }}">
                            Lihat Menu & Harga
                        </a>
                        <a href="{{ config('app.location_url') }}" target="_blank" rel="noopener noreferrer"
                            class="inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-background px-5 py-2.5 text-base font-bold transition-colors hover:bg-accent {{ $focus }}">
                            Lokasi di Google Maps<span class="sr-only"> (tab baru)</span>
                        </a>
                    </div>

                    <div class="flex flex-col gap-2 rounded-lg border border-border bg-card p-4 text-sm">
                        <p class="font-bold text-foreground">Program Member Digital Soho</p>
                        <p class="text-muted-foreground">
                            Setiap belanja <span class="marker font-bold text-foreground">{{ $spendStep }}</span> dapat <span class="marker font-bold text-foreground">1 poin</span> untuk ditukar kopi gratis atau diskon.
                        </p>
                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            <a href="{{ route('guide') }}"
                                class="inline-flex min-h-11 items-center rounded-md border border-foreground bg-foreground px-3.5 text-xs font-bold text-background transition-colors hover:bg-foreground/85 {{ $focus }}">
                                Buka Petunjuk & Hitung Poin
                            </a>
                            <a href="{{ $memberCta['href'] }}"
                                class="inline-flex min-h-11 items-center rounded-md px-2 text-xs font-bold underline underline-offset-4 hover:bg-accent {{ $focus }}">
                                {{ $memberCta['label'] }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Interactive Gallery Slider with keyboard arrow support, swipe gesture, and slide selector tabs. --}}
                <div class="lg:col-span-7"
                    role="region" aria-roledescription="carousel" aria-label="Galeri Soho Coffee & Eatery"
                    x-on:keydown.left.prevent="prevSlide()"
                    x-on:keydown.right.prevent="nextSlide()"
                    x-on:touchstart.passive="handleTouchStart($event)"
                    x-on:touchend.passive="handleTouchEnd($event)">
                    <div class="overflow-hidden rounded-xl border-2 border-foreground bg-foreground text-background shadow-lg dark:border-border dark:bg-card dark:text-card-foreground">
                        {{-- Visual stage for the 4 slides: bespoke coffee-house SVG artwork so every slide has rich visual craft. --}}
                        <div class="relative aspect-[16/10] w-full bg-[#1F1D19] text-[#F5F4EF]">
                            {{-- Slide 0: Espresso & Kopi Susu --}}
                            <div x-show="activeSlide === 0" class="absolute inset-0 flex flex-col justify-between p-6 sm:p-8">
                                <svg viewBox="0 0 640 360" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
                                    <rect width="640" height="360" fill="#1F1D19" />
                                    <circle cx="460" cy="180" r="145" fill="#2B2620" />
                                    <circle cx="460" cy="180" r="105" fill="#382F24" />
                                    {{-- Portafilter & Espresso Stream --}}
                                    <rect x="330" y="36" width="150" height="22" rx="6" fill="#D8D6CF" />
                                    <rect x="465" y="40" width="95" height="14" rx="7" fill="#8C5A2B" />
                                    <path d="M375 58 L390 82 L420 82 L435 58 Z" fill="#AEAAA3" />
                                    <path d="M398 82 Q400 125 398 158" stroke="#F5B60A" stroke-width="5" stroke-linecap="round" fill="none" />
                                    <path d="M412 82 Q410 125 412 158" stroke="#F5B60A" stroke-width="5" stroke-linecap="round" fill="none" />
                                    {{-- Espresso Ceramic Cup --}}
                                    <path d="M345 155 H465 L452 248 Q448 268 425 268 H385 Q362 268 358 248 Z" fill="#F5F4EF" />
                                    <path d="M462 172 H485 Q502 172 502 192 Q502 212 485 212 H457" stroke="#F5F4EF" stroke-width="12" fill="none" />
                                    <rect x="352" y="162" width="106" height="14" rx="4" fill="#F5B60A" />
                                    <rect x="325" y="268" width="160" height="14" rx="7" fill="#D8D6CF" />
                                    {{-- Iced Kopi Susu Glass --}}
                                    <path d="M185 95 H285 L273 268 H197 Z" fill="#E9E7E2" opacity="0.92" />
                                    <path d="M192 145 H278 L272 262 H198 Z" fill="#C88A36" />
                                    <path d="M190 120 H280 L276 168 H194 Z" fill="#6E4423" />
                                    <rect x="210" y="128" width="22" height="22" rx="4" fill="#F5F4EF" opacity="0.75" />
                                    <rect x="240" y="136" width="22" height="22" rx="4" fill="#F5F4EF" opacity="0.75" />
                                    <line x1="252" y1="62" x2="225" y2="245" stroke="#1F1D19" stroke-width="7" stroke-linecap="round" />
                                </svg>
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="rounded-md border border-[#F5F4EF]/30 bg-[#1F1D19]/85 px-3 py-1 text-xs font-bold text-[#F5B60A]">
                                        Signature Soho Coffee
                                    </span>
                                    <span class="rounded-md bg-[#F5B60A] px-3 py-1 text-xs font-extrabold text-[#1D1B16] tabular-nums">
                                        {{ $slides[0]['price'] }}
                                    </span>
                                </div>
                                <div class="relative z-10 max-w-lg rounded-lg border border-[#F5F4EF]/15 bg-[#1F1D19]/90 p-4">
                                    <h2 class="text-xl font-extrabold tracking-tight text-[#F5F4EF] sm:text-2xl">{{ $slides[0]['title'] }}</h2>
                                    <p class="mt-1 text-xs leading-relaxed text-[#D8D6CF] sm:text-sm">{{ $slides[0]['caption'] }}</p>
                                </div>
                            </div>

                            {{-- Slide 1: Suasana Kedai --}}
                            <div x-cloak x-show="activeSlide === 1" class="absolute inset-0 flex flex-col justify-between p-6 sm:p-8">
                                <svg viewBox="0 0 640 360" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
                                    <rect width="640" height="360" fill="#1F1D19" />
                                    {{-- Warm Pendant Lamp Glow --}}
                                    <polygon points="320,75 140,280 500,280" fill="#382F24" opacity="0.7" />
                                    <line x1="320" y1="0" x2="320" y2="58" stroke="#D8D6CF" stroke-width="4" />
                                    <path d="M275 78 Q320 38 365 78 Z" fill="#F5B60A" />
                                    <circle cx="320" cy="80" r="10" fill="#FBFBF8" />
                                    {{-- Cafe Wooden Table & Laptop + Coffee Cup --}}
                                    <rect x="110" y="235" width="420" height="20" rx="6" fill="#8C5A2B" />
                                    <rect x="160" y="255" width="18" height="105" fill="#5C3B1E" />
                                    <rect x="462" y="255" width="18" height="105" fill="#5C3B1E" />
                                    {{-- Open Laptop --}}
                                    <polygon points="210,145 345,145 335,227 200,227" fill="#D8D6CF" />
                                    <polygon points="218,153 337,153 329,219 210,219" fill="#1F1D19" />
                                    <rect x="225" y="168" width="65" height="6" rx="3" fill="#F5B60A" />
                                    <rect x="225" y="182" width="85" height="5" rx="2.5" fill="#AEAAA3" />
                                    <rect x="225" y="194" width="50" height="5" rx="2.5" fill="#AEAAA3" />
                                    <rect x="185" y="227" width="165" height="8" rx="4" fill="#AEAAA3" />
                                    {{-- Steaming Mug on Table --}}
                                    <rect x="395" y="185" width="52" height="50" rx="8" fill="#F5F4EF" />
                                    <path d="M447 196 H460 Q470 196 470 208 Q470 220 460 220 H447" stroke="#F5F4EF" stroke-width="7" fill="none" />
                                    <path d="M412 172 Q417 158 412 146" stroke="#F5B60A" stroke-width="3" stroke-linecap="round" fill="none" />
                                    <path d="M428 172 Q433 158 428 146" stroke="#F5B60A" stroke-width="3" stroke-linecap="round" fill="none" />
                                </svg>
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="rounded-md border border-[#F5F4EF]/30 bg-[#1F1D19]/85 px-3 py-1 text-xs font-bold text-[#F5B60A]">
                                        Ruang & Suasana Soho
                                    </span>
                                    <span class="rounded-md bg-[#F5B60A] px-3 py-1 text-xs font-extrabold text-[#1D1B16]">
                                        {{ $slides[1]['price'] }}
                                    </span>
                                </div>
                                <div class="relative z-10 max-w-lg rounded-lg border border-[#F5F4EF]/15 bg-[#1F1D19]/90 p-4">
                                    <h2 class="text-xl font-extrabold tracking-tight text-[#F5F4EF] sm:text-2xl">{{ $slides[1]['title'] }}</h2>
                                    <p class="mt-1 text-xs leading-relaxed text-[#D8D6CF] sm:text-sm">{{ $slides[1]['caption'] }}</p>
                                </div>
                            </div>

                            {{-- Slide 2: Non-Kopi Segar --}}
                            <div x-cloak x-show="activeSlide === 2" class="absolute inset-0 flex flex-col justify-between p-6 sm:p-8">
                                <svg viewBox="0 0 640 360" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
                                    <rect width="640" height="360" fill="#1F1D19" />
                                    <circle cx="320" cy="175" r="140" fill="#2A241E" />
                                    {{-- Iced Lemon Tea Glass with Citrus Slice --}}
                                    <circle cx="295" cy="98" r="28" fill="#F5B60A" />
                                    <circle cx="295" cy="98" r="21" fill="#FBFBF8" />
                                    <circle cx="295" cy="98" r="16" fill="#F5B60A" />
                                    <path d="M200 100 H290 L278 268 H212 Z" fill="#F5F4EF" opacity="0.9" />
                                    <path d="M206 132 H284 L274 260 H216 Z" fill="#C87A1E" />
                                    <rect x="222" y="142" width="20" height="20" rx="4" fill="#FBFBF8" opacity="0.8" />
                                    <rect x="248" y="150" width="20" height="20" rx="4" fill="#FBFBF8" opacity="0.8" />
                                    {{-- Rich Chocolate Glass --}}
                                    <path d="M355 110 H450 L438 268 H367 Z" fill="#F5F4EF" opacity="0.9" />
                                    <path d="M361 138 H444 L434 260 H371 Z" fill="#523018" />
                                    <path d="M360 122 Q402 102 445 122 L444 142 H361 Z" fill="#E9E7E2" />
                                    <rect x="392" y="114" width="22" height="8" rx="3" fill="#8C5A2B" />
                                </svg>
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="rounded-md border border-[#F5F4EF]/30 bg-[#1F1D19]/85 px-3 py-1 text-xs font-bold text-[#F5B60A]">
                                        Pilihan Non-Kopi
                                    </span>
                                    <span class="rounded-md bg-[#F5B60A] px-3 py-1 text-xs font-extrabold text-[#1D1B16] tabular-nums">
                                        {{ $slides[2]['price'] }}
                                    </span>
                                </div>
                                <div class="relative z-10 max-w-lg rounded-lg border border-[#F5F4EF]/15 bg-[#1F1D19]/90 p-4">
                                    <h2 class="text-xl font-extrabold tracking-tight text-[#F5F4EF] sm:text-2xl">{{ $slides[2]['title'] }}</h2>
                                    <p class="mt-1 text-xs leading-relaxed text-[#D8D6CF] sm:text-sm">{{ $slides[2]['caption'] }}</p>
                                </div>
                            </div>

                            {{-- Slide 3: Roti Bakar Hangat --}}
                            <div x-cloak x-show="activeSlide === 3" class="absolute inset-0 flex flex-col justify-between p-6 sm:p-8">
                                <svg viewBox="0 0 640 360" class="absolute inset-0 h-full w-full object-cover" aria-hidden="true">
                                    <rect width="640" height="360" fill="#1F1D19" />
                                    <ellipse cx="320" cy="215" rx="195" ry="62" fill="#D8D6CF" />
                                    <ellipse cx="320" cy="215" rx="165" ry="48" fill="#F5F4EF" />
                                    {{-- Thick Grilled Toast Slices --}}
                                    <rect x="205" y="135" width="135" height="82" rx="14" fill="#8C5A2B" />
                                    <rect x="215" y="143" width="115" height="66" rx="10" fill="#E6B35A" />
                                    <line x1="230" y1="155" x2="265" y2="195" stroke="#8C5A2B" stroke-width="5" stroke-linecap="round" />
                                    <line x1="255" y1="155" x2="290" y2="195" stroke="#8C5A2B" stroke-width="5" stroke-linecap="round" />
                                    <line x1="280" y1="155" x2="315" y2="195" stroke="#8C5A2B" stroke-width="5" stroke-linecap="round" />
                                    <rect x="305" y="152" width="135" height="82" rx="14" fill="#75481F" />
                                    <rect x="315" y="160" width="115" height="66" rx="10" fill="#F0C26B" />
                                    <rect x="355" y="176" width="34" height="26" rx="5" fill="#F5B60A" />
                                    <line x1="330" y1="172" x2="365" y2="212" stroke="#8C5A2B" stroke-width="5" stroke-linecap="round" />
                                    <line x1="380" y1="172" x2="415" y2="212" stroke="#8C5A2B" stroke-width="5" stroke-linecap="round" />
                                </svg>
                                <div class="relative z-10 flex items-center justify-between">
                                    <span class="rounded-md border border-[#F5F4EF]/30 bg-[#1F1D19]/85 px-3 py-1 text-xs font-bold text-[#F5B60A]">
                                        Teman Ngopi
                                    </span>
                                    <span class="rounded-md bg-[#F5B60A] px-3 py-1 text-xs font-extrabold text-[#1D1B16] tabular-nums">
                                        {{ $slides[3]['price'] }}
                                    </span>
                                </div>
                                <div class="relative z-10 max-w-lg rounded-lg border border-[#F5F4EF]/15 bg-[#1F1D19]/90 p-4">
                                    <h2 class="text-xl font-extrabold tracking-tight text-[#F5F4EF] sm:text-2xl">{{ $slides[3]['title'] }}</h2>
                                    <p class="mt-1 text-xs leading-relaxed text-[#D8D6CF] sm:text-sm">{{ $slides[3]['caption'] }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Slider Controls: Slide Tabs + Prev/Next Buttons (all >= 44px height) --}}
                        <div class="flex flex-col justify-between gap-3 border-t border-background/20 bg-card p-3 text-card-foreground sm:flex-row sm:items-center sm:px-5 dark:border-border">
                            <div role="tablist" aria-label="Pilih slide galeri" class="grid grid-cols-2 gap-1.5 sm:flex sm:flex-wrap">
                                @foreach ($slides as $index => $slide)
                                    <button type="button" role="tab"
                                        x-on:click="goToSlide({{ $index }})"
                                        x-bind:aria-selected="(activeSlide === {{ $index }}).toString()"
                                        class="inline-flex h-11 items-center justify-center rounded-md border border-input bg-background px-3 text-xs font-bold transition-colors hover:bg-accent aria-selected:border-foreground aria-selected:bg-foreground aria-selected:text-background {{ $focus }}">
                                        {{ $slide['tab'] }}
                                    </button>
                                @endforeach
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <button type="button" x-on:click="prevSlide()"
                                    aria-label="Slide sebelumnya"
                                    class="inline-flex h-11 items-center justify-center rounded-md border border-input bg-background px-3.5 text-xs font-bold transition-colors hover:bg-accent {{ $focus }}">
                                    Sebelumnya
                                </button>
                                <button type="button" x-on:click="nextSlide()"
                                    aria-label="Slide berikutnya"
                                    class="inline-flex h-11 items-center justify-center rounded-md border border-foreground bg-foreground px-3.5 text-xs font-bold text-background transition-colors hover:bg-foreground/85 {{ $focus }}">
                                    Berikutnya
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Three pillars of Soho Coffee experience. --}}
        <section class="border-t border-border bg-sidebar">
            <div class="mx-auto grid max-w-6xl gap-6 px-4 py-10 sm:grid-cols-3 sm:px-6 sm:py-14 lg:px-8">
                <div class="flex flex-col gap-2 border-t-2 border-foreground pt-4">
                    <h2 class="text-lg font-extrabold tracking-tight">Seduhan Segar Saat Dipesan</h2>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        Setiap cangkir Espresso, Americano, dan Kopi Susu dibuat langsung oleh barista saat pesanan Anda masuk di kasir.
                    </p>
                </div>
                <div class="flex flex-col gap-2 border-t-2 border-foreground pt-4">
                    <h2 class="text-lg font-extrabold tracking-tight">Menu Lengkap untuk Semua</h2>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        Tersedia pilihan Cokelat, Teh Lemon segar, dan Roti Bakar hangat untuk teman yang sedang tidak minum kopi.
                    </p>
                </div>
                <div class="flex flex-col gap-2 border-t-2 border-foreground pt-4">
                    <h2 class="text-lg font-extrabold tracking-tight">Poin Member Tanpa Kartu Fisik</h2>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        Cukup sebutkan nomor WhatsApp di kasir untuk mengumpulkan poin dan menukarkannya dengan menu gratis.
                    </p>
                </div>
            </div>
        </section>

        {{-- Cafe menu showcase: category filter tabs, search input, and menu cards. --}}
        <section id="menu" class="scroll-mt-20 border-t border-border">
            <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <h2 class="text-[clamp(1.75rem,3.6vw,2.75rem)] leading-tight font-extrabold tracking-tight">Pilihan Kopi, Non-Kopi, & Makanan</h2>
                    <p class="max-w-md text-sm leading-relaxed text-muted-foreground">
                        Daftar menu dan harga resmi di kasir Soho Coffee & Eatery.
                    </p>
                </div>

                @if ($categories->isEmpty())
                    <p class="rounded-lg border border-dashed border-input bg-card p-6 leading-relaxed text-muted-foreground">
                        Daftar menu belum diisi di sistem kasir. Begitu admin mengaktifkan item menu, daftar harga akan otomatis tampil di sini.
                    </p>
                @else
                    <div class="flex flex-col justify-between gap-4 rounded-lg border border-border bg-card p-4 sm:flex-row sm:items-center">
                        <div role="group" aria-label="Filter kategori menu" class="flex flex-wrap gap-2">
                            <button type="button" x-on:click="selectedCategory = 'all'"
                                x-bind:aria-pressed="(selectedCategory === 'all').toString()"
                                class="inline-flex h-11 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-bold text-muted-foreground transition-colors hover:bg-accent hover:text-foreground aria-pressed:border-foreground aria-pressed:bg-foreground aria-pressed:text-background {{ $focus }}">
                                Semua ({{ $menuItems->count() }})
                            </button>
                            @foreach ($categories as $category)
                                <button type="button" x-on:click="selectedCategory = {{ $category->id }}"
                                    x-bind:aria-pressed="(selectedCategory === {{ $category->id }}).toString()"
                                    class="inline-flex h-11 items-center justify-center rounded-md border border-input bg-background px-4 text-sm font-bold text-muted-foreground transition-colors hover:bg-accent hover:text-foreground aria-pressed:border-foreground aria-pressed:bg-foreground aria-pressed:text-background {{ $focus }}">
                                    {{ $category->name }} ({{ $category->items->count() }})
                                </button>
                            @endforeach
                        </div>

                        <div class="w-full sm:w-64">
                            <label for="cari-menu" class="sr-only">Cari menu</label>
                            <input id="cari-menu" type="search" x-model="search"
                                placeholder="Cari menu (misal: Kopi Susu)"
                                class="h-11 w-full scroll-my-24 rounded-md border border-input bg-background px-3 text-sm text-foreground placeholder:text-muted-foreground {{ $focus }}">
                        </div>
                    </div>

                    <div x-cloak x-show="visibleItemCount === 0" role="status"
                        class="flex flex-col items-start gap-3 rounded-lg border border-dashed border-input bg-card p-6">
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            Tidak ada menu yang cocok dengan kata kunci <strong class="text-foreground" x-text="search"></strong> pada kategori ini.
                        </p>
                        <button type="button" x-on:click="resetFilter()"
                            class="inline-flex h-11 items-center justify-center rounded-md border border-foreground bg-background px-4 text-sm font-semibold hover:bg-accent {{ $focus }}">
                            Tampilkan semua menu
                        </button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" x-show="visibleItemCount > 0">
                        @foreach ($categories as $category)
                            @foreach ($category->items as $item)
                                @php
                                    $itemPoints = intdiv($item->price, $settings->spend_per_point);
                                @endphp
                                <article x-show="matchesItem({{ $category->id }}, @js($item->name))"
                                    class="flex flex-col justify-between gap-4 rounded-lg border border-border bg-card p-5 text-card-foreground transition-colors hover:border-foreground">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-semibold text-muted-foreground">{{ $category->name }}</span>
                                            <h3 class="text-xl font-extrabold tracking-tight">{{ $item->name }}</h3>
                                        </div>
                                        @if ($item->image_url)
                                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                                                class="size-16 shrink-0 rounded-md border border-border object-cover">
                                        @endif
                                    </div>

                                    <div class="flex items-baseline justify-between border-t border-dashed border-input pt-3">
                                        <span class="text-xl font-extrabold tabular-nums">{{ Rupiah::format($item->price) }}</span>
                                        <span class="text-xs font-semibold text-muted-foreground">
                                            @if ($itemPoints > 0)
                                                +{{ $itemPoints }} poin member
                                            @else
                                                Akumulasi poin
                                            @endif
                                        </span>
                                    </div>
                                </article>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- Active reward coupons & bridge to the Guide Landing Page (/petunjuk). --}}
        <section id="promo" class="scroll-mt-20 border-t border-border bg-sidebar">
            <div class="mx-auto flex max-w-6xl flex-col gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <div class="flex flex-col gap-2">
                        <h2 class="text-[clamp(1.75rem,3.6vw,2.75rem)] leading-tight font-extrabold tracking-tight">Promo Voucher & Keuntungan Member</h2>
                        <p class="text-sm text-muted-foreground">Kumpulkan poin dari setiap pesanan dan tukarkan dengan voucher aktif di bawah ini.</p>
                    </div>
                    <a href="{{ route('customer.rewards.index') }}"
                        class="inline-flex h-11 shrink-0 items-center justify-center rounded-md border border-foreground bg-primary px-5 text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/85 dark:border-primary {{ $focus }}">
                        Klaim Voucher di Aplikasi
                    </a>
                </div>

                @if ($vouchers->isEmpty())
                    <p class="rounded-lg border border-dashed border-input bg-card p-6 leading-relaxed text-muted-foreground">
                        Belum ada promo voucher yang dibuka hari ini. Anda tetap bisa mengumpulkan poin setiap berbelanja di Soho, lalu menukarkannya saat promo baru dibuka.
                    </p>
                @else
                    <ul class="grid gap-5 md:grid-cols-2">
                        @foreach ($vouchers as $voucher)
                            <li class="flex flex-col overflow-hidden rounded-lg border-2 border-foreground bg-card text-card-foreground sm:flex-row">
                                <div class="flex flex-col items-center justify-center border-b-2 border-dashed border-foreground bg-foreground px-6 py-4 text-center text-background sm:w-40 sm:shrink-0 sm:border-r-2 sm:border-b-0">
                                    <span class="text-xs font-semibold text-background/80">Tukar</span>
                                    <span class="mt-0.5 text-3xl font-extrabold tabular-nums">{{ number_format($voucher->points_cost, 0, ',', '.') }}</span>
                                    <span class="text-xs font-semibold">poin</span>
                                </div>
                                <div class="flex flex-1 flex-col justify-between gap-3 p-5">
                                    <div class="flex flex-col gap-1">
                                        <h3 class="text-xl font-extrabold tracking-tight">{{ $voucher->name }}</h3>
                                        <p class="text-sm font-semibold text-muted-foreground">{{ $voucher->benefitLabel() }}</p>
                                    </div>
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-border pt-3 text-xs text-muted-foreground">
                                        <span>Berlaku {{ $voucher->valid_days ?? 30 }} hari sejak klaim</span>
                                        @if ($voucher->quota === 0)
                                            <strong class="font-bold text-destructive">Kuota habis</strong>
                                        @elseif ($voucher->quota !== null)
                                            <span class="font-semibold text-foreground">Sisa {{ $voucher->quota }} kupon</span>
                                        @else
                                            <span class="font-semibold text-foreground">Tersedia</span>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                {{-- Direct callout linking to the dedicated Guide & Points Calculator landing page (/petunjuk). --}}
                <div class="flex flex-col items-start justify-between gap-6 rounded-xl border-2 border-foreground bg-card p-6 text-card-foreground sm:p-8 lg:flex-row lg:items-center">
                    <div class="flex max-w-2xl flex-col gap-2">
                        <h3 class="text-2xl font-extrabold tracking-tight">
                            Ingin tahu cara kerja poin, tier member, dan simulasi struk pesanan?
                        </h3>
                        <p class="text-sm leading-relaxed text-muted-foreground sm:text-base">
                            Buka halaman petunjuk member untuk menghitung estimasi poin dari pesanan Anda, mengecek status nomor WhatsApp, dan melihat 4 langkah pemakaian voucher di kasir.
                        </p>
                    </div>
                    <div class="flex w-full shrink-0 flex-col gap-3 sm:w-auto sm:flex-row">
                        <a href="{{ route('guide') }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-primary px-6 py-2.5 text-base font-extrabold text-primary-foreground transition-colors hover:bg-primary/85 dark:border-primary {{ $focus }}">
                            Buka Halaman Petunjuk
                        </a>
                        <a href="{{ $memberCta['href'] }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-background px-5 py-2.5 text-base font-bold transition-colors hover:bg-accent {{ $focus }}">
                            {{ $memberCta['label'] }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Location and PWA installation. --}}
        <section id="lokasi" class="scroll-mt-20 border-t border-border bg-card text-card-foreground">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 sm:gap-0 sm:px-6 sm:py-14 lg:px-8 lg:py-16">
                <div class="flex flex-col items-start gap-3 sm:pr-8 lg:pr-14">
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Mampir ke Soho Coffee & Eatery</h2>
                    <p class="leading-relaxed text-muted-foreground">Lihat titik lokasi, rute perjalanan, dan jam operasional terbaru di Google Maps kami.</p>
                    <a href="{{ config('app.location_url') }}" target="_blank" rel="noopener noreferrer"
                        class="mt-1 inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-background px-5 py-2.5 text-base font-bold transition-colors hover:bg-accent {{ $focus }}">
                        Buka di Google Maps<span class="sr-only"> (tab baru)</span>
                    </a>
                </div>
                <div class="flex flex-col items-start gap-3 border-t border-border pt-10 sm:border-t-0 sm:border-l sm:pt-0 sm:pl-8 lg:pl-14">
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Simpan Aplikasi Soho di HP</h2>
                    <p class="leading-relaxed text-muted-foreground">Pasang aplikasi web Soho ke layar utama ponsel Anda agar kartu member dan kupon bisa dibuka kapan saja.</p>
                    <button type="button" x-show="$store.pwa?.canPrompt" x-on:click="$store.pwa.install()"
                        class="mt-1 inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-background px-5 py-2.5 text-base font-bold transition-colors hover:bg-accent {{ $focus }}">
                        Pasang Aplikasi Soho
                    </button>
                    <p x-cloak x-show="!$store.pwa?.canPrompt" class="mt-1 text-sm font-semibold">
                        Aplikasi sudah terpasang di perangkat ini.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-border">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-6 text-sm text-muted-foreground sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>&copy; {{ now()->year }} {{ config('app.name') }}</p>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('guide') }}"
                    class="inline-flex min-h-11 items-center rounded-md px-2 font-medium underline underline-offset-4 hover:text-foreground {{ $focus }}">Petunjuk & Hitung Poin</a>
                <a href="{{ route('customer.login') }}"
                    class="inline-flex min-h-11 items-center rounded-md px-2 font-medium underline underline-offset-4 hover:text-foreground {{ $focus }}">Masuk Member</a>
                <a href="{{ route('login') }}"
                    class="inline-flex min-h-11 items-center rounded-md px-2 font-medium underline underline-offset-4 hover:text-foreground {{ $focus }}">Masuk Staf</a>
            </div>
        </div>
    </footer>

    <div x-cloak x-show="$store.pwa?.showIOSModal || $store.pwa?.showAndroidModal"
        x-on:keydown.escape.window="$store.pwa.closeModals()"
        role="dialog" aria-modal="true" aria-labelledby="judul-panduan-pasang-home"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 p-0 sm:items-center sm:p-4">
        <div class="fixed inset-0" x-on:click="$store.pwa.closeModals()" aria-hidden="true"></div>
        <div class="relative z-10 w-full max-w-md rounded-t-lg border border-border bg-card p-5 text-card-foreground sm:rounded-lg sm:p-6">
            <div class="flex items-start justify-between gap-4 border-b border-border pb-4">
                <div>
                    <h2 id="judul-panduan-pasang-home" class="text-lg font-extrabold tracking-tight"
                        x-text="$store.pwa?.showIOSModal ? 'Pasang di iPhone atau iPad' : 'Pasang di HP Android'">
                        Pasang aplikasi member
                    </h2>
                    <p class="mt-0.5 text-sm text-muted-foreground">Tambahkan kartu member ke layar utama tanpa toko aplikasi.</p>
                </div>
                <button type="button" x-on:click="$store.pwa.closeModals()"
                    class="inline-flex h-11 shrink-0 items-center justify-center rounded-md border border-input bg-background px-3 text-sm font-semibold hover:bg-accent {{ $focus }}">
                    Tutup
                </button>
            </div>

            <ol x-show="$store.pwa?.showIOSModal" class="mt-4 flex flex-col gap-3 text-sm">
                <li class="flex gap-3">
                    <span class="font-extrabold tabular-nums">01.</span>
                    <span>Ketuk tombol <strong>Bagikan (Share)</strong> di bilah bawah Safari.</span>
                </li>
                <li class="flex gap-3">
                    <span class="font-extrabold tabular-nums">02.</span>
                    <span>Pilih <strong>Tambah ke Layar Utama</strong> (<em>Add to Home Screen</em>).</span>
                </li>
                <li class="flex gap-3">
                    <span class="font-extrabold tabular-nums">03.</span>
                    <span>Ketuk <strong>Tambah</strong> di pojok kanan atas layar.</span>
                </li>
            </ol>

            <ol x-show="!$store.pwa?.showIOSModal" class="mt-4 flex flex-col gap-3 text-sm">
                <li class="flex gap-3">
                    <span class="font-extrabold tabular-nums">01.</span>
                    <span>Buka menu browser di pojok kanan atas layar.</span>
                </li>
                <li class="flex gap-3">
                    <span class="font-extrabold tabular-nums">02.</span>
                    <span>Pilih <strong>Pasang aplikasi</strong> atau <strong>Tambahkan ke Layar Utama</strong>.</span>
                </li>
                <li class="flex gap-3">
                    <span class="font-extrabold tabular-nums">03.</span>
                    <span>Konfirmasi dengan memilih <strong>Pasang</strong>.</span>
                </li>
            </ol>

            <button type="button" x-on:click="$store.pwa.closeModals()"
                class="mt-6 inline-flex h-11 w-full items-center justify-center rounded-md border border-foreground bg-foreground px-4 text-sm font-semibold text-background transition-colors hover:bg-foreground/85 {{ $focus }}">
                Mengerti
            </button>
        </div>
    </div>
</body>

</html>
