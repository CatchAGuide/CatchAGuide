/**
 * Client-side mirror of TourCheckoutPricing for instant totals. It only looks up the
 * server-computed price table; the server re-quotes on submission, so these numbers are
 * display-only.
 */
const round2 = (value) => Math.round(value * 100) / 100;

export class TourPricing {
    /**
     * @param {{ perPerson: boolean, table: Object<string, number>, extras: Array<{index: number, name: string, price: number}> }} config
     */
    constructor({ perPerson, table, extras }) {
        this.perPerson = Boolean(perPerson);
        this.table = table || {};
        this.extras = Array.isArray(extras) ? extras : [];
    }

    basePrice(persons) {
        return Number(this.table[persons] ?? 0);
    }

    extrasTotal(persons, selected) {
        return round2(this.extras
            .filter((extra) => selected.includes(extra.index))
            .reduce((sum, extra) => sum + extra.price * persons, 0));
    }

    total(persons, selected) {
        return round2(this.basePrice(persons) + this.extrasTotal(persons, selected));
    }
}

export class MoneyFormatter {
    constructor(locale, currency = 'EUR') {
        const options = { style: 'currency', currency };
        this.whole = new Intl.NumberFormat(locale, { ...options, maximumFractionDigits: 0 });
        this.cents = new Intl.NumberFormat(locale, { ...options, minimumFractionDigits: 2 });
    }

    format(amount) {
        const value = round2(Number(amount) || 0);

        return (Number.isInteger(value) ? this.whole : this.cents).format(value);
    }
}
