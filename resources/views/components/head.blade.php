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
@aprilStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])
