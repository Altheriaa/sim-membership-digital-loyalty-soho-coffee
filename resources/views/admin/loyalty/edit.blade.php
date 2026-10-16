@use('App\Support\Rupiah')

@php
    $exampleSpend = 100000;
    $examplePoints = intdiv($exampleSpend, max($settings->spend_per_point, 1));
@endphp

<x-layouts.admin title="Aturan poin">
    <form method="POST" action="{{ route('admin.loyalty.update') }}" class="flex max-w-xl flex-col gap-5 rounded-lg border border-border bg-card p-4 text-card-foreground sm:p-6">
        @csrf
        @method('PUT')

        <x-field name="spend_per_point" label="Belanja untuk mendapat 1 poin (Rp)" type="number" :value="$settings->spend_per_point" min="1" step="500" inputmode="numeric" hint="Contoh: 10000 berarti setiap Rp 10.000 menghasilkan 1 poin." required />
        <x-field name="point_value" label="Nilai tukar 1 poin (Rp)" type="number" :value="$settings->point_value" min="1" step="100" inputmode="numeric" hint="Potongan harga yang didapat dari 1 poin saat dipakai membayar." required />
        <x-field name="tax_percent" label="Pajak (%)" type="number" :value="rtrim(rtrim($settings->tax_percent, '0'), '.')" min="0" max="100" step="0.01" inputmode="decimal" hint="Isi sesuai pajak cafe, misalnya 10 untuk PB1. Isi 0 jika tidak ada." required />

        <div class="flex flex-wrap items-center gap-2">
            <april:button type="submit" size="none" class="h-11 px-4 sm:h-10">Simpan</april:button>
        </div>
    </form>

    <section aria-labelledby="example-heading" class="max-w-xl rounded-lg border border-border px-4 py-4 sm:px-6">
        <h2 id="example-heading" class="font-serif text-lg font-medium">Dengan aturan yang tersimpan</h2>
        <ul class="mt-2 flex flex-col gap-1 text-sm text-muted-foreground">
            <li>Belanja {{ Rupiah::format($exampleSpend) }} menghasilkan {{ $examplePoints }} poin (sebelum pengali tier).</li>
            <li>10 poin bernilai {{ Rupiah::format(10 * $settings->point_value) }} saat dipakai membayar.</li>
        </ul>
    </section>
</x-layouts.admin>
