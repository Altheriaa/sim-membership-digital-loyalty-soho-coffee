@use('App\Models\Tier')
@use('App\Support\Rupiah')

<x-layouts.admin title="Member">
    <!-- Ringkasan Metrik Member -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <p class="text-xs font-medium text-muted-foreground">Total Member Terdaftar</p>
            <p class="mt-2 font-serif text-3xl font-semibold tabular-nums tracking-tight">{{ number_format($totalMembers) }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <p class="text-xs font-medium text-muted-foreground">Total Poin Beredar</p>
            <p class="mt-2 font-serif text-3xl font-semibold tabular-nums tracking-tight text-primary">{{ number_format($totalPoints) }}</p>
        </div>
        <div class="rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground">
            <p class="text-xs font-medium text-muted-foreground">Akumulasi Belanja Member</p>
            <p class="mt-2 font-serif text-3xl font-semibold tabular-nums tracking-tight">{{ Rupiah::format($totalSpend) }}</p>
        </div>
    </div>

    <section aria-label="Daftar keanggotaan" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        <!-- Search Bar -->
        <div class="flex flex-col gap-3 border-b border-border p-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-sm font-medium text-muted-foreground">Daftar Member Aktif</h2>

            <form method="GET" action="{{ route('admin.members.index') }}" role="search" class="flex items-center gap-2 sm:w-80">
                <div class="relative min-w-0 flex-1">
                    <x-lucide-search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        maxlength="100"
                        placeholder="Cari nama atau WhatsApp"
                        aria-label="Cari member"
                        class="h-10 w-full rounded-md border border-input bg-background pl-9 pr-3 text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                </div>
                <april:button type="submit" variant="outline" size="none" class="h-10 shrink-0 px-4">Cari</april:button>
            </form>
        </div>

        @if ($members->isEmpty())
            <div class="px-4 py-12 text-center sm:px-6">
                <p class="font-serif text-lg">
                    {{ $search ? "Tidak ada member yang cocok dengan \"{$search}\"" : 'Belum ada member terdaftar' }}
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ $search ? 'Periksa ejaan, atau cari dengan sebagian nama atau nomor WhatsApp.' : 'Member dapat didaftarkan langsung oleh kasir saat transaksi berlangsung.' }}
                </p>
                @if ($search)
                    <div class="mt-4 flex justify-center">
                        <april:button-link href="{{ route('admin.members.index') }}" variant="outline" size="none" class="h-10 px-4">
                            Hapus pencarian
                        </april:button-link>
                    </div>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <caption class="sr-only">Daftar member terdaftar</caption>
                    <thead>
                        <tr class="border-b border-border text-left text-muted-foreground">
                            <th scope="col" class="px-4 py-3 font-medium sm:px-6">Nama & Kontak</th>
                            <th scope="col" class="px-4 py-3 font-medium sm:px-6">Tier</th>
                            <th scope="col" class="px-4 py-3 font-medium tabular-nums sm:px-6">Saldo Poin</th>
                            <th scope="col" class="hidden px-4 py-3 font-medium sm:table-cell sm:px-6">Total Belanja</th>
                            <th scope="col" class="hidden px-4 py-3 font-medium md:table-cell md:px-6">Bergabung</th>
                            <th scope="col" class="px-4 py-3 text-right sm:px-6"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($members as $member)
                            @php
                                $tier = Tier::forSpendFrom($tiers, $member->lifetime_spend);
                            @endphp
                            <tr class="hover:bg-accent/40 transition-colors">
                                <td class="px-4 py-3 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-secondary text-xs font-semibold uppercase text-secondary-foreground">
                                            {{ substr($member->name, 0, 2) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-medium text-foreground">{{ $member->name }}</p>
                                            <p class="text-xs text-muted-foreground tabular-nums">{{ $member->displayPhone() }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 sm:px-6">
                                    @if ($tier)
                                        <span class="inline-flex items-center rounded bg-accent px-2 py-0.5 text-xs font-medium text-foreground">
                                            {{ $tier->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-muted-foreground">Standar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 sm:px-6">
                                    <span class="font-medium text-primary tabular-nums">{{ number_format($member->points_balance) }}</span>
                                    <span class="text-xs text-muted-foreground"> poin</span>
                                </td>
                                <td class="hidden px-4 py-3 tabular-nums sm:table-cell sm:px-6">
                                    {{ Rupiah::format($member->lifetime_spend) }}
                                </td>
                                <td class="hidden px-4 py-3 text-xs text-muted-foreground md:table-cell md:px-6">
                                    {{ $member->created_at->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3 text-right sm:px-6">
                                    <april:button-link href="{{ route('admin.members.show', $member) }}" variant="outline" size="none" class="h-9 px-3 text-xs">
                                        Lihat detail<span class="sr-only"> {{ $member->name }}</span>
                                    </april:button-link>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$members" />
        @endif
    </section>
</x-layouts.admin>
