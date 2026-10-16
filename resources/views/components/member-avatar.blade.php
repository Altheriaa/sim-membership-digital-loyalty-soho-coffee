@props(['name'])

@php
    $initials = str($name)
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

<span {{ $attributes->class('flex size-9 shrink-0 items-center justify-center rounded-full border border-border bg-muted text-xs font-medium text-foreground') }} aria-hidden="true">{{ $initials }}</span>
