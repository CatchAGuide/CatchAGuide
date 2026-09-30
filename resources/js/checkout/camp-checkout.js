/**
 * Camp checkout page (resources/views/pages/camp-checkout). Registers the `campCheckout`
 * Alpine component; Alpine itself ships with the Livewire bundle in the layout.
 *
 * Boot data comes from the JSON config rendered by CampCheckoutViewModel::clientConfig(), so no
 * server values are interpolated into this script. All dynamic text is rendered with x-text.
 */
import { CampPricing } from './camp-pricing';
import { MoneyFormatter } from './pricing';
import { CONTACT_FIELDS, ContactValidator } from './contact-validator';
import { BookingClient, CHECKOUT_FIELDS } from './booking-client';
import { choice, readConfig, trans } from './support';

const CONFIG_ELEMENT_ID = 'camp-checkout-config';
const RECAPTCHA_SELECTOR = '#checkout-recaptcha';
const ERROR_ORDER = ['date', 'nights', 'persons', 'accommodation', 'boat', 'tour', 'special', ...CONTACT_FIELDS, 'message', 'captcha'];

/** Server field → client error key (CampCheckoutRequest). */
const FIELD_MAP = {
    ...CHECKOUT_FIELDS,
    arrival_date: 'date',
    nights: 'nights',
    persons: 'persons',
    accommodation_id: 'accommodation',
    rental_boat_id: 'boat',
    guiding_id: 'tour',
    special_offer_id: 'special',
    message: 'message',
};

const idOrEmpty = (value) => (value === null || value === undefined ? '' : String(value));
const idOrNull = (value) => (value === '' ? null : Number(value));

