import {
    cashSuggestions,
    formatRupiah,
    isValidPhone,
    maxRedeemablePoints,
    normalizePhone,
    pointDiscount,
    pointsFor,
    subtotalOf,
    taxFor,
    voucherDiscount,
} from './pos-math';

const MAX_QUANTITY = 99;

const MESSAGES = {
    network: 'Tidak bisa menghubungi server. Periksa koneksi internet perangkat kasir, lalu coba lagi.',
    session: 'Sesi login sudah berakhir. Muat ulang halaman lalu masuk lagi.',
    forbidden: 'Akun ini tidak punya akses ke layar kasir. Minta admin memeriksa peran akunmu.',
    server: 'Server gagal memproses permintaan. Coba lagi, dan hubungi admin bila terus terjadi.',
};

class RequestError extends Error {
    constructor(message, errors = {}) {
        super(message);
        this.errors = errors;
    }
}

async function send(url, { method = 'GET', body, csrfToken }) {
    let response;

    try {
        response = await fetch(url, {
            method,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body === undefined ? undefined : JSON.stringify(body),
        });
    } catch {
        throw new RequestError(MESSAGES.network);
    }

    const payload = await response.json().catch(() => ({}));

    if (response.ok) {
        return payload;
    }

    if (response.status === 401 || response.status === 419) {
        throw new RequestError(MESSAGES.session);
    }

    if (response.status === 403) {
        throw new RequestError(MESSAGES.forbidden);
    }

    if (response.status === 422) {
        const errors = payload.errors ?? {};
        const first = Object.values(errors)[0]?.[0];

        throw new RequestError(first ?? payload.message ?? MESSAGES.server, errors);
    }

    throw new RequestError(MESSAGES.server);
}

function readConfig(id) {
    try {
        return JSON.parse(document.getElementById(id)?.textContent ?? 'null');
    } catch {
        return null;
    }
}

