<x-layouts.admin :title="'Ubah data · ' . $member->name">
    <form method="POST" action="{{ route('admin.members.update', $member) }}" class="flex max-w-xl flex-col gap-5 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6">
        @csrf
        @method('PUT')

        <x-field name="name" label="Nama Member" :value="$member->name" maxlength="50" required />
        <x-field name="phone" label="Nomor WhatsApp" :value="$member->displayPhone()" placeholder="0812-3456-7890" hint="Gunakan format nomor HP Indonesia, misalnya 0812 3456 7890." required />

        <div class="flex flex-wrap items-center gap-2">
            <april:button type="submit" size="none" class="h-10 px-4 text-xs font-medium">Simpan perubahan</april:button>
            <april:button-link href="{{ route('admin.members.show', $member) }}" variant="outline" size="none" class="h-10 px-4 text-xs">Batal</april:button-link>
        </div>
    </form>
</x-layouts.admin>
