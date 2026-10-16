@use('App\VoucherType')

@php
    $isEditing = $voucher->exists;
    $title = $isEditing ? 'Ubah voucer' : 'Tambah voucer';
    $currentType = old('type', $voucher->type?->value ?? VoucherType::DiscountAmount->value);
@endphp

<x-layouts.admin :title="$title">
    <form
        method="POST"
        action="{{ $isEditing ? route('admin.vouchers.update', $voucher) : route('admin.vouchers.store') }}"
        x-data="{ type: @js($currentType) }"
        class="flex max-w-xl flex-col gap-5 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6"
    >
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <x-field name="name" label="Nama voucer" :value="$voucher->name" maxlength="100" required />

        <x-field name="type" label="Jenis hadiah" as="select" x-model="type" required>
            @foreach (VoucherType::cases() as $type)
                <option value="{{ $type->value }}" @selected($currentType === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </x-field>

        <div x-show="type === '{{ VoucherType::FreeItem->value }}'" @if ($currentType !== VoucherType::FreeItem->value) style="display: none" @endif>
            <x-field name="menu_item_id" label="Menu yang diberikan gratis" as="select">
                <option value="">Pilih menu</option>
                @foreach ($menuItems as $menuItem)
                    <option value="{{ $menuItem->id }}" @selected((int) old('menu_item_id', $voucher->menu_item_id) === $menuItem->id)>{{ $menuItem->name }}</option>
                @endforeach
            </x-field>
        </div>

        <div x-show="type === '{{ VoucherType::DiscountAmount->value }}'" @if ($currentType !== VoucherType::DiscountAmount->value) style="display: none" @endif>
            <x-field name="discount_amount" label="Nominal diskon (Rp)" type="number" :value="$voucher->discount_amount" min="1" step="500" inputmode="numeric" />
        </div>

        <x-field name="points_cost" label="Harga dalam poin" type="number" :value="$voucher->points_cost" min="1" inputmode="numeric" hint="Jumlah poin yang dipotong saat member menukar voucer ini." required />
        <x-field name="quota" label="Kuota klaim" type="number" :value="$voucher->quota" min="1" inputmode="numeric" optional hint="Kosongkan untuk tanpa batas." />
        <x-field name="valid_days" label="Masa berlaku setelah diklaim (hari)" type="number" :value="$voucher->valid_days" min="1" max="365" inputmode="numeric" required />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-field name="available_from" label="Mulai bisa diklaim" type="date" :value="$voucher->available_from?->toDateString()" optional />
            <x-field name="available_until" label="Terakhir bisa diklaim" type="date" :value="$voucher->available_until?->toDateString()" optional />
        </div>

        <x-checkbox-field name="is_active" label="Voucer aktif" :checked="$voucher->is_active" hint="Voucer nonaktif tidak tampil di katalog reward." />

        <div class="flex flex-wrap items-center gap-2">
            <april:button type="submit" size="none" class="h-11 px-4 sm:h-10">Simpan</april:button>
            <april:button-link href="{{ route('admin.vouchers.index') }}" variant="outline" size="none" class="h-11 px-4 sm:h-10">Batal</april:button-link>
        </div>
    </form>

    @if ($isEditing)
        <form method="POST" action="{{ route('admin.vouchers.destroy', $voucher) }}" x-data x-on:submit="if (! confirm('Hapus voucer {{ e($voucher->name) }}?')) $event.preventDefault()" class="flex max-w-xl flex-col gap-2 rounded-lg border border-destructive p-4 sm:p-6">
            @csrf
            @method('DELETE')
            <h2 class="font-serif text-lg font-medium text-destructive">Hapus voucer</h2>
            <p class="text-sm text-muted-foreground">Voucer hilang dari katalog. Kupon yang sudah diklaim member tetap tercatat.</p>
            <april:button type="submit" variant="destructive" size="none" class="h-11 w-fit px-4 sm:h-10">Hapus voucer</april:button>
        </form>
    @endif
</x-layouts.admin>
