@use('App\Support\Rupiah')

@php
    $focus = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-foreground';
    $navLinks = ['#hitung' => 'Hitung poin', '#cara-kerja' => 'Cara kerja', '#tier' => 'Tingkat member', '#reward' => 'Reward', '#lokasi' => 'Lokasi'];
    $themeModes = ['system' => 'Sistem', 'light' => 'Terang', 'dark' => 'Gelap'];
    $memberCta = $isMember
        ? ['href' => route('customer.dashboard'), 'label' => 'Buka kartu member saya', 'short' => 'Kartu saya']
        : ['href' => route('customer.login'), 'label' => 'Daftar atau masuk dengan WhatsApp', 'short' => 'Daftar'];
    $spendStep = Rupiah::format($settings->spend_per_point);
    $tierCount = $tiers->count();
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
    <x-head title="Poin & reward member" />
    @push('styles')
        <meta name="description" content="Kartu member digital {{ config('app.name') }}: setiap {{ $spendStep }} belanja jadi 1 poin, tukar poin jadi reward di aplikasi.">
    @endpush
</head>

<body class="bg-background font-sans text-foreground antialiased selection:bg-primary/30">
    <a href="#isi"
        class="sr-only z-50 rounded-md bg-foreground px-4 py-3 text-sm font-semibold text-background focus:not-sr-only focus:fixed focus:top-3 focus:left-3">
        Langsung ke isi halaman
    </a>

    {{-- Single-row sticky header that never wraps onto two lines. --}}
    <header x-data="{ open: false }" x-on:keydown.escape.window="open = false"
        class="sticky top-0 z-30 border-b border-border bg-background/95 backdrop-blur-sm">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex min-h-11 shrink-0 items-center gap-2.5 rounded-md px-1 {{ $focus }}">
                <img src="{{ asset('icons/logo-black.png') }}" alt="{{ config('app.name') }}"
                    class="size-8 shrink-0 object-contain dark:invert">
                <span class="text-base font-extrabold tracking-tight whitespace-nowrap">{{ config('app.name') }}</span>
            </a>

            <nav aria-label="Navigasi halaman" class="hidden lg:block">
                <ul class="flex items-center gap-1 text-sm font-medium whitespace-nowrap">
                    <li>
                        <a href="{{ route('home') }}"
                            class="inline-flex h-11 items-center rounded-md px-3 font-bold text-foreground underline decoration-primary decoration-2 underline-offset-4 transition-colors hover:bg-accent {{ $focus }}">
                            Halaman utama
                        </a>
                    </li>
                    @foreach ($navLinks as $href => $label)
                        <li>
                            <a href="{{ $href }}"
                                class="inline-flex h-11 items-center rounded-md px-3 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground {{ $focus }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="flex shrink-0 items-center gap-2">
                <button type="button" x-data
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
            <nav aria-label="Navigasi halaman seluler" class="mx-auto max-w-6xl px-4 py-3 sm:px-6">
                <ul class="grid gap-1 sm:grid-cols-2">
                    <li class="sm:col-span-2">
                        <a href="{{ route('home') }}"
                            class="flex h-11 items-center rounded-md border border-input bg-card px-3 text-base font-bold hover:bg-accent {{ $focus }}">
                            Kembali ke halaman utama promosi
                        </a>
                    </li>
                    @foreach ($navLinks as $href => $label)
                        <li>
                            <a href="{{ $href }}" x-on:click="open = false"
                                class="flex h-11 items-center rounded-md px-3 text-base font-medium hover:bg-accent {{ $focus }}">{{ $label }}</a>
                        </li>
                    @endforeach
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
        {{-- Hero states the point rule on the left and proves it with the interactive receipt on the right. --}}
        <section class="mx-auto grid max-w-6xl gap-10 px-4 pt-8 pb-12 sm:px-6 sm:pt-12 sm:pb-16 lg:grid-cols-[1.08fr_0.92fr] lg:items-start lg:gap-14 lg:px-8 lg:pt-20 lg:pb-24">
            <div class="grid gap-6 md:grid-cols-2 md:items-start md:gap-8 lg:sticky lg:top-24 lg:grid-cols-1 lg:gap-6">
                <div class="flex flex-col gap-5">
                    <h1 class="text-[clamp(2.15rem,5.8vw,4.15rem)] leading-[1.05] font-extrabold tracking-tight text-balance">
                        Setiap {{ $spendStep }} di Soho jadi <span class="marker whitespace-nowrap">1 poin</span>.
                    </h1>
                    <p class="max-w-xl text-base leading-relaxed text-muted-foreground sm:text-lg">
                        Sebutkan nomor WhatsApp Anda saat membayar di kasir. Poinnya masuk ke kartu member digital,
                        lalu bisa ditukar dengan menu gratis atau potongan harga dari daftar reward.
                    </p>
                </div>

                <div class="flex flex-col gap-3">
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <a href="{{ $memberCta['href'] }}"
                            class="inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-primary px-5 py-2.5 text-base font-semibold text-primary-foreground transition-colors hover:bg-primary/85 dark:border-primary {{ $focus }}">
                            {{ $memberCta['label'] }}
                        </a>
                        <a href="#reward"
                            class="inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-background px-5 py-2.5 text-base font-semibold transition-colors hover:bg-accent {{ $focus }}">
                            Lihat reward yang bisa ditukar
                        </a>
                    </div>
                    @unless ($isMember)
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            Cukup nomor WhatsApp. Kode verifikasi 4 digit dikirim ke nomor itu, tanpa kata sandi.
                        </p>
                    @endunless
                </div>
            </div>

            <div id="hitung" class="scroll-mt-24 drop-shadow-md dark:drop-shadow-none">
                <div x-data="pointsEstimator(@js([
                        'items' => $menuItems,
                        'spendPerPoint' => $settings->spend_per_point,
                        'tiers' => $tiers->map(fn ($tier) => ['name' => $tier->name, 'multiplierHundredths' => $tier->multiplierHundredths()])->values(),
                        'checkPhoneUrl' => route('customer.check-phone'),
                    ]))"
                    class="receipt-edge w-full rounded-t-lg border border-b-0 border-border bg-card px-4 pt-5 text-card-foreground sm:px-6">
                    <div class="flex flex-col items-center gap-1 border-b border-dashed border-input pb-4 text-center">
                        <h2 class="text-lg font-bold tracking-tight">Hitung poin pesanan Anda</h2>
                        <p class="text-sm text-muted-foreground">Harga diambil langsung dari daftar menu kasir hari ini.</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-dashed border-input py-3 text-sm" aria-live="polite">
                        <span class="text-muted-foreground">Subtotal: <strong class="font-bold text-foreground tabular-nums" x-text="rupiah(subtotal)">Rp 0</strong></span>
                        <span class="font-bold tabular-nums">Estimasi: <span class="marker font-extrabold" x-text="points">0</span> poin</span>
                    </div>

                    <div class="grid gap-6 py-4 md:grid-cols-[1.15fr_0.85fr] md:items-start lg:grid-cols-1">
                        <div>
                            @if ($categories->isEmpty())
                                <p class="rounded-md border border-dashed border-input p-4 text-sm leading-relaxed text-muted-foreground">
                                    Daftar menu belum diisi di sistem kasir. Begitu admin menambahkan menu aktif, Anda bisa memilih item di sini untuk menghitung poin. Aturannya tetap: setiap {{ $spendStep }} dari subtotal belanja menjadi 1 poin.
                                </p>
                            @else
                                <div class="flex flex-col gap-5">
                                    @foreach ($categories as $category)
                                        <div>
                                            <h3 class="mb-1.5 text-sm font-semibold text-muted-foreground">{{ $category->name }}</h3>
                                            <ul class="flex flex-col gap-1">
                                                @foreach ($category->items as $item)
                                                    <li class="flex flex-wrap items-center justify-between gap-3 py-1">
                                                        <div class="leader-row min-w-[10rem] flex-1 text-sm">
                                                            <span class="min-w-0 font-medium break-words">{{ $item->name }}</span>
                                                            <span class="leader-fill" aria-hidden="true"></span>
                                                            <span class="shrink-0 tabular-nums">{{ Rupiah::format($item->price) }}</span>
                                                        </div>
                                                        <div class="flex shrink-0 items-center gap-1.5" role="group" aria-label="Jumlah {{ $item->name }}">
                                                            <button type="button" x-on:click="remove({{ $item->id }})"
                                                                x-bind:disabled="quantity({{ $item->id }}) === 0"
                                                                aria-label="Kurangi {{ $item->name }}"
                                                                class="inline-flex size-11 items-center justify-center rounded-md border border-input bg-background text-lg font-semibold transition-colors hover:bg-accent disabled:cursor-not-allowed disabled:opacity-40 {{ $focus }}">&minus;</button>
                                                            <span class="w-7 text-center text-sm font-semibold tabular-nums"
                                                                x-text="quantity({{ $item->id }})">0</span>
                                                            <button type="button" x-on:click="add({{ $item->id }})"
                                                                aria-label="Tambah {{ $item->name }}"
                                                                class="inline-flex size-11 items-center justify-center rounded-md border border-input bg-background text-lg font-semibold transition-colors hover:bg-accent {{ $focus }}">+</button>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-4 border-t border-dashed border-input pt-4 md:border-t-0 md:border-l md:pt-0 md:pl-5 lg:border-t lg:border-l-0 lg:pt-4 lg:pl-0">
                            <div class="leader-row text-sm">
                                <span>Subtotal</span>
                                <span class="leader-fill" aria-hidden="true"></span>
                                <span class="font-semibold tabular-nums" x-text="rupiah(subtotal)">Rp 0</span>
                            </div>

                            @if ($tierCount > 0)
                                <fieldset class="flex flex-col gap-2">
                                    <legend class="mb-2 text-sm font-medium">Tingkat member Anda</legend>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($tiers as $index => $tier)
                                            <label
                                                class="inline-flex min-h-11 cursor-pointer items-center gap-1.5 rounded-md border border-input bg-background px-3 text-sm transition-colors hover:bg-accent has-[input:checked]:border-foreground has-[input:checked]:bg-foreground has-[input:checked]:text-background has-[input:focus-visible]:outline-2 has-[input:focus-visible]:outline-offset-2 has-[input:focus-visible]:outline-foreground">
                                                <input type="radio" name="tier" value="{{ $index }}" x-model.number="tierIndex"
                                                    class="sr-only" @checked($index === 0)>
                                                <span class="font-semibold">{{ $tier->name }}</span>
                                                <span class="tabular-nums">{{ $tier->multiplierLabel() }}x</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endif

                            <div class="leader-row" aria-live="polite">
                                <span class="text-sm font-semibold">Poin didapat</span>
                                <span class="leader-fill" aria-hidden="true"></span>
                                <span class="text-2xl font-extrabold tabular-nums"><span class="marker" x-text="points">0</span> poin</span>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 text-sm text-muted-foreground">
                                <p x-show="itemCount === 0">Belum ada menu dipilih. Tekan tombol + di daftar menu untuk menghitung poin.</p>
                                <p x-cloak x-show="itemCount > 0">
                                    <span x-text="rupiah(spendToNextPoint)"></span> lagi menuju kelipatan {{ $spendStep }} berikutnya.
                                </p>
                                <button type="button" x-cloak x-show="itemCount > 0" x-on:click="clear()"
                                    class="inline-flex min-h-11 items-center rounded-md px-2 font-semibold text-foreground underline underline-offset-4 hover:bg-accent {{ $focus }}">
                                    Kosongkan
                                </button>
                            </div>
                            <p class="text-xs leading-relaxed text-muted-foreground">
                                Poin dihitung dari subtotal sebelum potongan voucher dan pajak.
                            </p>
                        </div>
                    </div>

                    {{-- Phone check widget gives guests a live member-status check with explicit empty, loading, and error states. --}}
                    <form x-on:submit.prevent="checkPhone()" class="flex flex-col gap-3 border-t border-dashed border-input pt-4">
                        <label for="cek-nomor-wa" class="text-sm font-semibold">
                            Cek apakah nomor WhatsApp Anda sudah terdaftar
                        </label>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input id="cek-nomor-wa" type="tel" inputmode="tel" autocomplete="tel"
                                x-model="phone"
                                placeholder="Nomor WhatsApp Anda (08xx...)"
                                class="h-11 w-full flex-1 scroll-my-24 rounded-md border border-input bg-background px-3 text-sm tabular-nums text-foreground placeholder:text-muted-foreground {{ $focus }}">
                            <button type="submit" x-bind:disabled="phoneState === 'loading'"
                                class="inline-flex h-11 shrink-0 items-center justify-center rounded-md border border-foreground bg-foreground px-4 text-sm font-semibold text-background transition-colors hover:bg-foreground/85 disabled:cursor-wait disabled:opacity-60 {{ $focus }}">
                                <span x-show="phoneState !== 'loading'">Periksa</span>
                                <span x-cloak x-show="phoneState === 'loading'">Memeriksa...</span>
                            </button>
                        </div>

                        <div role="status" aria-live="polite" class="text-xs leading-relaxed">
                            <p x-show="phoneState === 'idle'" class="text-muted-foreground">
                                Belum ada nomor yang diperiksa. Ketik nomor WhatsApp Anda lalu pilih Periksa untuk melihat status member.
                            </p>
                            <p x-cloak x-show="phoneState === 'loading'" class="font-medium text-foreground">
                                Sedang memeriksa nomor WhatsApp di daftar member Soho...
                            </p>
                            <p x-cloak x-show="phoneState === 'error'" class="font-medium text-destructive">
                                Perlu diperbaiki: <span x-text="phoneError"></span>
                            </p>
                            <p x-cloak x-show="phoneState === 'registered'" class="font-medium text-foreground">
                                Status: Terdaftar. Nomor ini sudah punya kartu member Soho. <a href="{{ route('customer.login') }}" class="font-bold underline underline-offset-4 {{ $focus }}">Masuk untuk lihat saldo poin</a>.
                            </p>
                            <p x-cloak x-show="phoneState === 'unregistered'" class="font-medium text-foreground">
                                Status: Belum terdaftar. <a href="{{ route('customer.login') }}" class="font-bold underline underline-offset-4 {{ $focus }}">Daftar sekarang dengan kode OTP WhatsApp</a> atau minta kasir mendaftarkan saat membayar.
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- Four steps match the actual four-stage flow from registration to PIN verification at the register. --}}
        <section id="cara-kerja" class="scroll-mt-20 border-t border-border">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-12 lg:gap-12 lg:px-8 lg:py-24">
                <div class="lg:col-span-4">
                    <div class="flex flex-col gap-3 lg:sticky lg:top-28">
                        <h2 class="text-[clamp(1.75rem,3.6vw,2.75rem)] leading-tight font-extrabold tracking-tight">Nomor WhatsApp Anda adalah kartu membernya</h2>
                        <p class="leading-relaxed text-muted-foreground">Tidak ada kartu fisik yang perlu dibawa. Kasir mencari keanggotaan Anda dari nomor itu.</p>
                    </div>
                </div>
                <ol class="grid gap-8 sm:grid-cols-2 lg:col-span-8 lg:gap-10">
                    <li class="flex flex-col gap-2 border-t-2 border-foreground pt-4">
                        <span class="text-3xl font-extrabold tabular-nums" aria-hidden="true">01</span>
                        <h3 class="text-lg font-bold">Daftar sekali</h3>
                        <p class="leading-relaxed text-muted-foreground">Minta kasir mendaftarkan nomor Anda saat membayar, atau daftar sendiri lewat halaman masuk member dengan kode dari WhatsApp.</p>
                    </li>
                    <li class="flex flex-col gap-2 border-t-2 border-foreground pt-4">
                        <span class="text-3xl font-extrabold tabular-nums" aria-hidden="true">02</span>
                        <h3 class="text-lg font-bold">Bayar seperti biasa</h3>
                        <p class="leading-relaxed text-muted-foreground">Sebutkan nomor Anda ke kasir. Poin dari transaksi itu langsung masuk ke saldo, dan struknya tersimpan di Riwayat.</p>
                    </li>
                    <li class="flex flex-col gap-2 border-t-2 border-foreground pt-4">
                        <span class="text-3xl font-extrabold tabular-nums" aria-hidden="true">03</span>
                        <h3 class="text-lg font-bold">Tukar poin di aplikasi</h3>
                        <p class="leading-relaxed text-muted-foreground">Buka Katalog Reward dan pilih voucher. Poin terpotong saat itu juga, dan vouchernya masuk ke Kupon Saya.</p>
                    </li>
                    <li class="flex flex-col gap-2 border-t-2 border-foreground pt-4">
                        <span class="text-3xl font-extrabold tabular-nums" aria-hidden="true">04</span>
                        <h3 class="text-lg font-bold">Pakai di kasir dengan PIN</h3>
                        <p class="leading-relaxed text-muted-foreground">Tunjukkan kode voucher ke kasir, lalu ketik sendiri PIN 6 digit Anda. Buat PIN dulu di menu PIN Akun sebelum memakai voucher pertama.</p>
                    </li>
                </ol>
            </div>
        </section>

        {{-- Tier staircase scales width on phone and height on tablet/desktop so progression is visible at a glance. --}}
        <section id="tier" class="scroll-mt-20 border-t border-border bg-sidebar">
            <div class="mx-auto flex max-w-6xl flex-col gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
                <div class="grid gap-4 lg:grid-cols-2 lg:items-end lg:gap-12">
                    <h2 class="text-[clamp(1.75rem,3.6vw,2.75rem)] leading-tight font-extrabold tracking-tight">Belanja lebih banyak, poin per transaksi ikut naik</h2>
                    <p class="leading-relaxed text-muted-foreground">
                        Tingkat naik otomatis begitu total belanja Anda sejak mendaftar mencapai batasnya.
                        Pengali tingkat berlaku untuk poin dari setiap transaksi berikutnya.
                    </p>
                </div>

                @if ($tierCount === 0)
                    <p class="rounded-lg border border-dashed border-input p-6 text-muted-foreground">
                        Tingkat member belum diatur di panel admin. Selama belum ada tingkat tambahan, setiap member mengumpulkan poin dengan pengali 1x.
                    </p>
                @else
                    <ol class="flex flex-col gap-3 sm:flex-row sm:items-end sm:gap-4">
                        @foreach ($tiers as $index => $tier)
                            @php
                                $isTop = $index === $tierCount - 1 && $tierCount > 1;
                                $step = $tierCount > 1 ? $index / ($tierCount - 1) : 1;
                            @endphp
                            <li style="--step: {{ $step }}"
                                class="flex w-[calc(68%+32%*var(--step))] flex-col justify-end gap-1 rounded-lg border p-4 sm:min-h-[calc(9.5rem+8.5rem*var(--step))] sm:w-auto sm:flex-1 sm:p-5 {{ $isTop ? 'border-foreground bg-foreground text-background' : 'border-border bg-card text-card-foreground' }}">
                                <span class="text-xl font-extrabold tracking-tight sm:text-2xl">{{ $tier->name }}</span>
                                <span class="text-sm {{ $isTop ? 'text-background/85' : 'text-muted-foreground' }}">
                                    @if ($tier->isBase())
                                        Langsung saat mendaftar
                                    @else
                                        Total belanja mulai {{ Rupiah::format($tier->min_spend) }}
                                    @endif
                                </span>
                                <span class="mt-2 text-lg font-bold tabular-nums">{{ $tier->multiplierLabel() }}x poin</span>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </section>

        {{-- Reward catalog uses the same dotted-leader menu ledger as the receipt so point costs read like prices. --}}
        <section id="reward" class="scroll-mt-20 border-t border-border bg-card text-card-foreground">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-12 lg:gap-14 lg:px-8 lg:py-24">
                <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between lg:col-span-5 lg:flex-col lg:items-start lg:justify-start">
                    <div class="flex flex-col gap-3">
                        <h2 class="text-[clamp(1.75rem,3.6vw,2.75rem)] leading-tight font-extrabold tracking-tight">Yang bisa ditukar sekarang</h2>
                        <p class="max-w-xl leading-relaxed text-muted-foreground">
                            Daftar ini sama dengan Katalog Reward di aplikasi member. Voucher berlaku sejak ditukar,
                            dengan masa berlaku yang tertulis di tiap baris.
                        </p>
                    </div>
                    <a href="{{ route('customer.rewards.index') }}"
                        class="inline-flex min-h-12 w-full shrink-0 items-center justify-center rounded-md border border-foreground bg-foreground px-5 py-2.5 text-base font-semibold text-background transition-colors hover:bg-foreground/85 sm:w-auto {{ $focus }}">
                        Tukar poin di aplikasi
                    </a>
                </div>

                <div class="lg:col-span-7">
                    @if ($vouchers->isEmpty())
                        <p class="rounded-lg border border-dashed border-input p-6 leading-relaxed text-muted-foreground">
                            Belum ada reward yang dibuka saat ini. Anda tetap bisa mengumpulkan poin setiap berbelanja, lalu menukarkannya begitu admin membuka reward baru di aplikasi.
                        </p>
                    @else
                        <ul class="grid gap-x-10 divide-y divide-border border-y border-border md:grid-cols-2 lg:grid-cols-1">
                            @foreach ($vouchers as $voucher)
                                <li class="flex flex-col gap-1.5 py-5">
                                    <div class="leader-row text-lg">
                                        <span class="min-w-0 font-bold break-words">{{ $voucher->name }}</span>
                                        <span class="leader-fill" aria-hidden="true"></span>
                                        <span class="shrink-0 font-extrabold tabular-nums">{{ number_format($voucher->points_cost, 0, ',', '.') }} poin</span>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        {{ $voucher->benefitLabel() }} · berlaku {{ $voucher->valid_days ?? 30 }} hari setelah ditukar
                                        @if ($voucher->quota === 0)
                                            · <strong class="font-semibold text-foreground">kuota habis</strong>
                                        @elseif ($voucher->quota !== null)
                                            · sisa {{ $voucher->quota }}
                                        @endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </section>

        {{-- Two physical-world actions: directions to the cafe and saving the PWA to the phone home screen. --}}
        <section id="lokasi" class="scroll-mt-20 border-t border-border">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:grid-cols-2 sm:gap-0 sm:px-6 sm:py-14 lg:px-8 lg:py-16">
                <div class="flex flex-col items-start gap-3 sm:pr-8 lg:pr-14">
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Mampir ke Soho</h2>
                    <p class="leading-relaxed text-muted-foreground">Petunjuk arah dan jam buka terbaru ada di halaman Google Maps kami.</p>
                    <a href="{{ config('app.location_url') }}" target="_blank" rel="noopener noreferrer"
                        class="mt-1 inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-background px-5 py-2.5 text-base font-semibold transition-colors hover:bg-accent {{ $focus }}">
                        Buka di Google Maps<span class="sr-only"> (tab baru)</span>
                    </a>
                </div>
                <div class="flex flex-col items-start gap-3 border-t border-border pt-10 sm:border-t-0 sm:border-l sm:pt-0 sm:pl-8 lg:pl-14">
                    <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Simpan kartu di layar HP</h2>
                    <p class="leading-relaxed text-muted-foreground">Kartu member bisa dipasang seperti aplikasi, tanpa lewat toko aplikasi.</p>
                    <button type="button" x-data x-show="$store.pwa?.canPrompt" x-on:click="$store.pwa.install()"
                        class="mt-1 inline-flex min-h-12 items-center justify-center rounded-md border border-foreground bg-background px-5 py-2.5 text-base font-semibold transition-colors hover:bg-accent {{ $focus }}">
                        Pasang aplikasi member
                    </button>
                    <p x-data x-cloak x-show="!$store.pwa?.canPrompt" class="mt-1 text-sm font-semibold">
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
                <a href="{{ route('home') }}"
                    class="inline-flex min-h-11 items-center rounded-md px-2 font-medium underline underline-offset-4 hover:text-foreground {{ $focus }}">Halaman utama</a>
                <a href="{{ route('customer.login') }}"
                    class="inline-flex min-h-11 items-center rounded-md px-2 font-medium underline underline-offset-4 hover:text-foreground {{ $focus }}">Masuk member</a>
                <a href="{{ route('login') }}"
                    class="inline-flex min-h-11 items-center rounded-md px-2 font-medium underline underline-offset-4 hover:text-foreground {{ $focus }}">Masuk staf</a>
            </div>
        </div>
    </footer>

    <div x-data x-cloak x-show="$store.pwa?.showIOSModal || $store.pwa?.showAndroidModal"
        x-on:keydown.escape.window="$store.pwa.closeModals()"
        role="dialog" aria-modal="true" aria-labelledby="judul-panduan-pasang"
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 p-0 sm:items-center sm:p-4">
        <div class="fixed inset-0" x-on:click="$store.pwa.closeModals()" aria-hidden="true"></div>
        <div class="relative z-10 w-full max-w-md rounded-t-lg border border-border bg-card p-5 text-card-foreground sm:rounded-lg sm:p-6">
            <div class="flex items-start justify-between gap-4 border-b border-border pb-4">
                <div>
                    <h2 id="judul-panduan-pasang" class="text-lg font-extrabold tracking-tight"
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
