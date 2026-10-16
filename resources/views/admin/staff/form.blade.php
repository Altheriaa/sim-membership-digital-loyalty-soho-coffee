@php
    $isEditing = $staff->exists;
    $editingSelf = $isEditing && $staff->is(auth()->user());
    $title = $isEditing ? 'Ubah staf' : 'Tambah staf';
@endphp

<x-layouts.admin :title="$title">
    <form method="POST" action="{{ $isEditing ? route('admin.staff.update', $staff) : route('admin.staff.store') }}" class="flex max-w-xl flex-col gap-5 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6">
        @csrf
        @if ($isEditing)
            @method('PUT')
        @endif

        <x-field name="name" label="Nama" :value="$staff->name" maxlength="100" autocomplete="name" required />
        <x-field name="email" label="Email" type="email" :value="$staff->email" autocomplete="off" required />

        @if ($editingSelf)
            <p class="text-sm text-muted-foreground">
                Peran kamu ({{ $staff->role->label() }}) dan status akun tidak bisa diubah dari akun sendiri.
            </p>
        @else
            <x-field name="role" label="Peran" as="select" required>
                @foreach (\App\Role::assignableByAdmin() as $role)
                    <option value="{{ $role->value }}" @selected(old('role', $staff->role?->value) === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </x-field>
            <x-checkbox-field name="is_active" label="Akun aktif" :checked="$staff->is_active" hint="Akun nonaktif tidak bisa masuk." />
        @endif

        <x-field
            name="password"
            label="{{ $isEditing ? 'Kata sandi baru' : 'Kata sandi' }}"
            type="password"
            autocomplete="new-password"
            :optional="$isEditing"
            :hint="$isEditing ? 'Kosongkan jika tidak ingin mengganti. Minimal 8 karakter.' : 'Minimal 8 karakter.'"
            :required="! $isEditing"
        />

        <div class="flex flex-wrap items-center gap-2">
            <april:button type="submit" size="none" class="h-11 px-4 sm:h-10">Simpan</april:button>
            <april:button-link href="{{ route('admin.staff.index') }}" variant="outline" size="none" class="h-11 px-4 sm:h-10">Batal</april:button-link>
        </div>
    </form>
</x-layouts.admin>
