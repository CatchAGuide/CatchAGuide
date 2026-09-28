/**
 * Month navigation for the shared checkout calendar partial
 * (resources/views/pages/modern-checkout/partials/calendar.blade.php).
 *
 * The partial binds to: monthGrid, monthLabel, canGoPrev, prevMonth(), nextMonth(),
 * isSelected(iso), selectDate(iso), selectedLabel and errors.date. This part provides the
 * navigation; each page component adds its own selection rules (single date for the
 * checkout, several dates for the guide's reject form). Combine with composeState().
 */
import { TourCalendar } from './calendar';

/**
 * @param {TourCalendar} calendar
 * @param {{ minDate: string, months: string[] }} options
 */
export function calendarNavigation(calendar, { minDate, months = [] }) {
    return {
        viewYear: 0,
        viewMonth: 0,

        showMonthOf(iso) {
            ({ year: this.viewYear, month: this.viewMonth } = TourCalendar.parts(iso || minDate));
        },

        get monthGrid() {
            return calendar.month(this.viewYear, this.viewMonth);
        },

        get monthLabel() {
            return `${months[this.viewMonth] || ''} ${this.viewYear}`;
        },

        get canGoPrev() {
            const min = TourCalendar.parts(minDate);

            return this.viewYear > min.year || (this.viewYear === min.year && this.viewMonth > min.month);
        },

        prevMonth() {
            if (this.canGoPrev) this.shiftMonth(-1);
        },

        nextMonth() {
            this.shiftMonth(1);
        },

        shiftMonth(delta) {
            const date = new Date(Date.UTC(this.viewYear, this.viewMonth + delta, 1));
            this.viewYear = date.getUTCFullYear();
            this.viewMonth = date.getUTCMonth();
        },
    };
}

/**
 * Merges component parts keeping getters as getters (object spread would freeze their values).
 */
export function composeState(...parts) {
    return Object.defineProperties({}, Object.assign({}, ...parts.map((part) => Object.getOwnPropertyDescriptors(part))));
}
