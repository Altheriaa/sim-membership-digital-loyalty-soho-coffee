@props(['title', 'maxWidth' => 'max-w-md', 'centered' => true])

<!DOCTYPE html>
<html lang="id">
    <head>
        <x-head :title="$title" />
    </head>
    <body class="bg-background font-sans text-foreground antialiased">
        <main {{ $attributes->class([
            'mx-auto flex min-h-svh w-full flex-col gap-6 p-4 sm:p-8',
            $maxWidth,
            'justify-center' => $centered,
        ]) }}>
            {{ $slot }}
        </main>
        @aprilScripts
    </body>
</html>
