/**
 * Month grid for the tour checkout calendar. Works on Y-m-d strings only, so no timezone can
 * shift a day: availability is "on or after minDate and outside every blocked range", which
 * mirrors the server rule (after:today + Guiding::isDateBlocked). On the reschedule page an
 * `allowed` list additionally limits it to the guide's suggested dates.
 */
const pad = (n) => String(n).padStart(2, '0');

export const isoDate = (year, month, day) => `${year}-${pad(month + 1)}-${pad(day)}`;

export class TourCalendar {
    /**
     * @param {{ minDate: string, blocked: Array<{from: string, due: string}>, allowed?: string[]|null }} options
     */
    constructor({ minDate, blocked, allowed = null }) {
        this.minDate = minDate;
        this.blocked = Array.isArray(blocked) ? blocked : [];
        this.allowed = Array.isArray(allowed) ? [...allowed].sort() : null;
    }

    isAllowed(iso) {
        return this.allowed === null || this.allowed.includes(iso);
    }

    isBlocked(iso) {
        return this.blocked.some((range) => iso >= range.from && iso <= range.due);
    }

    isPast(iso) {
        return iso < this.minDate;
    }

    isAvailable(iso) {
        return !this.isPast(iso) && this.isAllowed(iso) && !this.isBlocked(iso);
    }

    /**
     * First bookable day on or after `from`, jumping over blocked ranges; null when none
     * is found within `maxDays`.
     */
    firstAvailable(from = this.minDate, maxDays = 730) {
        if (this.allowed !== null) {
            return this.allowed.find((iso) => iso >= from && this.isAvailable(iso)) || null;
        }

        let cursor = from < this.minDate ? this.minDate : from;

        for (let i = 0; i < maxDays; i++) {
            const range = this.blocked.find((r) => cursor >= r.from && cursor <= r.due);
            if (!range) {
                return cursor;
            }
            cursor = TourCalendar.addDays(range.due, 1);
        }

        return null;
    }

    /**
     * @returns {{ leadingBlanks: number, days: Array<{iso: string, day: number, past: boolean, blocked: boolean, available: boolean}> }}
     */
    month(year, month) {
        // Sunday-first grid, matching the weekday header.
        const leadingBlanks = new Date(Date.UTC(year, month, 1)).getUTCDay();
        const length = new Date(Date.UTC(year, month + 1, 0)).getUTCDate();
        const days = [];

        for (let day = 1; day <= length; day++) {
            const iso = isoDate(year, month, day);
            const past = this.isPast(iso);
            const blocked = !past && (!this.isAllowed(iso) || this.isBlocked(iso));
            days.push({ iso, day, past, blocked, available: !past && !blocked });
        }

        return { leadingBlanks, days };
    }

    static addDays(iso, amount) {
        const [y, m, d] = iso.split('-').map(Number);
        const date = new Date(Date.UTC(y, m - 1, d + amount));

        return isoDate(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate());
    }

    static parts(iso) {
        const [y, m] = iso.split('-').map(Number);

        return { year: y, month: m - 1 };
    }
}
