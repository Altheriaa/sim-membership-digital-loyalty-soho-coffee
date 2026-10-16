@props(['paginator'])

@if ($paginator->total() > 0)
    <div {{ $attributes->class('flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3 text-xs text-muted-foreground sm:px-6') }}>
        <p>Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }} dari {{ $paginator->total() }}</p>
        @if ($paginator->hasPages())
            <div class="flex items-center gap-2">
                @if ($paginator->onFirstPage())
                    <april:button variant="outline" size="none" disabled class="h-11 px-4 sm:h-9">Sebelumnya</april:button>
                @else
                    <april:button-link href="{{ $paginator->previousPageUrl() }}" variant="outline" size="none" class="h-11 px-4 sm:h-9">Sebelumnya</april:button-link>
                @endif
                @if ($paginator->hasMorePages())
                    <april:button-link href="{{ $paginator->nextPageUrl() }}" variant="outline" size="none" class="h-11 px-4 sm:h-9">Berikutnya</april:button-link>
                @else
                    <april:button variant="outline" size="none" disabled class="h-11 px-4 sm:h-9">Berikutnya</april:button>
                @endif
            </div>
        @endif
    </div>
@endif
