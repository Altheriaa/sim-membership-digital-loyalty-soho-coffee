@php
    $modes = ['system' => ['monitor', 'System'], 'light' => ['sun', 'Light'], 'dark' => ['moon', 'Dark']];
@endphp

<div x-data role="group" aria-label="Theme" {{ $attributes->class('inline-flex items-center gap-0.5 rounded-md border border-input p-0.5') }}>
    @foreach ($modes as $mode => [$icon, $label])
        <button
            type="button"
            title="{{ $label }} theme"
            aria-label="{{ $label }} theme"
            x-on:click="$store.theme.set('{{ $mode }}')"
            x-bind:aria-pressed="$store.theme.mode === '{{ $mode }}'"
            class="inline-flex size-11 items-center justify-center rounded-sm text-muted-foreground transition-colors hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring aria-pressed:bg-accent aria-pressed:text-foreground sm:size-8"
        >
            <x-dynamic-component :component="'lucide-' . $icon" class="size-4 shrink-0" />
        </button>
    @endforeach
</div>
