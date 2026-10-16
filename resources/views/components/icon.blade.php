@props(['name'])

<svg {{ $attributes->merge(['class' => 'size-4 shrink-0']) }} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home')
            <path d="m3 11 9-8 9 8 M5 9.5V21h14V9.5 M10 21v-6h4v6" />
            @break
        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M22 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75" /><circle cx="9" cy="7" r="4" />
            @break
        @case('coffee')
            <path d="M4 8h12v6a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V8Z M16 10h2a2 2 0 0 1 0 4h-2 M7 3v2 M11 3v2" />
            @break
        @case('tag')
            <path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z M7 7h.01" />
            @break
        @case('coins')
            <circle cx="9" cy="9" r="6" /><path d="M18.09 10.37A6 6 0 1 1 10.34 18 M7 6h1v4" />
            @break
        @case('award')
            <circle cx="12" cy="8" r="6" /><path d="M15.48 12.89 17 22l-5-3-5 3 1.52-9.11" />
            @break
        @case('ticket')
            <path d="M2 9a3 3 0 0 1 0 6v3a1 1 0 0 0 1 1h18a1 1 0 0 0 1-1v-3a3 3 0 0 1 0-6V6a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1Z M13 5v2 M13 17v2 M13 11v2" />
            @break
        @case('logout')
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9" />
            @break
        @case('search')
            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
            @break
        @case('monitor')
            <rect x="3" y="4" width="18" height="12" rx="2" /><path d="M8 20h8 M12 16v4" />
            @break
        @case('sun')
            <circle cx="12" cy="12" r="4" /><path d="M12 2v2 M12 20v2 M2 12h2 M20 12h2 M4.93 4.93l1.42 1.42 M17.65 17.65l1.42 1.42 M4.93 19.07l1.42-1.42 M17.65 6.35l1.42-1.42" />
            @break
        @case('moon')
            <path d="M20.9 13.1A9 9 0 0 1 10.9 3a9 9 0 1 0 10 10.1Z" />
            @break
    @endswitch
</svg>
