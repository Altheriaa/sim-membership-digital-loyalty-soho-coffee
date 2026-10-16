@props(['title'])

<!DOCTYPE html>
<html lang="id">
    <head>
        <x-head :title="$title" />
    </head>
    <body class="bg-background font-sans text-foreground antialiased">
        <a href="#main-content" class="sr-only z-50 rounded-md bg-primary px-4 py-2 text-primary-foreground focus:not-sr-only focus:fixed focus:top-4 focus:left-4">
            Lewati ke konten utama
        </a>

        <april:sidebar-layout class="h-svh overflow-hidden" width="16rem">
            <x-admin-sidebar />

            <april:sidebar-inset class="overflow-y-auto">
                <header class="sticky top-0 z-20 flex min-h-16 shrink-0 items-center justify-between gap-3 border-b border-border bg-background px-4 sm:px-8">
                    <div class="flex min-w-0 items-center gap-2">
                        <april:sidebar-trigger class="size-11 shrink-0 md:size-9" aria-label="Buka atau tutup sidebar" />
                        <h1 class="truncate font-serif text-2xl font-medium tracking-tight sm:text-3xl">{{ $title }}</h1>
                    </div>
                    @isset($actions)
                        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
                    @endisset
                </header>

                <div id="main-content" tabindex="-1" class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-4 outline-none sm:gap-8 sm:p-8">
                    <x-alert-summary />
                    {{ $slot }}
                </div>

                <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3 text-xs text-muted-foreground sm:px-8">
                    <p>{{ config('app.name') }} &copy; {{ now()->year }}</p>
                    <x-theme-toggle />
                </footer>
            </april:sidebar-inset>
        </april:sidebar-layout>

        @aprilScripts
    </body>
</html>
