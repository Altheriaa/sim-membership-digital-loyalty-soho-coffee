// Pure helpers for the register. Each one mirrors a server rule so the screen never promises a different number
// than the receipt: LoyaltySetting::taxFor, LoyaltySetting::pointsFor and PhoneNumber::normalize.

export const PHONE_PATTERN = /^628\d{8,12}$/;

export function normalizePhone(input) {
    const digits = String(input ?? '').replace(/\D+/g, '');

    if (digits.startsWith('62')) {
        return digits;
    }

    if (digits.startsWith('0')) {
        return `62${digits.slice(1)}`;
    }

    if (digits.startsWith('8')) {
        return `62${digits}`;
    }

    return digits;
}

export function isValidPhone(normalized) {
    return PHONE_PATTERN.test(normalized);
}

export function taxFor(subtotal, taxBasisPoints) {
    return Math.floor((subtotal * taxBasisPoints + 5000) / 10000);
}

export function pointsFor(spend, spendPerPoint, multiplierHundredths = 100) {
    return Math.floor((Math.floor(spend / spendPerPoint) * multiplierHundredths) / 100);
}

export function subtotalOf(lines) {
    return lines.reduce((sum, line) => sum + line.price * line.quantity, 0);
}

/**
 * The rupiah value a pinned member voucher takes off this cart. The register
 * never charges more than the subtotal, mirroring the server-side cap.
 */
export function voucherDiscount(voucher, subtotal) {
    return voucher ? Math.min(voucher.amount, subtotal) : 0;
}

export function formatRupiah(amount) {
    const rounded = Math.round(amount);
    const grouped = Math.abs(rounded).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return `${rounded < 0 ? '-' : ''}Rp ${grouped}`;
}

/**
 * Cash amounts a customer is likely to hand over: the exact total, then the next round 10.000, 50.000 and 100.000.
 */
export function cashSuggestions(total) {
    if (total <= 0) {
        return [];
    }

    const steps = [10000, 50000, 100000].map((step) => Math.ceil(total / step) * step);

    return [...new Set([total, ...steps])].sort((a, b) => a - b).slice(0, 4);
}
