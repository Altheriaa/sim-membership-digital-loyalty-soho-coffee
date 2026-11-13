/**
 * Interactive state for the public landing pages: gallery slider, menu filter,
 * points calculator, and WhatsApp member status lookup.
 */
export default function pointsEstimator({
    items = [],
    spendPerPoint = 10000,
    tiers = [],
    checkPhoneUrl = '',
    slideCount = 4,
} = {}) {
    return {
        items,
        tiers,
        spendPerPoint,
        checkPhoneUrl,
        slideCount,
        activeSlide: 0,
        touchStartX: null,
        quantities: Object.fromEntries(items.map((item) => [item.id, 0])),
        tierIndex: 0,
        selectedCategory: 'all',
        search: '',
        phone: '',
        phoneState: 'idle',
        phoneError: '',

        nextSlide() {
            this.activeSlide = (this.activeSlide + 1) % this.slideCount;
        },

        prevSlide() {
            this.activeSlide = (this.activeSlide - 1 + this.slideCount) % this.slideCount;
        },

        goToSlide(index) {
            if (index >= 0 && index < this.slideCount) {
                this.activeSlide = index;
            }
        },

        handleTouchStart(event) {
            this.touchStartX = event.changedTouches?.[0]?.screenX ?? null;
        },

        handleTouchEnd(event) {
            if (this.touchStartX === null) {
                return;
            }

            const endX = event.changedTouches?.[0]?.screenX ?? this.touchStartX;
            const delta = endX - this.touchStartX;
            this.touchStartX = null;

            if (Math.abs(delta) >= 40) {
                if (delta < 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }
        },

        quantity(id) {
            return this.quantities[id] ?? 0;
        },

        add(id) {
            this.quantities[id] = Math.min(this.quantity(id) + 1, 99);
        },

        remove(id) {
            this.quantities[id] = Math.max(this.quantity(id) - 1, 0);
        },

        clear() {
            Object.keys(this.quantities).forEach((id) => {
                this.quantities[id] = 0;
            });
        },

        matchesItem(categoryId, name) {
            const categoryMatch = this.selectedCategory === 'all' || this.selectedCategory === categoryId;
            const query = this.search.trim().toLowerCase();
            const searchMatch = query === '' || String(name).toLowerCase().includes(query);

            return categoryMatch && searchMatch;
        },

        get visibleItemCount() {
            return this.items.filter((item) => this.matchesItem(item.category_id, item.name)).length;
        },

        resetFilter() {
            this.selectedCategory = 'all';
            this.search = '';
        },

        get itemCount() {
            return Object.values(this.quantities).reduce((sum, qty) => sum + qty, 0);
        },

        get subtotal() {
            return this.items.reduce((sum, item) => sum + item.price * this.quantity(item.id), 0);
        },

        get multiplierHundredths() {
            return this.tiers[this.tierIndex]?.multiplierHundredths ?? 100;
        },

        get points() {
            return Math.floor((Math.floor(this.subtotal / this.spendPerPoint) * this.multiplierHundredths) / 100);
        },

        get spendToNextPoint() {
            const remainder = this.subtotal % this.spendPerPoint;

            return this.spendPerPoint - remainder;
        },

        rupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        },

        async checkPhone() {
            const digits = this.phone.replace(/[^0-9]/g, '');

            if (digits.length < 10 || digits.length > 15) {
                this.phoneState = 'error';
                this.phoneError = 'Masukkan nomor HP Indonesia yang lengkap (10 sampai 15 digit), lalu pilih Periksa.';

                return;
            }

            this.phoneState = 'loading';
            this.phoneError = '';

            try {
                const response = await fetch(`${this.checkPhoneUrl}?phone=${encodeURIComponent(this.phone)}`, {
                    headers: { Accept: 'application/json' },
                });

                if (!response.ok) {
                    throw new Error('Request failed');
                }

                const data = await response.json();

                if (!data.valid) {
                    this.phoneState = 'error';
                    this.phoneError = 'Format nomor belum sesuai nomor HP Indonesia (contoh awalan 08 atau 628). Perbaiki nomor lalu pilih Periksa.';

                    return;
                }

                this.phoneState = data.registered ? 'registered' : 'unregistered';
            } catch {
                this.phoneState = 'error';
                this.phoneError = 'Gagal terhubung ke server untuk memeriksa nomor. Periksa koneksi internet Anda lalu coba lagi.';
            }
        },
    };
}
