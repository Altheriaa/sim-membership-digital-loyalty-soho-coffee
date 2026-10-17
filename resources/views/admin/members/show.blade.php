@use('App\Support\Rupiah')

<x-layouts.admin :title="'Member : '. $member->name">
    <x-slot:actions>
        <april:button-link href="{{ route('admin.members.edit', $member) }}" variant="outline" size="none"
            class="h-10 px-3.5 text-xs">
            Ubah kontak
        </april:button-link>
        <april:button-link href="{{ route('admin.members.index') }}" variant="outline" size="none"
            class="h-10 px-3.5 text-xs">
            &larr; Semua member
        </april:button-link>
    </x-slot:actions>

    <!-- Kartu Informasi & Statistik Member -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Kolom Profil -->
        <div class="rounded-lg border border-border bg-card p-5 text-card-foreground flex flex-col gap-4">
            <div class="flex items-center gap-3">
                <div
                    class="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-bold uppercase text-primary-foreground">
                    {{ substr($member->name, 0, 2) }}
                </div>
                <div class="min-w-0">
                    <h2 class="font-serif text-xl font-medium tracking-tight truncate">{{ $member->name }}</h2>
                    <p class="text-xs text-muted-foreground tabular-nums">{{ $member->displayPhone() }}</p>
                </div>
            </div>

            <div class="border-t border-border pt-3 flex flex-col gap-2.5 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-muted-foreground">Tingkat Tier:</span>
                    @if ($tier)
                        <span class="rounded bg-accent px-2 py-0.5 font-medium text-foreground">
                            {{ $tier->name }} ({{ $tier->multiplierLabel() }}x poin)
                        </span>
                    @else
                        <span class="text-muted-foreground">Standar (1x poin)</span>
                    @endif
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-muted-foreground">Saldo Poin:</span>
                    <span
                        class="font-serif text-base font-bold text-primary tabular-nums">{{ number_format($member->points_balance) }}
                        poin</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-muted-foreground">Akumulasi Belanja:</span>
                    <span
                        class="font-medium text-foreground tabular-nums">{{ Rupiah::format($member->lifetime_spend) }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-muted-foreground">Bergabung Sejak:</span>
                    <span class="text-muted-foreground">{{ $member->created_at->translatedFormat('d M Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Kolom Penyesuaian Poin Manual -->
        <div class="lg:col-span-2 rounded-lg border border-border bg-card p-5 text-card-foreground flex flex-col gap-3">
            <h3 class="font-serif text-base font-medium">Penyesuaian Poin Manual</h3>
            <p class="text-xs text-muted-foreground">
                Gunakan untuk menambah poin apresiasi atau mengoreksi kesalahan sistem. Setiap penyesuaian akan tercatat
                rapi di log mutasi.
            </p>

            <form method="POST" action="{{ route('admin.members.adjust-points', $member) }}"
                class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-12 items-end">
                @csrf
                <div class="sm:col-span-4 flex flex-col gap-1">
                    <label for="points-input" class="text-xs font-medium text-muted-foreground">Jumlah Poin (+ /
                        -)</label>
                    <input id="points-input" type="number" name="points" value="{{ old('points') }}"
                        placeholder="Contoh: 50 atau -20" required
                        class="h-10 w-full rounded-md border border-input bg-background px-3 text-xs tabular-nums text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                </div>

                <div class="sm:col-span-5 flex flex-col gap-1">
                    <label for="desc-input" class="text-xs font-medium text-muted-foreground">Alasan /
                        Keterangan</label>
                    <input id="desc-input" type="text" name="description" value="{{ old('description') }}"
                        placeholder="Contoh: Bonus event atau kompensasi" required maxlength="100"
                        class="h-10 w-full rounded-md border border-input bg-background px-3 text-xs text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring">
                </div>

                <div class="sm:col-span-3">
                    <april:button type="submit" size="none" class="h-10 w-full px-4 text-xs font-medium">
                        Simpan mutasi
                    </april:button>
                </div>
            </form>
            @error('points')
                <p class="text-xs text-destructive">{{ $message }}</p>
            @enderror
            @error('description')
                <p class="text-xs text-destructive">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Riwayat Transaksi Belanja Member -->
    <section aria-labelledby="orders-heading"
        class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        <div class="border-b border-border p-4">
            <h3 id="orders-heading" class="font-serif text-base font-medium">Riwayat Transaksi Pesanan</h3>
        </div>

        @if ($orders->isEmpty())
            <div class="p-8 text-center text-xs text-muted-foreground">
                Belum ada transaksi belanja yang tercatat untuk member ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-border text-left text-muted-foreground">
                            <th scope="col" class="px-4 py-3 font-medium">No. Transaksi</th>
                            <th scope="col" class="px-4 py-3 font-medium">Tanggal</th>
                            <th scope="col" class="px-4 py-3 font-medium">Kasir</th>
                            <th scope="col" class="px-4 py-3 font-medium">Metode</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Poin Didapat</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Total Belanja</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-accent/40">
                                <td class="px-4 py-3 font-mono font-medium text-foreground">{{ $order->number }}</td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ $order->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 text-foreground">{{ $order->cashier->name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ $order->payment_method->label() }}</td>
                                <td class="px-4 py-3 text-right font-medium text-primary tabular-nums">
                                    +{{ number_format($order->points_earned) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-foreground tabular-nums">
                                    {{ Rupiah::format($order->total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$orders" />
        @endif
    </section>

    <!-- Riwayat Mutasi Poin -->
    <section aria-labelledby="points-heading"
        class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        <div class="border-b border-border p-4">
            <h3 id="points-heading" class="font-serif text-base font-medium">Log Mutasi & Riwayat Poin</h3>
        </div>

        @if ($transactions->isEmpty())
            <div class="p-8 text-center text-xs text-muted-foreground">
                Belum ada riwayat mutasi poin untuk member ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-border text-left text-muted-foreground">
                            <th scope="col" class="px-4 py-3 font-medium">Waktu</th>
                            <th scope="col" class="px-4 py-3 font-medium">Keterangan</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Perubahan</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($transactions as $tx)
                            <tr class="hover:bg-accent/40">
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ $tx->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 text-foreground">{{ $tx->description }}</td>
                                <td
                                    class="px-4 py-3 text-right font-semibold tabular-nums {{ $tx->points >= 0 ? 'text-primary' : 'text-destructive' }}">
                                    {{ $tx->points >= 0 ? '+' : '' }}{{ number_format($tx->points) }} poin
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-muted-foreground tabular-nums">
                                    {{ number_format($tx->balance_after) }} poin
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$transactions" />
        @endif
    </section>
</x-layouts.admin>