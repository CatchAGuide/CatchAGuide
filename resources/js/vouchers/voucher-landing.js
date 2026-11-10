/**
 * Gift voucher landing page (resources/views/pages/voucher-landing). Registers the
 * `voucherLanding` Alpine component: the live voucher preview, Google Places destination
 * search, region tiles, amount chips and the same checks the server runs
 * (VoucherLandingRequest). The form then posts normally. Boot data comes from
 * VoucherLandingViewModel::clientConfig().
 */
import { readConfig, trans } from '../checkout/support';

const CONFIG_ELEMENT_ID = 'voucher-landing-config';
const RECAPTCHA_SELECTOR = '#voucher-landing-recaptcha';
const PLACE_INPUT_ID = 'voucherLandingSearchPlace';
const MOBILE_MEDIA = '(max-width: 1023px)'; // include-media "<desktop"
const HERO_SLIDE_MS = 7000;
const PHONE_PATTERN = /^[0-9][0-9 ()/.-]{2,24}$/;
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const ERROR_ORDER = ['country', 'custom_amount', 'name', 'phone', 'email', 'consent'];

function voucherLanding() {
    const config = readConfig(CONFIG_ELEMENT_ID);
    const i18n = config.i18n || {};
    const messages = i18n.errors || {};
    const open = config.open || '_open';
    const destinations = config.destinations || [];
    const values = config.values || {};
    const money = (amount) => (config.locale === 'de'
        ? `${Number(amount).toLocaleString('de-DE')} €`
        : `€${Number(amount).toLocaleString('en-GB')}`);

    return {
        destinations,
        place: values.place || '',
        country: values.country || '',
        region: values.region || '',
        city: values.city || '',
        amount: values.amount || '150',
        customAmount: values.customAmount || '',
        recipient: values.recipient || '',
        name: values.name || '',
        countryCode: values.countryCode || '+49',
        phone: values.phone || '',
        email: values.email || '',
        consent: Boolean(values.consent),
        errors: { ...(config.errors || {}) },
        submitting: false,
        pulses: 0,
        showBar: false,

        init() {
            this.watchViewport();
            this.watchNav();
            this.watchPlaceSearch();
            this.startHeroCarousel();

            if (this.place && document.getElementById(PLACE_INPUT_ID)) {
                document.getElementById(PLACE_INPUT_ID).value = this.place;
            }

            const firstError = ERROR_ORDER.find((field) => this.errors[field]);
            if (firstError) {
                this.$nextTick(() => this.reveal(firstError === 'country' ? PLACE_INPUT_ID : firstError));
            }
        },

        isOpen(value) {
            return value === open;
        },

        get amountLabel() {
            if (this.amount !== 'custom') {
                return money(this.amount);
            }

            return Number(this.customAmount) > 0 ? money(this.customAmount) : (config.locale === 'de' ? '— €' : '€ —');
        },

        get whereText() {
            if (!this.country || this.isOpen(this.country)) {
                return i18n.whereOpen;
            }
            if (!this.region || this.isOpen(this.region)) {
                return trans(i18n.whereCountry, { country: this.country });
            }

            return trans(i18n.whereRegion, { region: this.region, country: this.country });
        },

        get cityText() {
            return this.city ? trans(i18n.city, { city: this.city }) : '';
        },

        get forText() {
            return this.recipient.trim();
        },

        get pulseClass() {
            if (!this.pulses) {
                return '';
            }

            return this.pulses % 2 ? 'is-pulse-a' : 'is-pulse-b';
        },

        bumpPreview() {
            this.pulses += 1;
        },

        clearError(field) {
            if (this.errors[field]) {
                const { [field]: _removed, ...rest } = this.errors;
                this.errors = rest;
            }
        },

        formatPlaceLabel(country, region, city) {
            return [city, region, country].filter(Boolean).join(', ');
        },

        setPlaceInput(value) {
            const input = document.getElementById(PLACE_INPUT_ID);
            if (input) {
                input.value = value;
            }
            this.place = value;
        },

        applyDestination(country, region = '', city = '') {
            this.country = country;
            this.region = region;
            this.city = city;
            this.setPlaceInput(this.isOpen(country) ? (i18n.placeOpen || '') : this.formatPlaceLabel(country, region, city));
            this.clearError('country');
            this.bumpPreview();
        },

        onPlaceInput(event) {
            this.place = event?.target?.value ?? document.getElementById(PLACE_INPUT_ID)?.value ?? '';
            if (!this.isOpen(this.country)) {
                this.country = '';
                this.region = '';
                this.city = '';
            }
            this.clearError('country');
            this.bumpPreview();
        },

        syncPlaceFromHidden() {
            const country = document.getElementById('LocationCountryVoucherLanding')?.value || '';
            const region = document.getElementById('LocationRegionVoucherLanding')?.value || '';
            const city = document.getElementById('LocationCityVoucherLanding')?.value || '';
            const input = document.getElementById(PLACE_INPUT_ID);
            if (!country) {
                return;
            }

            this.country = country;
            this.region = region;
            this.city = city;
            this.place = input?.value || this.formatPlaceLabel(country, region, city);
            this.clearError('country');
            this.bumpPreview();
        },

        watchPlaceSearch() {
            document.addEventListener('cag:place-search-changed', () => this.syncPlaceFromHidden());
        },

        pickAmount(value) {
            this.amount = value;
            this.clearError('custom_amount');
            this.bumpPreview();
        },

        /** A region tile, or the "no idea yet" tile (null, null): fill the form and scroll to it. */
        pickTile(country, region) {
            if (country) {
                this.applyDestination(country, region || '', '');
            } else {
                this.pickOpenDestination();
            }

            document.getElementById('gutschein')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        pickOpenDestination() {
            this.applyDestination(open, open, '');
        },

        validate() {
            const errors = {};
            const amount = Number(this.customAmount);

            if (!this.country) {
                errors.country = messages.country;
            }
            if (this.amount === 'custom') {
                if (!(amount > 0)) {
                    errors.custom_amount = messages.custom_amount;
                } else if (amount < config.minAmount || amount > config.maxAmount || !Number.isInteger(amount)) {
                    errors.custom_amount = i18n.customRange;
                }
            }
            if (!this.name.trim()) {
                errors.name = messages.name;
            }
            if (!this.phone.trim()) {
                errors.phone = messages.phone;
            } else if (!PHONE_PATTERN.test(this.phone.trim()) || this.phone.replace(/\D/g, '').length < 6) {
                errors.phone = messages.phone_invalid;
            }
            if (!this.email.trim()) {
                errors.email = messages.email;
            } else if (!EMAIL_PATTERN.test(this.email.trim())) {
                errors.email = messages.email_invalid;
            }
            if (!this.consent) {
                errors.consent = messages.consent;
            }

            return errors;
        },

        async submit() {
            if (this.submitting) {
                return;
            }

            this.errors = this.validate();
            const firstError = ERROR_ORDER.find((field) => this.errors[field]);
            if (firstError) {
                this.reveal(firstError === 'country' ? PLACE_INPUT_ID : firstError);
                return;
            }

            this.submitting = true;

            const captcha = typeof window.RecaptchaWidget === 'function' && document.querySelector(RECAPTCHA_SELECTOR)
                ? new window.RecaptchaWidget(RECAPTCHA_SELECTOR)
                : null;
            if (captcha) {
                await captcha.execute();
            }

            this.$refs.form.submit();
        },

        reveal(field) {
            const input = field === PLACE_INPUT_ID
                ? document.getElementById(PLACE_INPUT_ID)
                : this.$root.querySelector(`[name="${field}"]`);
            input?.closest('.vl-field')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            input?.focus({ preventScroll: true });
        },

        /** Mobile bar: only between the hero and the form, and never once the request is sent. */
        watchViewport() {
            const hero = document.querySelector('[data-vl-hero]');
            const form = document.querySelector('[data-vl-form]');
            const mobile = window.matchMedia(MOBILE_MEDIA);
            if (config.submitted || !hero || !form || !('IntersectionObserver' in window)) {
                return;
            }

            let pastHero = false;
            let formIn = false;
            const update = () => { this.showBar = mobile.matches && pastHero && !formIn; };

            new IntersectionObserver(([entry]) => {
                pastHero = !entry.isIntersecting && entry.boundingClientRect.bottom < 0;
                update();
            }).observe(hero);
            new IntersectionObserver(([entry]) => {
                formIn = entry.isIntersecting;
                update();
            }, { threshold: 0.02 }).observe(form);
            mobile.addEventListener?.('change', update);
        },

        /** Overlay nav turns solid once the hero has scrolled away (as on the catalog pages). */
        watchNav() {
            const shell = document.querySelector('[data-vl-nav-shell]');
            const hero = document.querySelector('[data-vl-hero]');
            const nav = shell?.querySelector('.cag-site-nav');
            if (!nav || !hero) {
                return;
            }

            const sync = () => nav.classList.toggle('is-solid', hero.getBoundingClientRect().bottom <= nav.offsetHeight + 12);
            sync();
            window.addEventListener('scroll', sync, { passive: true });
            window.addEventListener('resize', sync);
        },

        startHeroCarousel() {
            const slides = [...document.querySelectorAll('[data-vl-hero-slide]')];
            if (slides.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            let index = 0;
            window.setInterval(() => {
                slides[index].classList.remove('is-active');
                index = (index + 1) % slides.length;
                slides[index].classList.add('is-active');
            }, HERO_SLIDE_MS);
        },
    };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('voucherLanding', voucherLanding);
});
