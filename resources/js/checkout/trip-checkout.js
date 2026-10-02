/**
 * Trip checkout page (resources/views/pages/trip-checkout). Registers the `tripCheckout`
 * Alpine component; Alpine itself ships with the Livewire bundle in the layout.
 *
 * Boot data comes from the JSON config rendered by TripCheckoutViewModel::clientConfig(), so no
 * server values are interpolated into this script. All dynamic text is rendered with x-text.
 * The estimate (price per person × party size) is display-only; the server re-estimates it.
 */
import { MoneyFormatter } from './pricing';
import { CONTACT_FIELDS, ContactValidator } from './contact-validator';
import { BookingClient, CHECKOUT_FIELDS } from './booking-client';
import { choice, readConfig, trans } from './support';

const CONFIG_ELEMENT_ID = 'trip-checkout-config';
const RECAPTCHA_SELECTOR = '#checkout-recaptcha';
const ERROR_ORDER = ['date', 'wish', 'persons', ...CONTACT_FIELDS, 'message', 'captcha'];

/** Server field → client error key (TripCheckoutRequest). */
const FIELD_MAP = {
    ...CHECKOUT_FIELDS,
    departure_date: 'date',
    wish_start: 'wish',
    wish_end: 'wish',
    persons: 'persons',
    message: 'message',
};

/** "03.10.2026" / "10/3/2026" for a Y-m-d string, without timezone shifts. */
const shortDate = (locale, iso) => new Intl.DateTimeFormat(locale, {
    day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'UTC',
}).format(new Date(`${iso}T00:00:00Z`));

