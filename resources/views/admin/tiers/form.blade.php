@php
    $isEditing = $tier->exists;
    $isBase = $isEditing && $tier->isBase();
    $title = $isEditing ? 'Ubah tier' : 'Tambah tier';
@endphp

<x-layouts.admin :title="$title">
    <form method="POST" action="{{ $isEditing ? route('admin.tiers.update', $tier) : route('admin.tiers.store') }}" class="flex max-w-xl flex-col gap-5 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <x-field name="name" label="Nama tier" :value="$tier->name" maxlength="40" required />

        @if ($isBase)
            <input type="hidden" name="min_spend" value="0">
            <p class="text-sm text-muted-foreground">Ini tier dasar. Syarat belanjanya tetap Rp 0 supaya setiap member punya tier.</p>
        @else
            <x-field name="min_spend" label="Syarat total belanja (Rp)" type="number" :value="$tier->min_spend" min="0" step="1000" inputmode="numeric" hint="Member naik ke tier ini setelah total belanjanya mencapai angka ini." required />
        @endif

        <x-field name="multiplier" label="Pengali poin" type="number" :value="$tier->multiplier" min="1" max="10" step="0.05" inputmode="decimal" hint="1 berarti poin normal, 1,5 berarti 50% lebih banyak." required />

        <div class="flex flex-wrap items-center gap-2">
            <april:button type="submit" size="none" class="h-11 px-4 sm:h-10">Simpan</april:button>
            <april:button-link href="{{ route('admin.tiers.index') }}" variant="outline" size="none" class="h-11 px-4 sm:h-10">Batal</april:button-link>
        </div>
    </form>

    @if ($isEditing && ! $isBase)
        <form method="POST" action="{{ route('admin.tiers.destroy', $tier) }}" x-data x-on:submit="if (! confirm('Hapus tier {{ e($tier->name) }}?')) $event.preventDefault()" class="flex max-w-xl flex-col gap-2 rounded-lg border border-destructive p-4 sm:p-6">
            @csrf
            @method('DELETE')
            <h2 class="font-serif text-lg font-medium text-destructive">Hapus tier</h2>
            <p class="text-sm text-muted-foreground">Member di tier ini turun ke tier di bawahnya sesuai total belanja mereka.</p>
            <april:button type="submit" variant="destructive" size="none" class="h-11 w-fit px-4 sm:h-10">Hapus tier</april:button>
        </form>
    @endif
</x-layouts.admin>
