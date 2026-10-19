/**
 * Gift voucher checkout (resources/views/pages/voucher-checkout). Registers the
 * `voucherCheckout` Alpine component: the tour checkout without calendar and extras, plus the
 * recipient / sender / message fields. Boot data comes from VoucherCheckoutViewModel::clientConfig().
 */
import { MoneyFormatter, TourPricing } from './pricing';
import { CONTACT_FIELDS, ContactValidator } from './contact-validator';
import { BookingClient, CHECKOUT_FIELDS } from './booking-client';
import { StickyBarWatcher } from './sticky-bar';
import { choice, readConfig, trans } from './support';

const CONFIG_ELEMENT_ID = 'voucher-checkout-config';
const MOBILE_MEDIA = '(max-width: 1023px)'; // include-media "<desktop"
const RECAPTCHA_SELECTOR = '#checkout-recaptcha';
const ERROR_ORDER = ['recipients', 'sender', 'message', ...CONTACT_FIELDS, 'captcha'];
const VOUCHER_FIELDS = {
    ...CHECKOUT_FIELDS,
    recipient_names: 'recipients',
    sender_name: 'sender',
    personal_message: 'message',
};

function voucherCheckout() {
    const config = readConfig(CONFIG_ELEMENT_ID);
    const i18n = config.i18n || {};
    const limits = config.limits || {};
    const pricing = new TourPricing(config.pricing || {});
    const money = new MoneyFormatter(config.locale || 'de');
    const validator = new ContactValidator(i18n.errors);
    const client = new BookingClient(config.submitUrl, VOUCHER_FIELDS);
    let stickyBar = null;
    let submitAttempt = 0;

    return {
        persons: config.persons || 1,
        maxGuests: config.maxGuests || 1,
        voucher: { ...(config.voucher || {}) },
        contact: { ...(config.contact || {}) },
        errors: {},
        formError: '',
        loading: false,
        showBar: false,

        init() {
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

        // Pricing (the regular tour price for the guest count)
        get basePrice() {
            return pricing.basePrice(this.persons);
        },

        get baseLine() {
            return pricing.perPerson
                ? trans(i18n.perPersonLine, { price: this.money(this.basePrice / this.persons), count: this.persons, unit: this.unitLabel })
                : trans(i18n.fixedLine, { count: this.persons, unit: this.unitLabel });
        },

        get total() {
            return pricing.total(this.persons, []);
        },

        get barSubline() {
            return this.participantsLabel;
        },

        get messageCounter() {
            return `${String(this.voucher.message || '').length} / ${limits.message || 200}`;
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
            const errors = validator.validate(this.contact);
            if (!String(this.voucher.recipients || '').trim()) errors.recipients = i18n.errors?.recipients;
            if (captcha && !captcha.isInvisible?.() && !captcha.getResponse()) errors.captcha = i18n.errors?.captcha;

            this.errors = errors;

            return ERROR_ORDER.find((field) => errors[field]) || null;
        },

        revealError(field) {
            const target = this.$refs[`field_${field}`];
            if (!target) return;

            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const focusable = target.matches('input, select, textarea, button') ? target : target.querySelector('input, select, textarea, button');
            focusable?.focus({ preventScroll: true });
        },

        async captchaToken(captcha) {
            if (!captcha) return '';
            if (!captcha.isInvisible?.()) return captcha.getResponse();

            return captcha.execute();
        },

        payload(captchaToken) {
            return {
                persons: this.persons,
                recipient_names: this.voucher.recipients,
                sender_name: this.voucher.sender,
                personal_message: this.voucher.message,
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

            if (result.status === 422) {
                captcha?.reset();
            }

            const firstServerError = ERROR_ORDER.find((field) => this.errors[field]);
            this.$nextTick(() => (firstServerError ? this.revealError(firstServerError) : this.revealError('submit')));
        },
    };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('voucherCheckout', voucherCheckout);
});
