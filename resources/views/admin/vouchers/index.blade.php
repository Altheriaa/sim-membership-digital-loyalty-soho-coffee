@use('App\Support\Rupiah')
@use('App\VoucherType')

<x-layouts.admin title="Voucer">
    <x-slot:actions>
        <april:button-link href="{{ route('admin.vouchers.create') }}" size="none" class="h-11 px-4 sm:h-10">Tambah voucer</april:button-link>
    </x-slot:actions>

    <section aria-label="Daftar voucer" class="overflow-hidden rounded-lg border border-border bg-card text-card-foreground">
        @if ($vouchers->isEmpty())
            <div class="px-4 py-12 text-center sm:px-6">
                <p class="font-serif text-lg">Belum ada voucer</p>
                <p class="mx-auto mt-1 max-w-md text-sm text-muted-foreground">Voucer adalah hadiah yang bisa ditukar member dengan poin, misalnya gratis satu kopi atau potongan harga.</p>
                <april:button-link href="{{ route('admin.vouchers.create') }}" size="none" class="mt-4 h-11 px-4 sm:h-10">Tambah voucer pertama</april:button-link>
            </div>
        @else
            <table class="w-full text-sm">
                <caption class="sr-only">Daftar voucer, yang terbaru di atas</caption>
                <thead>
                    <tr class="border-b border-border text-left text-muted-foreground">
                        <th scope="col" class="w-1/2 px-4 py-3 font-medium sm:px-6">Voucer</th>
                        <th scope="col" class="px-4 py-3 font-medium sm:px-6">Poin</th>
                        <th scope="col" class="hidden px-4 py-3 font-medium md:table-cell sm:px-6">Kuota</th>
                        <th scope="col" class="px-4 py-3 font-medium sm:px-6">Status</th>
                        <th scope="col" class="px-4 py-3 sm:px-6"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($vouchers as $voucher)
                        <tr>
                            <td class="max-w-0 px-4 py-3 sm:px-6">
                                <p class="truncate font-medium">{{ $voucher->name }}</p>
                                <p class="truncate text-xs text-muted-foreground">
                                    @if ($voucher->type === VoucherType::FreeItem)
                                        Gratis: {{ $voucher->menuItem?->name }}
                                    @else
                                        Diskon {{ Rupiah::format($voucher->discount_amount) }}
                                    @endif
                                    · berlaku {{ $voucher->valid_days }} hari setelah diklaim
                                </p>
                            </td>
                            <td class="px-4 py-3 tabular-nums sm:px-6">{{ $voucher->points_cost }}</td>
                            <td class="hidden px-4 py-3 tabular-nums md:table-cell sm:px-6">{{ $voucher->quota ?? 'Tanpa batas' }}</td>
                            <td class="px-4 py-3 sm:px-6">{{ $voucher->statusLabel() }}</td>
                            <td class="px-4 py-3 text-right sm:px-6">
                                <april:button-link href="{{ route('admin.vouchers.edit', $voucher) }}" variant="outline" size="none" class="h-11 px-3 sm:h-9">
                                    Ubah<span class="sr-only"> {{ $voucher->name }}</span>
                                </april:button-link>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <x-pagination :paginator="$vouchers" />
        @endif
    </section>
</x-layouts.admin>
