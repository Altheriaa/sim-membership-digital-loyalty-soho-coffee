@props(['title'])

<!DOCTYPE html>
<html lang="id">
    <head>
        <x-head :title="$title" />
    </head>
    <body class="bg-background font-mono text-foreground antialiased">
        <main class="mx-auto flex min-h-svh w-full max-w-md flex-col justify-center gap-6 p-4 sm:p-8">
            {{ $slot }}
        </main>
        @aprilScripts
    </body>
</html>
