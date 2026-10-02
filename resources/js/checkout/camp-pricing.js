/**
 * Client-side mirror of App\Services\Checkout\Camp\CampCheckoutPricing (+ StayRate) for
 * instant estimates. It only works on server-provided prices; the server re-quotes on
 * submission, so these numbers are display-only.
 */
const round2 = (value) => Math.round(value * 100) / 100;
const sameId = (a, b) => String(a) === String(b);

export class StayRate {
    constructor({ daily = null, weekly = null } = {}) {
        this.daily = daily > 0 ? Number(daily) : null;
        this.weekly = weekly > 0 ? Number(weekly) : null;
    }

    /** Whole weeks at the weekly rate plus remaining nights, never above paying every night. */
    total(units) {
        const n = Math.max(0, units);
        if (this.daily === null) {
            return this.weekly === null ? 0 : round2(Math.ceil(n / 7) * this.weekly);
        }

        const allDaily = n * this.daily;
        if (this.weekly === null || n < 7) return round2(allDaily);

        return round2(Math.min(allDaily, Math.floor(n / 7) * this.weekly + (n % 7) * this.daily));
    }
}

export class CampPricing {
    /**
     * @param {{ accommodations: Array, boats: Array, tours: Array, specials: Array }} config
     */
    constructor({ accommodations = [], boats = [], tours = [], specials = [] } = {}) {
        this.accommodations = accommodations;
        this.boats = boats;
        this.tours = tours;
        this.specials = specials;
    }

    find(list, id) {
        return id === '' || id === null ? null : list.find((item) => sameId(item.id, id)) || null;
    }

    /** Tier for exactly that many guests, else the largest tier below, else the smallest. */
    accommodationRate(unit, persons) {
        const tiers = unit?.tiers || [];
        let match = tiers[0] || {};
        tiers.forEach((tier) => {
            if (tier.persons <= persons) match = tier;
        });

        return new StayRate(match);
    }

    tourPrice(tour, persons) {
        const counts = Object.keys(tour?.prices || {}).map(Number);
        if (!counts.length) return 0;

        return Number(tour.prices[Math.min(Math.max(1, persons), Math.max(...counts))] || 0);
    }

    /**
     * @returns {Array<{ key: string, type: string, name: string, quantity: number, unitPrice: ?number, amount: number }>}
     */
    quote({ nights, persons, accommodationId, boatId, tourId, specialId }) {
        const lines = [];
        const unit = this.find(this.accommodations, accommodationId);
        const boat = this.find(this.boats, boatId);
        const tour = this.find(this.tours, tourId);
        const special = this.find(this.specials, specialId);

        if (unit) {
            const rate = this.accommodationRate(unit, persons);
            lines.push({ key: `a${unit.id}`, type: 'accommodation', name: unit.name, quantity: nights, unitPrice: rate.daily, amount: rate.total(nights) });
        }
        if (boat) {
            const rate = new StayRate(boat);
            lines.push({ key: `b${boat.id}`, type: 'boat', name: boat.name, quantity: nights, unitPrice: rate.daily, amount: rate.total(nights) });
        }
        if (tour) {
            const price = this.tourPrice(tour, persons);
            lines.push({ key: `t${tour.id}`, type: 'tour', name: tour.name, quantity: 1, unitPrice: price, amount: price });
        }
        if (special) {
            lines.push({ key: `s${special.id}`, type: 'special', name: special.name, quantity: 1, unitPrice: special.price, amount: Number(special.price) || 0 });
        }

        return lines;
    }

    total(lines) {
        return round2(lines.reduce((sum, line) => sum + line.amount, 0));
    }
}
