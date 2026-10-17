@use('App\Role')

<x-layouts.guest title="Kasir" max-width="max-w-7xl" :centered="false">
    <header class="flex flex-wrap items-center justify-between gap-4 border-b border-border pb-4">
        <div class="flex flex-col gap-0.5">
            <div class="flex items-center gap-2">
                <span class="font-serif text-lg font-semibold tracking-tight">{{ config('app.name') }}</span>
                <span class="text-xs text-muted-foreground">·</span>
                <span class="text-xs text-muted-foreground">{{ auth()->user()->name }} ({{ auth()->user()->role->label() }})</span>
            </div>
            <h1 class="font-serif text-2xl font-medium tracking-tight sm:text-3xl">Layar Kasir</h1>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                x-cloak
                x-show="$store.pwa?.canPrompt"
                x-on:click="$store.pwa.install()"
                class="flex h-9 items-center gap-1.5 rounded-md border border-primary/30 bg-primary/10 px-3 text-xs font-semibold text-primary hover:bg-primary/20 transition-colors"
                title="Pasang aplikasi POS"
            >
                <x-lucide-download class="size-3.5" />
                <span class="hidden sm:inline">Install POS</span>
            </button>
            @if (auth()->user()->hasRole(Role::Admin))
                <april:button-link href="{{ route('admin.dashboard') }}" variant="outline" size="none" class="h-9 px-3 text-xs">
                    Ke back office
                </april:button-link>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <april:button type="submit" variant="outline" size="none" class="h-9 px-3 text-xs">
                    Keluar
                </april:button>
            </form>
            <x-theme-toggle />
        </div>
    </header>

    <script id="pos-config" type="application/json">
        {!! json_encode([
            'items' => $items,
            'categories' => $categories,
            'settings' => [
                'taxBasisPoints' => $settings->taxBasisPoints(),
                'spendPerPoint' => (int) $settings->spend_per_point,
            ],
            'routes' => [
                'lookup' => route('pos.members.lookup'),
                'registerMember' => route('pos.members.store'),
                'voucherLookup' => route('pos.vouchers.lookup'),
                'storeOrder' => route('pos.orders.store'),
            ],
            'csrfToken' => csrf_token(),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

    <div x-data="posRegister('pos-config')" class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
        <!-- Kolom Kiri: Katalog Menu & Pencarian -->
        <section aria-labelledby="catalog-heading" class="lg:col-span-7 xl:col-span-8 flex flex-col gap-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 id="catalog-heading" class="sr-only">Katalog Menu</h2>
                
                <!-- Input Pencarian Menu -->
                <div class="relative w-full sm:max-w-xs">
                    <input
                        type="search"
                        x-model="search"
                        placeholder="Cari menu..."
                        class="h-10 w-full rounded-md border border-input bg-card px-3 py-2 text-sm text-card-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        aria-label="Cari menu"
                    >
                </div>

                <!-- Filter Kategori Tabs -->
                <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto pb-1" role="tablist" aria-label="Kategori menu">
                    <button
                        type="button"
                        role="tab"
                        :aria-selected="categoryId === null"
                        @click="categoryId = null"
                        class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors"
                        :class="categoryId === null ? 'bg-primary text-primary-foreground shadow-xs' : 'border border-border bg-card text-muted-foreground hover:bg-accent hover:text-foreground'"
                    >
                        Semua
                    </button>
                    <template x-for="category in categories" :key="category.id">
                        <button
                            type="button"
                            role="tab"
                            :aria-selected="categoryId === category.id"
                            @click="categoryId = category.id"
                            class="rounded-md px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="categoryId === category.id ? 'bg-primary text-primary-foreground shadow-xs' : 'border border-border bg-card text-muted-foreground hover:bg-accent hover:text-foreground'"
                            x-text="category.name"
                        ></button>
                    </template>
                </div>
            </div>

            <!-- Grid Menu Items -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                <template x-for="item in filteredItems" :key="item.id">
                    <button
                        type="button"
                        @click="add(item)"
                        :disabled="!item.available"
                        class="group relative flex flex-col justify-between rounded-lg border border-border bg-card p-3 text-left text-card-foreground transition-all hover:border-primary/50 hover:shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                        :aria-label="item.name + ' harga ' + rupiah(item.price) + (quantityOf(item.id) > 0 ? ', ada ' + quantityOf(item.id) + ' di keranjang' : '')"
                    >
                        <!-- Badge Jumlah di Keranjang -->
                        <span
                            x-show="quantityOf(item.id) > 0"
                            x-text="quantityOf(item.id)"
                            class="absolute top-2 right-2 flex size-6 items-center justify-center rounded-full bg-primary text-xs font-bold text-primary-foreground"
                            aria-hidden="true"
                        ></span>

                        <div class="flex items-start gap-2.5 pr-6">
                            <template x-if="item.imageUrl">
                                <img :src="item.imageUrl" :alt="item.name" class="size-10 shrink-0 rounded-md border border-border object-cover" loading="lazy">
                            </template>
                            <span class="font-serif font-medium leading-snug tracking-tight text-foreground group-hover:text-primary transition-colors" x-text="item.name"></span>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-border/60 pt-2">
                            <span class="text-sm font-semibold tabular-nums text-foreground" x-text="rupiah(item.price)"></span>
                            <span
                                x-show="!item.available"
                                class="rounded bg-destructive/15 px-1.5 py-0.5 text-[11px] font-medium text-destructive"
                            >
                                Habis
                            </span>
                            <span
                                x-show="item.available"
                                class="text-xs text-muted-foreground group-hover:text-primary font-medium"
                            >
                                + Tambah
                            </span>
                        </div>
                    </button>
                </template>
            </div>

            <!-- Empty State Katalog -->
            <div
                x-show="filteredItems.length === 0"
                class="flex flex-col items-center justify-center rounded-lg border border-dashed border-border p-8 text-center"
            >
                <p class="font-serif text-base font-medium">Menu tidak ditemukan</p>
                <p class="mt-1 text-xs text-muted-foreground">Tidak ada menu yang sesuai dengan kata kunci pencarian atau kategori ini.</p>
                <button
                    type="button"
                    x-show="search || categoryId !== null"
                    @click="search = ''; categoryId = null"
                    class="mt-3 text-xs text-primary underline underline-offset-4"
                >
                    Tampilkan semua menu
                </button>
            </div>
        </section>

        <!-- Kolom Kanan: Keranjang, Member, Voucer & Pembayaran -->
        <section
            aria-labelledby="order-heading"
            class="lg:col-span-5 xl:col-span-4 flex flex-col gap-4 rounded-lg border border-border bg-card p-4 sm:p-5 text-card-foreground shadow-xs"
        >
            <div class="flex items-center justify-between border-b border-border pb-3">
                <div class="flex items-center gap-2">
                    <h2 id="order-heading" class="font-serif text-lg font-medium">Pesanan</h2>
                    <span
                        x-show="itemCount > 0"
                        x-text="itemCount + ' item'"
                        class="rounded-full bg-secondary px-2 py-0.5 text-xs text-secondary-foreground"
                    ></span>
                </div>
                <button
                    type="button"
                    x-show="cartLines.length > 0"
                    @click="clearCart"
                    class="text-xs text-muted-foreground hover:text-destructive transition-colors"
                >
                    Kosongkan
                </button>
            </div>

            <!-- Daftar Keranjang -->
            <div class="flex flex-col gap-2">
                <div x-show="cartLines.length === 0" class="py-8 text-center text-xs text-muted-foreground">
                    Keranjang masih kosong. Klik menu di samping untuk menambahkan pesanan.
                </div>

                <ul x-show="cartLines.length > 0" class="max-h-56 divide-y divide-border overflow-y-auto pr-1">
                    <template x-for="line in cartLines" :key="line.id">
                        <li class="flex items-center justify-between gap-2 py-2.5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium" x-text="line.item.name"></p>
                                <p class="text-xs text-muted-foreground tabular-nums" x-text="rupiah(line.price)"></p>
                            </div>

                            <div class="flex items-center gap-2">
                                <div class="flex items-center rounded border border-border bg-background">
                                    <button
                                        type="button"
                                        @click="change(line.id, -1)"
                                        class="flex size-7 items-center justify-center text-sm font-bold text-muted-foreground hover:bg-accent hover:text-foreground"
                                        aria-label="Kurangi 1"
                                    >
                                        -
                                    </button>
                                    <span class="w-7 text-center text-xs font-semibold tabular-nums" x-text="line.quantity"></span>
                                    <button
                                        type="button"
                                        @click="change(line.id, 1)"
                                        :disabled="!canIncrease(line.id)"
                                        class="flex size-7 items-center justify-center text-sm font-bold text-muted-foreground hover:bg-accent hover:text-foreground disabled:opacity-30"
                                        aria-label="Tambah 1"
                                    >
                                        +
                                    </button>
                                </div>
                                <span class="w-20 text-right text-xs font-medium tabular-nums" x-text="rupiah(line.lineTotal)"></span>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Panel Member Loyalty -->
            <div class="rounded-md border border-border bg-background/50 p-3 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Member & Poin</span>
                    <button
                        type="button"
                        x-show="member"
                        @click="forgetMember"
                        class="text-[11px] text-muted-foreground hover:text-destructive underline underline-offset-2"
                    >
                        Ganti member
                    </button>
                </div>

                <!-- Form Cari Member WhatsApp -->
                <div x-show="memberStatus === 'idle' || memberStatus === 'error'" class="flex flex-col gap-1.5">
                    <div class="flex gap-1.5">
                        <input
                            type="tel"
                            x-ref="phoneInput"
                            x-model="phone"
                            @input="phoneEdited"
                            @keydown.enter.prevent="lookup"
                            placeholder="No. WhatsApp (08...)"
                            class="h-9 flex-1 rounded border border-input bg-card px-2.5 text-xs text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                        <button
                            type="button"
                            @click="lookup"
                            :disabled="memberBusy || !phone.trim()"
                            class="h-9 rounded bg-secondary px-3 text-xs font-medium text-secondary-foreground hover:bg-accent hover:text-foreground disabled:opacity-50"
                        >
                            <span x-text="memberBusy ? 'Mencari...' : 'Cari'"></span>
                        </button>
                    </div>
                    <p x-show="memberError" class="text-xs text-destructive" x-text="memberError"></p>
                </div>

                <!-- Info Member Terpilih -->
                <div x-show="memberStatus === 'found' && member" class="flex flex-col gap-1">
                    <div class="flex items-baseline justify-between">
                        <span class="text-sm font-medium text-foreground" x-text="member?.name"></span>
                        <span class="text-xs tabular-nums text-muted-foreground" x-text="member?.phone"></span>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-1 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span x-show="member?.tier" class="rounded bg-accent px-1.5 py-0.5 text-[11px] font-medium" x-text="member?.tier"></span>
                            <span class="text-muted-foreground tabular-nums" x-text="(member?.points_balance ?? 0) + ' poin'"></span>
                        </div>
                        <span x-show="pointsEstimate > 0" class="font-medium text-primary tabular-nums">
                            +<span x-text="pointsEstimate"></span> poin belanja ini
                        </span>
                    </div>
                </div>

                <!-- Register Cepat Jika Nomor Belum Terdaftar -->
                <div x-show="memberStatus === 'not_found'" class="flex flex-col gap-2 pt-1">
                    <p class="text-xs text-muted-foreground">
                        Nomor <span class="font-medium text-foreground" x-text="registerPhone"></span> belum terdaftar. Daftarkan member baru:
                    </p>
                    <div class="flex gap-1.5">
                        <input
                            type="text"
                            x-ref="registerName"
                            x-model="registerName"
                            @keydown.enter.prevent="register"
                            placeholder="Nama panggilan member"
                            class="h-9 flex-1 rounded border border-input bg-card px-2.5 text-xs text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                        <button
                            type="button"
                            @click="register"
                            :disabled="memberBusy || !registerName.trim()"
                            class="h-9 rounded bg-primary px-3 text-xs font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
                        >
                            <span x-text="memberBusy ? 'Mendaftar...' : 'Daftar'"></span>
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <p x-show="registerError" class="text-xs text-destructive" x-text="registerError"></p>
                        <button
                            type="button"
                            @click="forgetMember"
                            class="text-[11px] text-muted-foreground hover:underline ml-auto"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Panel Voucer Member -->
            <div class="rounded-md border border-border bg-background/50 p-3 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Voucer</span>
                    <button
                        type="button"
                        x-show="voucher"
                        @click="clearVoucher"
                        class="text-[11px] text-muted-foreground hover:text-destructive underline underline-offset-2"
                    >
                        Lepas voucer
                    </button>
                </div>

                <!-- Belum ada member: voucer menunggu -->
                <p x-show="!member" class="text-xs text-muted-foreground">
                    Voucer dipakai dengan kode klaim member. Pilih member di panel atas untuk mengaktifkan kotak ini.
                </p>

                <!-- Cari kode voucer -->
                <div x-show="member && !voucherInfo && !voucher" class="flex flex-col gap-1.5">
                    <div class="flex gap-1.5">
                        <input
                            type="text"
                            x-model="voucherCode"
                            @input="voucherEdited"
                            @keydown.enter.prevent="checkVoucher"
                            placeholder="Kode voucer (KOP-XXXXXX)"
                            aria-label="Kode voucer member"
                            class="h-9 flex-1 rounded border border-input bg-card px-2.5 text-xs uppercase tracking-wider text-foreground placeholder:tracking-normal placeholder:normal-case placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                        >
                        <button
                            type="button"
                            @click="checkVoucher"
                            :disabled="voucherBusy || !voucherCode.trim()"
                            class="h-9 rounded bg-secondary px-3 text-xs font-medium text-secondary-foreground hover:bg-accent hover:text-foreground disabled:opacity-50"
                        >
                            <span x-text="voucherBusy ? 'Memeriksa...' : 'Cek'"></span>
                        </button>
                    </div>
                    <p x-show="voucherError" class="text-xs text-destructive" x-text="voucherError"></p>
                </div>

                <!-- Detail voucer ditemukan + verifikasi PIN keamanan member -->
                <div x-show="member && voucherInfo && !voucher" class="flex flex-col gap-2">
                    <div class="rounded border border-primary/30 bg-primary/5 p-2 flex flex-col gap-1">
                        <div class="flex items-baseline justify-between gap-2">
                            <span class="text-sm font-medium text-foreground" x-text="voucherInfo?.name"></span>
                            <span class="shrink-0 text-[11px] tabular-nums text-muted-foreground" x-text="'Kode ' + voucherInfo?.code"></span>
                        </div>
                        <p class="text-xs font-semibold text-primary" x-text="voucherInfo?.benefit"></p>
                        <p class="text-[11px] min-w-0 text-muted-foreground">
                            Atas nama <span class="font-medium text-foreground" x-text="voucherInfo?.owner_name"></span> · diklaim
                            <span class="tabular-nums" x-text="voucherInfo?.points_spent"></span> poin · berlaku sampai
                            <span class="tabular-nums" x-text="voucherInfo?.expires_label"></span>
                        </p>
                    </div>

                    <label class="text-[11px] font-medium text-muted-foreground" for="voucher-pin-input">
                        PIN Keamanan dari <span x-text="voucherInfo?.owner_name"></span> (member memasukkan PIN sendiri)
                    </label>
                    <input
                        id="voucher-pin-input"
                        type="password"
                        inputmode="numeric"
                        maxlength="6"
                        x-model="voucherPin"
                        @keydown.enter.prevent="useVoucher"
                        placeholder="6 digit"
                        aria-label="PIN Keamanan member"
                        class="h-9 w-full rounded border border-input bg-card px-2.5 text-sm tracking-[0.3em] tabular-nums text-foreground placeholder:tracking-normal placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >

                    <div class="flex gap-1.5">
                        <button
                            type="button"
                            @click="useVoucher"
                            class="h-9 flex-1 rounded bg-primary px-3 text-xs font-medium text-primary-foreground hover:bg-primary/90"
                        >
                            Gunakan Voucer
                        </button>
                        <button type="button" @click="voucherInfo = null; voucherPin = ''" class="h-9 rounded px-3 text-xs text-muted-foreground hover:text-foreground underline underline-offset-2">
                            Batal
                        </button>
                    </div>
                    <p x-show="voucherError" class="text-xs text-destructive" x-text="voucherError"></p>
                </div>

                <!-- Voucer terpasang di transaksi -->
                <div x-show="voucher" class="rounded border border-primary/30 bg-primary/5 p-2 flex flex-col gap-1">
                    <div class="flex items-baseline justify-between gap-2">
                        <span class="text-sm font-medium text-foreground" x-text="voucher?.name"></span>
                        <span class="shrink-0 text-[11px] tabular-nums text-muted-foreground" x-text="'Kode ' + voucher?.code"></span>
                    </div>
                    <p class="text-xs font-semibold text-primary" x-text="voucher?.benefit"></p>
                    <p class="text-[11px] text-muted-foreground">Siap dipakai saat pembayaran. PIN Keamanan sudah diverifikasi kasir.</p>
                </div>
            </div>

            <!-- Ringkasan Biaya -->
            <div class="border-t border-border pt-3 flex flex-col gap-1.5 text-xs">
                <div class="flex justify-between text-muted-foreground">
                    <span>Subtotal</span>
                    <span class="tabular-nums" x-text="rupiah(subtotal)"></span>
                </div>
                <div class="flex justify-between text-muted-foreground">
                    <span>Pajak</span>
                    <span class="tabular-nums" x-text="rupiah(tax)"></span>
                </div>
                <div x-show="voucherAmount > 0" class="flex justify-between text-muted-foreground">
                    <span>Potongan voucer</span>
                    <span class="tabular-nums text-primary" x-text="'- ' + rupiah(voucherAmount)"></span>
                </div>
                <div class="flex justify-between pt-1 border-t border-border/60 text-sm font-semibold text-foreground">
                    <span>Total Tagihan</span>
                    <span class="font-serif text-lg tabular-nums text-primary" x-text="rupiah(total)"></span>
                </div>
            </div>

            <!-- Pilihan Metode Pembayaran -->
            <div class="flex flex-col gap-2 border-t border-border pt-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Metode Pembayaran</span>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        @click="paymentMethod = 'cash'"
                        class="h-10 rounded border text-xs font-medium transition-colors"
                        :class="paymentMethod === 'cash' ? 'border-primary bg-primary/10 text-primary font-semibold' : 'border-border bg-background hover:bg-accent text-foreground'"
                    >
                        Tunai
                    </button>
                    <button
                        type="button"
                        @click="paymentMethod = 'non_cash'"
                        class="h-10 rounded border text-xs font-medium transition-colors"
                        :class="paymentMethod === 'non_cash' ? 'border-primary bg-primary/10 text-primary font-semibold' : 'border-border bg-background hover:bg-accent text-foreground'"
                    >
                        Non-tunai / QRIS
                    </button>
                </div>

                <!-- Bagian Uang Tunai -->
                <div x-show="paymentMethod === 'cash'" class="flex flex-col gap-2 pt-1">
                    <label for="cash-received-input" class="sr-only">Uang diterima</label>
                    <input
                        id="cash-received-input"
                        type="number"
                        x-model="cashReceived"
                        placeholder="Uang diterima (Rp)"
                        min="0"
                        step="1000"
                        class="h-10 rounded border border-input bg-background px-3 text-sm tabular-nums text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                    >

                    <!-- Pilihan Cepat Nominal Uang Pas / Pecahan -->
                    <div x-show="total > 0" class="flex flex-wrap gap-1.5">
                        <template x-for="suggestion in suggestions" :key="suggestion">
                            <button
                                type="button"
                                @click="cashReceived = String(suggestion)"
                                class="rounded border border-border bg-secondary/60 px-2 py-1 text-[11px] font-medium text-foreground hover:bg-secondary"
                                x-text="rupiah(suggestion)"
                            ></button>
                        </template>
                    </div>

                    <!-- Informasi Kembalian -->
                    <div x-show="cashValue !== null" class="rounded bg-background p-2 text-xs flex justify-between items-center border border-border/80">
                        <span class="text-muted-foreground">Kembalian:</span>
                        <span
                            class="font-semibold tabular-nums text-sm"
                            :class="changeDue >= 0 ? 'text-foreground' : 'text-destructive'"
                            x-text="changeDue >= 0 ? rupiah(changeDue) : 'Kurang ' + rupiah(Math.abs(changeDue))"
                        ></span>
                    </div>
                </div>
            </div>

            <!-- Pesan Error Pembayaran -->
            <div x-show="payErrors.length > 0" class="flex flex-col gap-1">
                <template x-for="err in payErrors" :key="err">
                    <p class="text-xs text-destructive" x-text="err"></p>
                </template>
            </div>

            <!-- Tombol Bayar / Selesaikan Pesanan -->
            <button
                type="button"
                @click="pay"
                :disabled="!canPay || paying"
                class="mt-1 flex h-12 w-full items-center justify-center rounded-md bg-primary font-medium text-primary-foreground shadow-sm hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
            >
                <span x-show="paying">Memproses transaksi...</span>
                <span x-show="!paying" x-text="paymentMethod === 'cash' ? 'Bayar Tunai' : 'Selesaikan Pembayaran'"></span>
            </button>
        </section>
    </div>
</x-layouts.guest>
