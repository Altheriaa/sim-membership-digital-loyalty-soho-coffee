<!-- iOS Safari Installation Sheet / Modal -->
<div
    x-cloak
    x-show="$store.pwa?.showIOSModal"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-on:keydown.escape.window="$store.pwa.closeModals()"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4"
>
    <div class="fixed inset-0" x-on:click="$store.pwa.closeModals()" aria-hidden="true"></div>

    <div
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full sm:scale-95"
        x-transition:enter-end="translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 sm:scale-100"
        x-transition:leave-end="translate-y-full sm:scale-95"
        class="relative z-10 w-full max-w-md rounded-t-2xl sm:rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-2xl"
    >
        <div class="flex items-center justify-between border-b border-border/60 pb-3">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('icons/logo-black.png') }}" alt="{{ config('app.name') }}" class="size-8 object-contain dark:invert shrink-0">
                <div>
                    <h2 class="font-serif text-base font-bold leading-tight text-foreground">Pasang di iPhone / iPad</h2>
                    <p class="text-[11px] text-muted-foreground">Ikuti 3 langkah mudah berikut di Safari</p>
                </div>
            </div>
            <button
                type="button"
                x-on:click="$store.pwa.closeModals()"
                aria-label="Tutup panduan"
                class="flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground transition-colors"
            >
                <x-lucide-x class="size-4" />
            </button>
        </div>

        <ol class="mt-4 flex flex-col gap-3 text-xs">
            <li class="flex items-start gap-3 rounded-lg border border-border/50 bg-background/60 p-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/20 text-xs font-bold text-primary">1</span>
                <div class="flex-1">
                    <p class="font-semibold text-foreground">Ketuk tombol Bagikan (Share)</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5 leading-relaxed">
                        Tekan ikon kotak dengan panah ke atas
                        <x-lucide-share class="inline size-3.5 text-primary align-middle mx-0.5" />
                        pada bilah menu di bagian bawah layar Safari Anda.
                    </p>
                </div>
            </li>

            <li class="flex items-start gap-3 rounded-lg border border-border/50 bg-background/60 p-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/20 text-xs font-bold text-primary">2</span>
                <div class="flex-1">
                    <p class="font-semibold text-foreground">Pilih "Tambah ke Layar Utama"</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5 leading-relaxed">
                        Gulir menu ke bawah lalu pilih opsi
                        <span class="font-semibold text-foreground">"Tambah ke Layar Utama"</span> (atau <em>Add to Home Screen</em>
                        <x-lucide-plus-square class="inline size-3.5 text-primary align-middle mx-0.5" />).
                    </p>
                </div>
            </li>

            <li class="flex items-start gap-3 rounded-lg border border-border/50 bg-background/60 p-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/20 text-xs font-bold text-primary">3</span>
                <div class="flex-1">
                    <p class="font-semibold text-foreground">Ketuk "Tambah" (Add)</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5 leading-relaxed">
                        Ketuk tombol <span class="font-semibold text-foreground">"Tambah"</span> di sudut kanan atas layar. Selesai! Aplikasi Soho Coffee kini terpasang di layar utama iPhone Anda.
                    </p>
                </div>
            </li>
        </ol>

        <div class="mt-5">
            <button
                type="button"
                x-on:click="$store.pwa.closeModals()"
                class="flex h-11 w-full items-center justify-center rounded-lg bg-primary font-semibold text-xs text-primary-foreground hover:bg-primary/90 transition-colors shadow-xs"
            >
                Saya Mengerti
            </button>
        </div>
    </div>
</div>

<!-- Android Fallback Guide Modal -->
<div
    x-cloak
    x-show="$store.pwa?.showAndroidModal"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-on:keydown.escape.window="$store.pwa.closeModals()"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4"
>
    <div class="fixed inset-0" x-on:click="$store.pwa.closeModals()" aria-hidden="true"></div>

    <div
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full sm:scale-95"
        x-transition:enter-end="translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 sm:scale-100"
        x-transition:leave-end="translate-y-full sm:scale-95"
        class="relative z-10 w-full max-w-md rounded-t-2xl sm:rounded-2xl border border-border bg-card p-5 text-card-foreground shadow-2xl"
    >
        <div class="flex items-center justify-between border-b border-border/60 pb-3">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('icons/logo-black.png') }}" alt="{{ config('app.name') }}" class="size-8 object-contain dark:invert shrink-0">
                <div>
                    <h2 class="font-serif text-base font-bold leading-tight text-foreground">Pasang di HP Android</h2>
                    <p class="text-[11px] text-muted-foreground">Melalui menu browser Anda</p>
                </div>
            </div>
            <button
                type="button"
                x-on:click="$store.pwa.closeModals()"
                aria-label="Tutup panduan"
                class="flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground transition-colors"
            >
                <x-lucide-x class="size-4" />
            </button>
        </div>

        <ol class="mt-4 flex flex-col gap-3 text-xs">
            <li class="flex items-start gap-3 rounded-lg border border-border/50 bg-background/60 p-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/20 text-xs font-bold text-primary">1</span>
                <div class="flex-1">
                    <p class="font-semibold text-foreground">Buka Menu Browser (⋮)</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5 leading-relaxed">
                        Ketuk ikon titik tiga vertikal <span class="font-bold text-foreground">⋮</span> di pojok kanan atas browser (Chrome, Samsung Internet, Edge).
                    </p>
                </div>
            </li>

            <li class="flex items-start gap-3 rounded-lg border border-border/50 bg-background/60 p-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/20 text-xs font-bold text-primary">2</span>
                <div class="flex-1">
                    <p class="font-semibold text-foreground">Pilih "Pasang Aplikasi" / "Tambahkan ke Layar Utama"</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5 leading-relaxed">
                        Pilih opsi <span class="font-semibold text-foreground">"Pasang aplikasi"</span> (<em>Install app</em>) atau <span class="font-semibold text-foreground">"Tambahkan ke Layar Utama"</span>.
                    </p>
                </div>
            </li>

            <li class="flex items-start gap-3 rounded-lg border border-border/50 bg-background/60 p-3">
                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-primary/20 text-xs font-bold text-primary">3</span>
                <div class="flex-1">
                    <p class="font-semibold text-foreground">Konfirmasi Pemasangan</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5 leading-relaxed">
                        Ketuk <span class="font-semibold text-foreground">"Pasang"</span> / <span class="font-semibold text-foreground">"Tambahkan"</span>. Aplikasi Soho Coffee siap dibuka kapan saja dari layar smartphone.
                    </p>
                </div>
            </li>
        </ol>

        <div class="mt-5">
            <button
                type="button"
                x-on:click="$store.pwa.closeModals()"
                class="flex h-11 w-full items-center justify-center rounded-lg bg-primary font-semibold text-xs text-primary-foreground hover:bg-primary/90 transition-colors shadow-xs"
            >
                Saya Mengerti
            </button>
        </div>
    </div>
</div>
