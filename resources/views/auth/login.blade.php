<x-layouts.guest title="Masuk">
    <header class="flex flex-col gap-1">
        <p class="font-serif text-xl font-semibold">{{ config('app.name') }}</p>
        <h1 class="font-serif text-3xl font-medium tracking-tight">Masuk ke back office</h1>
        <p class="text-sm text-muted-foreground">Untuk admin, owner, dan kasir.</p>
    </header>

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6">
        @csrf

        <x-field name="email" label="Email" type="email" autocomplete="username" required autofocus />
        <x-field name="password" label="Kata sandi" type="password" autocomplete="current-password" required />
        <x-checkbox-field name="remember" label="Tetap masuk di perangkat ini" />

        <april:button type="submit" size="none" class="h-11 px-4">Masuk</april:button>
    </form>

    <div class="flex justify-end">
        <x-theme-toggle />
    </div>
</x-layouts.guest>