function campCheckout() {
    const config = readConfig(CONFIG_ELEMENT_ID);
    const i18n = config.i18n || {};
    // Service objects stay outside Alpine's reactive state.
    const pricing = new CampPricing(config.pricing || {});
    const money = new MoneyFormatter(config.locale || 'de', config.currency || 'EUR');
    const validator = new ContactValidator(i18n.errors);
    const client = new BookingClient(config.submitUrl, FIELD_MAP);
    let submitAttempt = 0;

    return {
        options: config.pricing || { accommodations: [], boats: [], tours: [], specials: [] },
        arrivalDate: config.arrivalDate || '',
        nights: config.nights || 1,
        persons: config.persons || 1,
        maxNights: config.maxNights || 30,
        maxPersons: config.maxPersons || 20,
        accommodationId: idOrEmpty(config.accommodationId),
        boatId: '',
        tourId: '',
        specialId: '',
        contact: { ...(config.contact || {}) },
        message: '',
        errors: {},
        formError: '',
        loading: false,

        // Stay
        get selectedUnit() {
            return pricing.find(pricing.accommodations, this.accommodationId);
        },

        get minNights() {
            return Math.max(1, this.selectedUnit?.minNights || 1);
        },

        get minNightsLabel() {
            return trans(i18n.minNights, { count: this.minNights });
        },

        openArrivalPicker() {
            const input = this.$refs.arrivalInput;
            if (typeof input?.showPicker !== 'function') {
                return;
            }

            try {
                input.showPicker();
            } catch (error) {
                // This phone already opened its picker from the tap.
            }
        },

        changeNights(delta) {
            this.nights = Math.max(this.minNights, Math.min(this.maxNights, this.nights + delta));
            this.clearError('nights');
        },

        changePersons(delta) {
            this.persons = Math.max(1, Math.min(this.maxPersons, this.persons + delta));
            this.clearError('persons');
        },

        onAccommodationChange() {
            this.clearError('accommodation');
            if (this.nights < this.minNights) {
                this.nights = this.minNights;
                this.clearError('nights');
            }
        },

        get overCapacity() {
            return Boolean(this.selectedUnit) && this.persons > this.selectedUnit.capacity;
        },

        get overCapacityLabel() {
            return trans(i18n.overCapacity, { cap: this.selectedUnit?.capacity ?? '' });
        },

        get stayShort() {
            return `${choice(i18n.nightsCount, this.nights)} · ${choice(i18n.personsCount, this.persons)}`;
        },

        // Option labels (prices follow the party size)
        unitLabel(unit) {
            const rate = pricing.accommodationRate(unit, this.persons);
            const price = rate.daily ?? (rate.weekly ? rate.weekly / 7 : null);

            return price
                ? trans(i18n.unitOption, { name: unit.name, cap: unit.capacity, price: this.money(price) })
                : trans(i18n.optionOnRequest, { name: unit.name, cap: unit.capacity });
        },

        boatLabel(boat) {
            const price = boat.daily ?? (boat.weekly ? boat.weekly / 7 : null);

            return price
                ? trans(i18n.boatOption, { name: boat.name, cap: boat.capacity, price: this.money(price) })
                : trans(i18n.optionOnRequest, { name: boat.name, cap: boat.capacity });
        },

        tourLabel(tour) {
            const price = pricing.tourPrice(tour, this.persons);

            return price
                ? trans(i18n.tourOption, { name: tour.name, cap: tour.capacity, price: this.money(price) })
                : trans(i18n.optionOnRequest, { name: tour.name, cap: tour.capacity });
        },

        specialLabel(special) {
            return special.price
                ? trans(i18n.specialOption, { name: special.name, price: this.money(special.price) })
                : trans(i18n.specialOnRequest, { name: special.name });
        },

        // Estimate
        get quoteLines() {
            return pricing.quote({
                nights: this.nights,
                persons: this.persons,
                accommodationId: this.accommodationId,
                boatId: this.boatId,
                tourId: this.tourId,
                specialId: this.specialId,
            });
        },

        // Name can wrap; the "· 3 Nächte × 55 €" tail stays one piece so its amount shares that baseline.
        lineParts(line) {
            const exact = line.unitPrice && Math.abs(line.unitPrice * line.quantity - line.amount) < 0.01;
            const withRate = (text) => (exact
                ? trans(i18n.lineRate, { label: text, price: this.money(line.unitPrice) })
                : text);

            if (line.type === 'accommodation') {
                return { name: line.name, detail: withRate(choice(i18n.nightsCount, line.quantity)) };
            }
            if (line.type === 'boat') {
                const days = choice(i18n.daysCount, line.quantity);
                const full = trans(i18n.lineBoat, { days });
                const name = full.endsWith(days) ? full.slice(0, -days.length).replace(/[\s·]+$/u, '') : full;

                return { name, detail: withRate(days) };
            }
            if (line.type === 'tour') {
                return { name: trans(i18n.lineTour, { name: line.name }), detail: '' };
            }

            return { name: line.name, detail: '' };
        },

        get lines() {
            return this.quoteLines.map((line) => ({
                key: line.key,
                ...this.lineParts(line),
                amount: line.amount > 0 ? this.money(line.amount) : i18n.onRequest,
            }));
        },

        get total() {
            return pricing.total(this.quoteLines);
        },

        get totalLabel() {
            return this.total > 0 ? trans(i18n.approx, { amount: this.money(this.total) }) : i18n.onRequest;
        },

        money(amount) {
            return money.format(amount);
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
            if (!this.arrivalDate) errors.date = i18n.errors?.date;
            if (pricing.accommodations.length && !this.selectedUnit) errors.accommodation = i18n.errors?.accommodation;
            // Invisible widgets fetch their token on submit; only a checkbox must be solved first.
            if (captcha && !captcha.isInvisible?.() && !captcha.getResponse()) errors.captcha = i18n.errors?.captcha;

            this.errors = errors;

            return ERROR_ORDER.find((field) => errors[field]) || null;
        },

        revealError(field) {
            const target = this.$refs[`field_${field}`];
            if (!target) return;

            // Keep the field clear of the sticky site nav and the mobile total bar.
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const focusable = target.matches('input, select, textarea, button')
                ? target
                : target.querySelector('input, select, textarea, button');
            focusable?.focus({ preventScroll: true });
        },

        async captchaToken(captcha) {
            if (!captcha) return '';
            if (!captcha.isInvisible?.()) return captcha.getResponse();

            return captcha.execute();
        },

        payload(captchaToken) {
            return {
                arrival_date: this.arrivalDate,
                nights: this.nights,
                persons: this.persons,
                accommodation_id: idOrNull(this.accommodationId),
                rental_boat_id: idOrNull(this.boatId),
                guiding_id: idOrNull(this.tourId),
                special_offer_id: idOrNull(this.specialId),
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
    window.Alpine.data('campCheckout', campCheckout);
});