function tripCheckout() {
    const config = readConfig(CONFIG_ELEMENT_ID);
    const i18n = config.i18n || {};
    const locale = config.locale || 'de';
    // Service objects stay outside Alpine's reactive state.
    const money = new MoneyFormatter(locale, config.currency || 'EUR');
    const validator = new ContactValidator(i18n.errors);
    const client = new BookingClient(config.submitUrl, FIELD_MAP);
    const departures = Array.isArray(config.departures) ? config.departures : [];
    const pricePerPerson = Number(config.pricePerPerson) || 0;
    let submitAttempt = 0;

    return {
        i18n,
        fixedDates: Boolean(config.fixedDates),
        departureDate: config.departureDate || '',
        datesOpen: false,
        wishStart: '',
        wishEnd: '',
        persons: config.persons || 2,
        maxPersons: config.maxPersons || 20,
        contact: { ...(config.contact || {}) },
        message: '',
        errors: {},
        formError: '',
        loading: false,

        // Date
        get selected() {
            return departures.find((departure) => departure.date === this.departureDate) || null;
        },

        get hasDate() {
            return !this.fixedDates || Boolean(this.selected);
        },

        toggleDates() {
            if (this.datesOpen) {
                this.closeDates();
            } else {
                this.openDates();
            }
        },

        openDates() {
            this.datesOpen = true;
            this.$nextTick(() => {
                const options = this.dateOptions();
                (options.find((option) => option.dataset.date === this.departureDate) || options[0])?.focus();
            });
        },

        closeDates() {
            if (!this.datesOpen) return;
            this.datesOpen = false;
            this.$refs.datesTrigger?.focus();
        },

        dateOptions() {
            return Array.from(this.$refs.datesList?.querySelectorAll('[role="option"]') || []);
        },

        moveDateFocus(step) {
            const options = this.dateOptions();
            if (!options.length) return;
            const index = options.indexOf(document.activeElement);
            options[(index + step + options.length) % options.length].focus();
        },

        pickDate(date) {
            this.departureDate = date;
            this.clearError('date');
            this.closeDates();
        },

        openPicker(ref) {
            const input = this.$refs[ref];
            if (typeof input?.showPicker !== 'function') return;

            try {
                input.showPicker();
            } catch (error) {
                // This phone already opened its picker from the tap.
            }
        },

        // Both inputs are marked when one is missing (that one) or the order is wrong (both).
        wishInvalid(field) {
            if (!this.errors.wish) return false;

            return !this[field] || (Boolean(this.wishStart) && Boolean(this.wishEnd));
        },

        get wishRange() {
            if (this.wishStart && this.wishEnd) {
                return `${shortDate(locale, this.wishStart)} – ${shortDate(locale, this.wishEnd)}`;
            }

            return this.wishStart ? trans(i18n.wishFrom, { date: shortDate(locale, this.wishStart) }) : '';
        },

        get sideDate() {
            if (!this.fixedDates) {
                return this.wishRange ? trans(i18n.wishRange, { range: this.wishRange }) : i18n.wishOpen;
            }

            return this.selected ? this.selected.label : i18n.noDate;
        },

        get barSub() {
            const when = this.fixedDates ? (this.selected ? this.selected.short : i18n.chooseDate) : i18n.wish;

            return `${when} · ${this.personsLabel}`;
        },

        // Party
        changePersons(delta) {
            this.persons = Math.max(1, Math.min(this.maxPersons, this.persons + delta));
            this.clearError('persons');
        },

        get personsLabel() {
            return choice(i18n.personsCount, this.persons);
        },

        get capacityHint() {
            const spots = this.selected?.spots;

            return spots !== null && spots !== undefined && this.persons > spots ? choice(i18n.capacity, spots) : '';
        },

        // Estimate
        get total() {
            return pricePerPerson * this.persons;
        },

        // A fixed departure gets "approx."; a preferred window only "from" until the offer.
        get exact() {
            return this.fixedDates && Boolean(this.selected);
        },

        get lineLabel() {
            const price = pricePerPerson ? money.format(pricePerPerson) : i18n.onRequest;

            return trans(this.exact || !pricePerPerson ? i18n.line : i18n.lineFrom, { persons: this.personsLabel, price });
        },

        get lineAmount() {
            if (!pricePerPerson) return i18n.onRequest;

            return this.exact ? money.format(this.total) : trans(i18n.from, { amount: money.format(this.total) });
        },

        get totalLabel() {
            if (!pricePerPerson) return i18n.onRequest;

            return trans(this.exact ? i18n.approx : i18n.from, { amount: money.format(this.total) });
        },

        get note() {
            return this.exact ? i18n.noteFixed : i18n.noteRequest;
        },

        // Validation + submission
        get hasErrors() {
            return Object.keys(this.errors).length > 0;
        },

        clearError(field) {
            if (!this.errors[field]) return;
            const { [field]: removed, ...rest } = this.errors;
            this.errors = rest;
        },

        recaptcha() {
            return typeof window.RecaptchaWidget === 'function' && document.querySelector(RECAPTCHA_SELECTOR)
                ? new window.RecaptchaWidget(RECAPTCHA_SELECTOR)
                : null;
        },

        validate(captcha) {
            const errors = validator.validate(this.contact);

            if (this.fixedDates) {
                if (!this.selected) errors.date = i18n.errors?.date;
            } else if (!this.wishStart || !this.wishEnd) {
                errors.wish = i18n.errors?.wishRequired;
            } else if (this.wishEnd < this.wishStart) {
                errors.wish = i18n.errors?.wishOrder;
            }

            // Invisible widgets fetch their token on submit; only a checkbox must be solved first.
            if (captcha && !captcha.isInvisible?.() && !captcha.getResponse()) errors.captcha = i18n.errors?.captcha;

            this.errors = errors;

            return ERROR_ORDER.find((field) => errors[field]) || null;
        },

        // Mobile book bar covers the bottom of the viewport. A checkbox that still
        // sits under that bar (or further down the page) has to be scrolled up.
        captchaSitsBelow(target) {
            if (!window.matchMedia('(max-width: 1023px)').matches) {
                return false;
            }

            const dock = document.querySelector('.cc-dock');
            const limit = dock ? dock.getBoundingClientRect().top : window.innerHeight;

            return target.getBoundingClientRect().bottom > limit - 8;
        },

        scrollCaptchaIntoView(target) {
            const dock = document.querySelector('.cc-dock');
            const dockHeight = dock ? dock.getBoundingClientRect().height : 0;
            const rect = target.getBoundingClientRect();
            const room = window.innerHeight - dockHeight - 24;
            const top = rect.height > room
                ? window.scrollY + rect.top - 16
                : window.scrollY + rect.bottom - (window.innerHeight - dockHeight - 24);

            window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
        },

        revealError(field) {
            const target = this.$refs[`field_${field}`];
            if (!target) return;

            if (field === 'captcha' && this.captchaSitsBelow(target)) {
                this.scrollCaptchaIntoView(target);
            } else {
                // Keep the field clear of the sticky site nav and the mobile total bar.
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // The first empty date input, else the first control in the block.
            const focusable = target.matches('input, select, textarea, button')
                ? target
                : (field === 'wish' && !this.wishStart ? this.$refs.wishStart : null)
                    || (field === 'wish' && !this.wishEnd ? this.$refs.wishEnd : null)
                    || target.querySelector('input, select, textarea, button');
            focusable?.focus({ preventScroll: true });
        },

        async captchaToken(captcha) {
            if (!captcha) return '';
            if (!captcha.isInvisible?.()) return captcha.getResponse();

            return captcha.execute();
        },

        payload(captchaToken) {
            const dates = this.fixedDates
                ? { departure_date: this.departureDate }
                : { wish_start: this.wishStart, wish_end: this.wishEnd };

            return {
                ...dates,
                persons: this.persons,
                first_name: this.contact.firstName,
                last_name: this.contact.lastName,
                email: this.contact.email,
                country_code: this.contact.countryCode,
                phone: this.contact.phone,
                message: this.message,
                'g-recaptcha-response': captchaToken,
            };
        },

        async submit() {
            if (this.loading) return;

            this.formError = '';
            const captcha = this.recaptcha();
            const firstInvalid = this.validate(captcha);
            if (firstInvalid) {
                this.$nextTick(() => this.revealError(firstInvalid));
                return;
            }

            const attempt = ++submitAttempt;
            const captchaToken = await this.captchaToken(captcha);
            if (attempt !== submitAttempt || this.loading) return; // superseded by a newer click
            if (captcha && !captchaToken) {
                this.errors = { captcha: i18n.errors?.captcha };
                this.$nextTick(() => this.revealError('captcha'));
                return;
            }

            this.loading = true;
            const result = await client.submit(this.payload(captchaToken));

            if (result.ok) {
                window.location.assign(result.redirectUrl);
                return;
            }

            this.loading = false;
            this.errors = result.fieldErrors;

            if (result.status === 429) {
                this.formError = result.retryAfter > 0
                    ? `${i18n.errors?.tooManyRequests} (${result.retryAfter}s)`
                    : i18n.errors?.tooManyRequests;
            } else if (result.message || result.status !== 422) {
                this.formError = result.message || i18n.errors?.unexpected;
            }

            // Only a request that reached validation consumed the captcha token.
            if (result.status === 422) {
                captcha?.reset();
            }

            const firstServerError = ERROR_ORDER.find((field) => this.errors[field]);
            this.$nextTick(() => (firstServerError ? this.revealError(firstServerError) : this.revealError('submit')));
        },
    };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('tripCheckout', tripCheckout);
});
