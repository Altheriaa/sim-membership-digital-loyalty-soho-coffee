<x-layouts.guest title="Masuk">
    <header class="flex items-center gap-3">
        <img src="{{ asset('icons/logo-black.png') }}" alt="{{ config('app.name') }}"
            class="size-10 object-contain dark:invert shrink-0">
        <div class="flex flex-col">
            <p class="font-serif text-2xl font-semibold leading-tight">{{ config('app.name') }}</p>
            <p class="text-xs text-muted-foreground">Masuk sebagai Admin, Owner, atau Kasir</p>
        </div>
    </header>

    <form method="POST" action="{{ route('login') }}"
        class="flex flex-col gap-4 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6">
        @csrf

        <x-field name="email" label="Email" type="email" autocomplete="username" required autofocus />
        <x-field name="password" label="Kata sandi" type="password" autocomplete="current-password" required />
        <x-checkbox-field name="remember" label="Tetap masuk di perangkat ini" />

        <april:button type="submit" size="none" class="h-11 px-4">Masuk</april:button>
    </form>

    <div class="flex items-center justify-between">
        <button type="button" x-cloak x-show="$store.pwa?.canPrompt" x-on:click="$store.pwa.install()"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:underline"
            title="Pasang Aplikasi ke Smartphone">
            <x-lucide-download class="size-3.5" />
            <span>Install Soho Coffee</span>
        </button>
        <x-theme-toggle />
    </div>
</x-layouts.guest>