export default function posRegister(configId) {
    const config = readConfig(configId);

    return {
        configLoaded: config !== null,
        items: config?.items ?? [],
        categories: config?.categories ?? [],
        settings: config?.settings ?? { taxBasisPoints: 0, spendPerPoint: 1, pointValue: 0 },
        routes: config?.routes ?? {},
        csrfToken: config?.csrfToken ?? '',

        search: '',
        categoryId: null,
        cart: [],

        phone: '',
        member: null,
        memberStatus: 'idle',
        memberError: '',
        registerName: '',
        registerPhone: '',
        registerError: '',
        memberBusy: false,

        paymentMethod: 'cash',
        cashReceived: '',
        paying: false,
        payErrors: [],

        voucherCode: '',
        voucherInfo: null,
        voucherPin: '',
        voucher: null,
        voucherBusy: false,
        voucherError: '',
        pointsToRedeem: 0,
        pointPin: '',
        pointError: '',

        rupiah(amount) {
            return formatRupiah(amount);
        },

        itemById(id) {
            return this.items.find((item) => item.id === id);
        },

        get filteredItems() {
            const term = this.search.trim().toLowerCase();

            return this.items.filter((item) => (this.categoryId === null || item.categoryId === this.categoryId)
                && (term === '' || item.name.toLowerCase().includes(term)));
        },

        get cartLines() {
            return this.cart
                .map((line) => ({ ...line, item: this.itemById(line.id) }))
                .filter((line) => line.item)
                .map((line) => ({ ...line, price: line.item.price, lineTotal: line.item.price * line.quantity }));
        },

        get itemCount() {
            return this.cart.reduce((sum, line) => sum + line.quantity, 0);
        },

        get subtotal() {
            return subtotalOf(this.cartLines);
        },

        get voucherMissingItemMessage() {
            if (!this.voucher || this.voucher.type !== 'free_item') {
                return '';
            }

            const hasItem = this.cartLines.some((line) => Number(line.id) === Number(this.voucher.menu_item_id));
            if (hasItem) {
                return '';
            }

            return `Tambahkan menu ${this.voucher.menu_item_name ?? 'hadiah'} ke keranjang untuk memakai voucer ini.`;
        },

        get voucherAmount() {
            return voucherDiscount(this.voucher, this.subtotal, this.cartLines);
        },

        get remainingAfterVoucher() {
            return Math.max(0, this.subtotal - this.voucherAmount);
        },

        get maxRedeemablePoints() {
            if (!this.member || !this.member.has_pin) {
                return 0;
            }

            return maxRedeemablePoints(
                this.member.points_balance,
                this.settings.pointValue ?? 0,
                this.remainingAfterVoucher,
            );
        },

        get effectivePointsToRedeem() {
            const requested = Math.max(0, Number.parseInt(this.pointsToRedeem, 10) || 0);

            return Math.min(requested, this.maxRedeemablePoints);
        },

        get pointAmount() {
            return pointDiscount(
                this.effectivePointsToRedeem,
                this.settings.pointValue ?? 0,
                this.remainingAfterVoucher,
            );
        },

        get totalDiscount() {
            return this.voucherAmount + this.pointAmount;
        },

        get netSubtotal() {
            return Math.max(0, this.subtotal - this.totalDiscount);
        },

        get tax() {
            return taxFor(this.netSubtotal, this.settings.taxBasisPoints);
        },

        get total() {
            return this.netSubtotal + this.tax;
        },

        get pointsEstimate() {
            if (!this.member) {
                return 0;
            }

            return pointsFor(this.netSubtotal, this.settings.spendPerPoint, this.member.multiplier_hundredths);
        },

        quantityOf(id) {
            return this.cart.find((line) => line.id === id)?.quantity ?? 0;
        },

        add(item) {
            if (!item.available) {
                return;
            }

            const line = this.cart.find((entry) => entry.id === item.id);

            if (line) {
                line.quantity = Math.min(line.quantity + 1, MAX_QUANTITY);
            } else {
                this.cart.push({ id: item.id, quantity: 1 });
            }

            this.payErrors = [];
        },

        change(id, delta) {
            const line = this.cart.find((entry) => entry.id === id);

            if (!line) {
                return;
            }

            line.quantity = Math.min(line.quantity + delta, MAX_QUANTITY);

            if (line.quantity <= 0) {
                this.cart = this.cart.filter((entry) => entry.id !== id);
            }

            this.payErrors = [];
        },

        canIncrease(id) {
            return this.quantityOf(id) < MAX_QUANTITY;
        },

        clearCart() {
            if (this.cart.length === 0 || !window.confirm('Kosongkan semua pesanan di keranjang?')) {
                return;
            }

            this.cart = [];
            this.cashReceived = '';
            this.payErrors = [];
        },

        phoneEdited() {
            if (this.memberStatus === 'not_found' || this.memberStatus === 'error') {
                this.memberStatus = 'idle';
                this.memberError = '';
                this.registerError = '';
            }
        },

        async lookup() {
            if (this.memberBusy) {
                return;
            }

            const normalized = normalizePhone(this.phone);

            if (!isValidPhone(normalized)) {
                this.memberStatus = 'error';
                this.memberError = 'Nomor WhatsApp tidak dikenali. Ketik nomor HP Indonesia, misalnya 0812 3456 7890.';

                return;
            }

            this.memberBusy = true;
            this.memberError = '';

            try {
                const url = `${this.routes.lookup}?phone=${encodeURIComponent(normalized)}`;
                const { member } = await send(url, { csrfToken: this.csrfToken });

                if (member) {
                    this.member = member;
                    this.clearVoucher();
                    this.memberStatus = 'found';
                } else {
                    this.registerPhone = normalized;
                    this.memberStatus = 'not_found';
                    this.$nextTick(() => this.$refs.registerName?.focus());
                }
            } catch (error) {
                this.memberStatus = 'error';
                this.memberError = error.message;
            } finally {
                this.memberBusy = false;
            }
        },

        async register() {
            if (this.memberBusy) {
                return;
            }

            if (this.registerName.trim() === '') {
                this.registerError = 'Nama panggilan wajib diisi.';

                return;
            }

            this.memberBusy = true;
            this.registerError = '';

            try {
                const { member } = await send(this.routes.registerMember, {
                    method: 'POST',
                    body: { name: this.registerName.trim(), phone: this.registerPhone },
                    csrfToken: this.csrfToken,
                });

                this.member = member;
                this.clearVoucher();
                this.memberStatus = 'found';
                this.registerName = '';
            } catch (error) {
                this.registerError = error.message;
            } finally {
                this.memberBusy = false;
            }
        },

        forgetMember() {
            this.member = null;
            this.memberStatus = 'idle';
            this.memberError = '';
            this.clearVoucher();
            this.clearPoints();
            this.registerError = '';
            this.registerName = '';
            this.phone = '';
            this.clearVoucher();
            this.$nextTick(() => this.$refs.phoneInput?.focus());
        },

        async checkVoucher() {
            if (this.voucherBusy || !this.member) {
                return;
            }

            const code = this.voucherCode.trim().toUpperCase();

            if (code === '') {
                this.voucherError = 'Kode voucer wajib diisi.';

                return;
            }

            this.voucherBusy = true;
            this.voucherError = '';

            try {
                const url = `${this.routes.voucherLookup}?code=${encodeURIComponent(code)}&member_id=${encodeURIComponent(this.member.id)}`;
                const { voucher } = await send(url, { csrfToken: this.csrfToken });

                this.voucherInfo = voucher;
                this.voucherPin = '';
            } catch (error) {
                this.voucherError = error.message;
            } finally {
                this.voucherBusy = false;
            }
        },

        useVoucher() {
            const pin = String(this.voucherPin).replace(/\D+/g, '');

            if (pin.length !== 6) {
                this.voucherError = 'PIN Keamanan member harus tepat 6 digit angka.';

                return;
            }

            if (this.voucherInfo?.type === 'free_item') {
                const hasItem = this.cartLines.some((line) => Number(line.id) === Number(this.voucherInfo.menu_item_id));
                if (!hasItem) {
                    this.voucherError = `Tambahkan menu ${this.voucherInfo.menu_item_name ?? 'hadiah'} ke keranjang sebelum memakai voucer ini.`;

                    return;
                }
            }

            this.voucher = { ...this.voucherInfo, pin };
            this.voucherInfo = null;
            this.voucherPin = '';
            this.voucherError = '';
            this.payErrors = [];
        },

        clearVoucher() {
            this.voucherCode = '';
            this.voucherInfo = null;
            this.voucherPin = '';
            this.voucher = null;
            this.voucherError = '';
        },

        voucherEdited() {
            if (this.voucherError) {
                this.voucherError = '';
            }
        },

        clearPoints() {
            this.pointsToRedeem = 0;
            this.pointPin = '';
            this.pointError = '';
        },

        redeemAllPoints() {
            this.pointsToRedeem = this.maxRedeemablePoints;
            this.pointError = '';
        },

        effectiveMemberPin() {
            if (this.voucher?.pin) {
                return this.voucher.pin;
            }

            const cleaned = String(this.pointPin ?? '').replace(/\D+/g, '');
            return cleaned.length === 6 ? cleaned : null;
        },

        get cashValue() {
            const digits = String(this.cashReceived).replace(/\D+/g, '');

            return digits === '' ? null : Number(digits);
        },

        get changeDue() {
            return this.cashValue === null ? null : this.cashValue - this.total;
        },

        get suggestions() {
            return cashSuggestions(this.total);
        },

        get canPay() {
            if (this.cart.length === 0 || this.paying || this.voucherMissingItemMessage) {
                return false;
            }

            return this.paymentMethod !== 'cash' || (this.cashValue !== null && this.cashValue >= this.total);
        },

        async pay() {
            if (!this.canPay) {
                return;
            }

            if (this.voucherMissingItemMessage) {
                this.payErrors = [this.voucherMissingItemMessage];

                return;
            }

            if (this.effectivePointsToRedeem > 0 && !this.effectiveMemberPin()) {
                this.pointError = 'Masukkan 6-digit PIN Keamanan member untuk menukarkan poin.';
                this.payErrors = [this.pointError];

                return;
            }

            this.paying = true;
            this.payErrors = [];
            this.pointError = '';

            try {
                const { receipt_url: receiptUrl } = await send(this.routes.storeOrder, {
                    method: 'POST',
                    body: {
                        items: this.cart.map(({ id, quantity }) => ({ id, quantity })),
                        member_id: this.member?.id ?? null,
                        voucher_id: this.voucher?.id ?? null,
                        points_to_redeem: this.effectivePointsToRedeem,
                        voucher_pin: this.effectiveMemberPin(),
                        payment_method: this.paymentMethod,
                        cash_received: this.paymentMethod === 'cash' ? this.cashValue : null,
                    },
                    csrfToken: this.csrfToken,
                });

                window.location.assign(receiptUrl);
            } catch (error) {
                const messages = Object.values(error.errors ?? {}).flat();

                if (this.voucher && ('voucher_id' in (error.errors ?? {}) || 'voucher_pin' in (error.errors ?? {}))) {
                    this.clearVoucher();
                }

                if ('points_to_redeem' in (error.errors ?? {}) || (!this.voucher && 'voucher_pin' in (error.errors ?? {}))) {
                    this.pointError = error.errors?.points_to_redeem?.[0] ?? error.errors?.voucher_pin?.[0] ?? '';
                }

                this.payErrors = messages.length > 0 ? [...new Set(messages)] : [error.message];
                this.paying = false;
            }
        },
    };
}
