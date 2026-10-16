@if (session('status'))
    <div role="status" class="rounded-lg border border-border bg-card px-4 py-3 text-sm text-card-foreground">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div role="alert" class="rounded-lg border border-destructive p-4 text-sm text-destructive">
        <p class="font-medium">Ada yang perlu diperbaiki</p>
        <ul class="mt-1 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
