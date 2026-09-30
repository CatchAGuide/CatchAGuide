/**
 * Guide "decline request" form (resources/views/pages/modern-checkout/reject.blade.php).
 * Registers the `bookingReject` Alpine component: pick 1..maxDates alternative dates on the
 * shared checkout calendar, write a message, send. Rules mirror App\Http\Requests\RejectionRequest,
 * which stays authoritative. Boot data comes from BookingRejectViewModel::clientConfig().
 */
import { TourCalendar } from './calendar';
import { calendarNavigation, composeState } from './calendar-state';
import { BookingClient } from './booking-client';
import { formatIsoDate, readConfig, trans } from './support';

const CONFIG_ELEMENT_ID = 'booking-reject-config';
const ERROR_ORDER = ['date', 'message'];

function bookingReject() {
    const config = readConfig(CONFIG_ELEMENT_ID);
    const i18n = config.i18n || {};
    const errorsText = i18n.errors || {};
    const maxDates = config.maxDates || 5;
    const minMessage = config.minMessage || 50;
    const calendar = new TourCalendar({ minDate: config.minDate, blocked: config.blocked });
    const client = new BookingClient(config.submitUrl, { alternative_dates: 'date', reason: 'message' });
    const chipFormat = new Intl.DateTimeFormat(config.locale || 'de', {
        weekday: 'short', day: 'numeric', month: 'short', timeZone: 'UTC',
    });
    const longFormat = new Intl.DateTimeFormat(config.locale || 'de', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC',
    });

    return composeState(calendarNavigation(calendar, { minDate: config.minDate, months: i18n.months }), {
        dates: [],
        message: '',
        errors: {},
        formError: '',
        loading: false,

        init() {
            // Open near the requested date: alternatives are usually close to it.
            const start = config.startDate && config.startDate > config.minDate ? config.startDate : config.minDate;
            this.showMonthOf(calendar.firstAvailable(start) || start);
        },

        // Calendar selection: several dates, tap again to remove.
        isSelected(iso) {
            return this.dates.includes(iso);
        },

        selectDate(iso) {
            if (this.isSelected(iso)) {
                this.dates = this.dates.filter((d) => d !== iso);
                return;
            }

            if (!calendar.isAvailable(iso)) return;

            if (this.dates.length >= maxDates) {
                this.errors = { ...this.errors, date: errorsText.datesMax };
                return;
            }

            this.dates = [...this.dates, iso].sort();
            this.clearError('date');
        },

        get selectedLabel() {
            return this.dates.map((iso) => this.shortLabel(iso)).join(', ') || i18n.noDates;
        },

        shortLabel(iso) {
            return formatIsoDate(chipFormat, iso);
        },

        removeLabel(iso) {
            return trans(i18n.removeDate || ':date', { date: formatIsoDate(longFormat, iso) });
        },

        get countLabel() {
            return this.dates.length ? trans(i18n.datesCount, { count: this.dates.length, max: maxDates }) : i18n.noDates;
        },

        // Message
        get messageOk() {
            return this.message.trim().length >= minMessage;
        },

        get charsLabel() {
            return trans(i18n.chars, { count: this.message.trim().length, min: minMessage });
        },

        // Validation + submission
        clearError(field) {
            if (!this.errors[field]) return;
            const { [field]: removed, ...rest } = this.errors;
            this.errors = rest;
        },

        validate() {
            const errors = {};
            if (!this.dates.length) errors.date = errorsText.datesRequired;
            if (!this.messageOk) errors.message = errorsText.message;
            this.errors = errors;

            return ERROR_ORDER.find((field) => errors[field]) || null;
        },

        revealError(field) {
            const target = this.$refs[`field_${field}`];
            if (!target) return;

            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const focusable = target.matches('textarea, button') ? target : target.querySelector('textarea');
            focusable?.focus({ preventScroll: true });
        },

        async submit() {
            if (this.loading) return;

            this.formError = '';
            const firstInvalid = this.validate();
            if (firstInvalid) {
                this.$nextTick(() => this.revealError(firstInvalid));
                return;
            }

            this.loading = true;
            const result = await client.submit({ alternative_dates: this.dates, reason: this.message.trim() });

            if (result.ok) {
                // Replace the form entry so Back returns to the bookings list.
                // The form URL is no-store, so a history entry would be refetched
                // and show a second confirmation after the request is no longer pending.
                window.location.replace(result.redirectUrl);
                return;
            }

            this.loading = false;
            this.errors = result.fieldErrors;
            if (result.status === 429) {
                this.formError = errorsText.tooManyRequests;
            } else if (result.message || result.status !== 422) {
                this.formError = result.message || errorsText.unexpected;
            }

            const firstServerError = ERROR_ORDER.find((field) => this.errors[field]);
            this.$nextTick(() => this.revealError(firstServerError || 'submit'));
        },
    });
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('bookingReject', bookingReject);
});
