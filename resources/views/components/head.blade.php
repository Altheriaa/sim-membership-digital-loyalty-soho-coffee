@props(['title'])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<script>
    (() => {
        let saved = null;

        try {
            saved = localStorage.getItem('theme');
        } catch {
            // Without storage the page follows the system theme.
        }

        document.documentElement.classList.toggle('dark',
            saved === 'dark' || (saved !== 'light' && matchMedia('(prefers-color-scheme: dark)').matches)
        );
    })();
</script>
<title>{{ $title }} · {{ config('app.name') }}</title>
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#1c325c">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
<link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
@aprilStyles
@aprilScripts
@vite(['resources/css/app.css', 'resources/js/app.js'])
