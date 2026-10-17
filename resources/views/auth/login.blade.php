<x-layouts.guest title="Masuk">
    <header class="flex flex-col gap-1">
        <p class="font-serif text-2xl font-semibold">{{ config('app.name') }}</p>
        <p class="text-sm text-muted-foreground">Masuk sebagai Admin, Owner, atau Kasir</p>
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