/**
 * Tour checkout page (resources/views/pages/modern-checkout). Registers the `tourCheckout`
 * Alpine component; Alpine itself ships with the Livewire bundle in the layout.
 *
 * Boot data comes from the JSON config rendered by TourCheckoutViewModel::clientConfig(), so no
 * server values are interpolated into this script. All dynamic text is rendered with x-text.
 */
import { TourCalendar } from './calendar';
import { calendarNavigation, composeState } from './calendar-state';
import { MoneyFormatter, TourPricing } from './pricing';
import { CONTACT_FIELDS, ContactValidator } from './contact-validator';
import { BookingClient } from './booking-client';
import { StickyBarWatcher } from './sticky-bar';
import { choice, formatIsoDate, readConfig, trans } from './support';

const CONFIG_ELEMENT_ID = 'tour-checkout-config';
const MOBILE_MEDIA = '(max-width: 1023px)'; // include-media "<desktop"
const RECAPTCHA_SELECTOR = '#checkout-recaptcha';
const ERROR_ORDER = ['date', ...CONTACT_FIELDS, 'captcha'];

function tourCheckout() {
    const config = readConfig(CONFIG_ELEMENT_ID);
    const i18n = config.i18n || {};
    // Service objects stay outside Alpine's reactive state.
    const calendar = new TourCalendar({ minDate: config.minDate, blocked: config.blocked, allowed: config.allowedDates });
    // Reschedule: contact details come from the original request and are not editable here.
    const contactLocked = Boolean(config.contactLocked);
    const pricing = new TourPricing(config.pricing || {});
    const money = new MoneyFormatter(config.locale || 'de');
    const validator = new ContactValidator(i18n.errors);
    const client = new BookingClient(config.submitUrl);
    const dateFormat = new Intl.DateTimeFormat(config.locale || 'de', {
        weekday: 'short', day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC',
    });
    const shortDateFormat = new Intl.DateTimeFormat(config.locale || 'de', {
        day: 'numeric', month: 'short', timeZone: 'UTC',
    });
    let stickyBar = null;
    let submitAttempt = 0;

    return composeState(calendarNavigation(calendar, { minDate: config.minDate, months: i18n.months }), {
        persons: config.persons || 1,
        maxGuests: config.maxGuests || 1,
        selectedDate: config.selectedDate || null,
        selectedExtras: Array.isArray(config.initialExtras) ? [...config.initialExtras] : [],
        contact: { ...(config.contact || {}) },
        errors: {},
        formError: '',
        loading: false,
        showBar: false,

        init() {
            this.showMonthOf(this.selectedDate || calendar.firstAvailable());

            stickyBar = new StickyBarWatcher({
                anchor: this.$refs.product,
                cta: this.$refs.cta,
                media: MOBILE_MEDIA,
                onChange: (visible) => { this.showBar = visible; },
            }).start();
        },

        destroy() {
            stickyBar?.stop();
        },

        // Calendar selection (navigation comes from calendarNavigation)
        isSelected(iso) {
            return iso === this.selectedDate;
        },

        selectDate(iso) {
            if (!calendar.isAvailable(iso)) return;
            this.selectedDate = iso;
            this.clearError('date');
        },

        get selectedLabel() {
            return this.selectedDate ? formatIsoDate(dateFormat, this.selectedDate) : i18n.noDate;
        },

        // Guests
        get atMax() {
            return this.persons >= this.maxGuests;
        },

        changePersons(delta) {
            this.persons = Math.max(1, Math.min(this.maxGuests, this.persons + delta));
        },

        get unitLabel() {
            return this.persons === 1 ? i18n.person : i18n.persons;
        },

        get participantsLabel() {
            return choice(i18n.participants, this.persons);
        },

        // Pricing
        isExtraSelected(index) {
            return this.selectedExtras.includes(index);
        },

        toggleExtra(index) {
            this.selectedExtras = this.isExtraSelected(index)
                ? this.selectedExtras.filter((i) => i !== index)
                : [...this.selectedExtras, index];
        },

        extraTotal(price) {
            return this.money(price * this.persons);
        },

        get basePrice() {
            return pricing.basePrice(this.persons);
        },

        get baseLine() {
            return pricing.perPerson
                ? trans(i18n.perPersonLine, { price: this.money(this.basePrice / this.persons), count: this.persons, unit: this.unitLabel })
                : trans(i18n.fixedLine, { count: this.persons, unit: this.unitLabel });
        },

        get total() {
            return pricing.total(this.persons, this.selectedExtras);
        },

        get barSubline() {
            const date = this.selectedDate ? formatIsoDate(shortDateFormat, this.selectedDate) : i18n.noDate;

            return `${this.participantsLabel} · ${date}`;
        },

        money(amount) {
            return money.format(amount);
        },

        // Validation + submission
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
            const errors = contactLocked ? {} : validator.validate(this.contact);
            if (!this.selectedDate) errors.date = i18n.errors?.date;
            // Invisible widgets fetch their token on submit; only a checkbox must be solved first.
            if (captcha && !captcha.isInvisible?.() && !captcha.getResponse()) errors.captcha = i18n.errors?.captcha;

            this.errors = errors;

            return ERROR_ORDER.find((field) => errors[field]) || null;
        },

        revealError(field) {
            const target = this.$refs[`field_${field}`];
            if (!target) return;

            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const focusable = target.matches('input, select, button') ? target : target.querySelector('input, select, button');
            focusable?.focus({ preventScroll: true });
        },

        /**
         * Token to send with the request. An invisible widget is run now; if the visitor closes a
         * challenge, the next click simply starts a new run (the unanswered one resolves empty).
         */
        async captchaToken(captcha) {
            if (!captcha) return '';
            if (!captcha.isInvisible?.()) return captcha.getResponse();

            return captcha.execute();
        },

        payload(captchaToken) {
            const booking = {
                persons: this.persons,
                selected_date: this.selectedDate,
                extras: this.selectedExtras,
            };

            if (contactLocked) {
                return booking;
            }

            return {
                ...booking,
                guiding_id: config.guidingId,
                first_name: this.contact.firstName,
                last_name: this.contact.lastName,
                email: this.contact.email,
                country_code: this.contact.countryCode,
                phone: this.contact.phone,
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
    });
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('tourCheckout', tourCheckout);
});
