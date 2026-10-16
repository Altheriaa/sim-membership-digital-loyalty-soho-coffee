<x-layouts.guest title="Kasir">
    <header class="flex flex-col gap-1">
        <p class="text-sm text-muted-foreground">{{ auth()->user()->name }} · {{ auth()->user()->role->label() }}</p>
        <h1 class="font-serif text-3xl font-medium tracking-tight">Kasir</h1>
    </header>

    <section class="flex flex-col gap-2 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6" aria-labelledby="pos-status">
        <h2 id="pos-status" class="font-serif text-xl font-medium">Layar kasir belum tersedia</h2>
        <p class="text-sm text-muted-foreground">
            Keranjang, pencarian member, dan checkout dikerjakan di Tahap 2. Akun kamu sudah siap dipakai begitu layar itu selesai.
        </p>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        @if (auth()->user()->hasRole(\App\Role::Admin))
            <april:button-link href="{{ route('admin.dashboard') }}" variant="outline" size="none" class="h-11 px-4">Ke back office</april:button-link>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <april:button type="submit" variant="outline" size="none" class="h-11 px-4">Keluar</april:button>
        </form>
        <x-theme-toggle />
    </div>
</x-layouts.guest>
