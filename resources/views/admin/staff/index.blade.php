@php
    $totalStaff = array_sum($counts);
    $filterQuery = fn (?string $role): array => array_filter(['role' => $role, 'search' => $search]);
    $tabClass = 'inline-flex min-h-11 items-center gap-2 rounded-md px-3 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring aria-[current=page]:bg-accent aria-[current=page]:font-medium aria-[current=page]:text-foreground sm:min-h-9';
@endphp

<x-layouts.admin title="Staf">
    <x-slot:actions>
        <april:button-link href="{{ route('admin.staff.create') }}" size="none" class="h-11 px-4 sm:h-10">Tambah staf</april:button-link>
    </x-slot:actions>

    <section aria-label="Daftar staf" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        <div class="flex flex-col gap-3 border-b border-border p-3 lg:flex-row lg:items-center lg:justify-between">
            <nav aria-label="Filter berdasarkan peran" class="flex flex-wrap gap-1">
                <a href="{{ route('admin.staff.index', $filterQuery(null)) }}" @if ($activeRole === null) aria-current="page" @endif class="{{ $tabClass }}">
                    Semua peran <span class="tabular-nums text-xs">{{ $totalStaff }}</span>
                </a>
                @foreach ($roles as $role)
                    <a href="{{ route('admin.staff.index', $filterQuery($role->value)) }}" @if ($activeRole === $role) aria-current="page" @endif class="{{ $tabClass }}">
                        {{ $role->label() }} <span class="tabular-nums text-xs">{{ $counts[$role->value] ?? 0 }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('admin.staff.index') }}" role="search" class="flex items-center gap-2 lg:w-80">
                @if ($activeRole)
                    <input type="hidden" name="role" value="{{ $activeRole->value }}">
                @endif
                <div class="relative min-w-0 flex-1">
                    <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground" />
                    <april:input type="search" name="search" value="{{ $search }}" maxlength="100" placeholder="Cari nama atau email" aria-label="Cari staf" class="h-11 pl-9 sm:h-10" />
                </div>
                <april:button type="submit" variant="outline" size="none" class="h-11 shrink-0 px-4 sm:h-10">Cari</april:button>
            </form>
        </div>

        @if ($staff->isEmpty())
            <div class="px-4 py-12 text-center sm:px-6">
                <p class="font-serif text-lg">
                    @if ($search && $activeRole)
                        Tidak ada {{ strtolower($activeRole->label()) }} yang cocok dengan “{{ $search }}”
                    @elseif ($search)
                        Tidak ada staf yang cocok dengan “{{ $search }}”
                    @else
                        Belum ada staf dengan peran {{ $activeRole->label() }}
                    @endif
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ $search ? 'Periksa ejaan, atau cari dengan sebagian nama atau email.' : 'Tambahkan akun baru untuk peran ini.' }}
                </p>
                <div class="mt-4 flex flex-wrap justify-center gap-2">
                    @if ($search)
                        <april:button-link href="{{ route('admin.staff.index', array_filter(['role' => $activeRole?->value])) }}" variant="outline" size="none" class="h-11 px-4 sm:h-10">Hapus pencarian</april:button-link>
                    @endif
                    @if ($activeRole)
                        <april:button-link href="{{ route('admin.staff.index', array_filter(['search' => $search])) }}" variant="outline" size="none" class="h-11 px-4 sm:h-10">Tampilkan semua peran</april:button-link>
                    @endif
                </div>
            </div>
        @else
            <table class="w-full text-sm">
                <caption class="sr-only">Daftar staf, diurutkan menurut nama</caption>
                <thead>
                    <tr class="border-b border-border text-left text-muted-foreground">
                        <th scope="col" class="w-3/5 px-4 py-3 font-medium sm:px-6">Nama</th>
                        <th scope="col" class="px-4 py-3 font-medium sm:px-6">Peran</th>
                        <th scope="col" class="hidden px-4 py-3 font-medium sm:table-cell sm:px-6">Status</th>
                        <th scope="col" class="px-4 py-3 sm:px-6"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($staff as $member)
                        <tr>
                            <td class="max-w-0 px-4 py-3 sm:px-6">
                                <div class="flex min-w-0 items-center gap-3">
                                    <x-member-avatar :name="$member->name" />
                                    <div class="min-w-0">
                                        <p class="truncate font-medium">{{ $member->name }}</p>
                                        <p class="truncate text-xs text-muted-foreground">{{ $member->email }}</p>
                                        @unless ($member->is_active)
                                            <p class="text-xs text-muted-foreground sm:hidden">Nonaktif</p>
                                        @endunless
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 sm:px-6">{{ $member->role->label() }}</td>
                            <td class="hidden px-4 py-3 sm:table-cell sm:px-6">{{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="px-4 py-3 text-right sm:px-6">
                                @can('update', $member)
                                    <april:button-link href="{{ route('admin.staff.edit', $member) }}" variant="outline" size="none" class="h-11 px-3 sm:h-9">
                                        Ubah<span class="sr-only"> {{ $member->name }}</span>
                                    </april:button-link>
                                @else
                                    <span class="text-xs text-muted-foreground">Dikelola di luar panel</span>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <x-pagination :paginator="$staff" />
        @endif
    </section>
</x-layouts.admin>
