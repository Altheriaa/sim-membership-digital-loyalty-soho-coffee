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
/**
 * Computes discount for a voucher, enforcing cart presence for `free_item` rewards.
 *
 * @param  {object|null}  voucher
 * @param  {number}  subtotal
 * @param  {Array<{id:number, price:number, quantity:number}>}  cartLines
 * @return {number}
 */
export function voucherDiscount(voucher, subtotal, cartLines = []) {
    if (!voucher || subtotal <= 0) {
        return 0;
    }

    if (voucher.type === 'free_item') {
        const line = cartLines.find((item) => Number(item.id) === Number(voucher.menu_item_id));
        if (!line) {
            return 0;
        }

        return Math.min(voucher.amount, line.price, subtotal);
    }

    return Math.min(voucher.amount, subtotal);
}

/**
 * Maximum points usable against remaining subtotal without wasting excess points.
 */
export function maxRedeemablePoints(memberPoints, pointValue, remainingSubtotal) {
    if (!memberPoints || memberPoints <= 0 || !pointValue || pointValue <= 0 || remainingSubtotal <= 0) {
        return 0;
    }

    return Math.min(memberPoints, Math.floor(remainingSubtotal / pointValue));
}

export function pointDiscount(pointsToRedeem, pointValue, remainingSubtotal) {
    const usable = Math.min(Math.max(0, Number(pointsToRedeem) || 0), maxRedeemablePoints(pointsToRedeem, pointValue, remainingSubtotal));

    return usable * (Number(pointValue) || 0);
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
