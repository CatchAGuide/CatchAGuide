/**
 * Gift voucher landing page (resources/views/pages/voucher-landing). Registers the
 * `voucherLanding` Alpine component: the live voucher preview, the country → region → city
 * selects, the region tiles, the amount chips and the same checks the server runs
 * (VoucherLandingRequest). The form then posts normally. Boot data comes from
 * VoucherLandingViewModel::clientConfig().
 */
import { readConfig, trans } from '../checkout/support';

const CONFIG_ELEMENT_ID = 'voucher-landing-config';
const RECAPTCHA_SELECTOR = '#voucher-landing-recaptcha';
const MOBILE_MEDIA = '(max-width: 1023px)'; // include-media "<desktop"
const FLASH_MS = 2600;
const PHONE_PATTERN = /^[0-9][0-9 ()/.-]{2,24}$/;
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const ERROR_ORDER = ['country', 'region', 'city', 'custom_amount', 'name', 'phone', 'email', 'consent'];

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
        flash: '',
        submitting: false,
        pulses: 0,
        showBar: false,

        init() {
            this.watchViewport();
            this.watchNav();
            this.parallax();

            const firstError = ERROR_ORDER.find((field) => this.errors[field]);
            if (firstError) {
                this.$nextTick(() => this.reveal(firstError));
            }
        },

        isOpen(value) {
            return value === open;
        },

        get countryOption() {
            return this.destinations.find((option) => option.name === this.country) || null;
        },

        get regionOptions() {
            return this.countryOption ? this.countryOption.regions : [];
        },

        get cityOptions() {
            const region = this.regionOptions.find((option) => option.name === this.region);

            return region ? region.cities : [];
        },

        get regionDisabled() {
            return !this.country || this.isOpen(this.country) || this.regionOptions.length === 0;
        },

        get regionRequired() {
            return !this.regionDisabled;
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
            const name = this.recipient.trim();

            return name ? trans(i18n.for, { name }) : i18n.forDefault;
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

        onCountry() {
            this.region = this.isOpen(this.country) ? open : '';
            this.city = '';
            this.clearError('country');
            this.clearError('region');
            this.bumpPreview();
        },

        onRegion() {
            this.city = '';
            this.clearError('region');
            this.bumpPreview();
        },

        pickAmount(value) {
            this.amount = value;
            this.clearError('custom_amount');
            this.bumpPreview();
        },

        /** A region tile, or the "no idea yet" tile (null, null): fill the form and scroll to it. */
        pickTile(country, region) {
            if (country && this.destinations.some((option) => option.name === country)) {
                this.country = country;
                this.region = region || '';
                this.flash = trans(i18n.flashRegion, { region, country });
            } else {
                this.country = open;
                this.region = open;
                this.flash = i18n.flashOpen;
            }
            this.city = '';
            this.clearError('country');
            this.clearError('region');
            this.bumpPreview();

            clearTimeout(this.flashTimer);
            this.flashTimer = setTimeout(() => { this.flash = ''; }, FLASH_MS);

            document.getElementById('gutschein')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        validate() {
            const errors = {};
            const amount = Number(this.customAmount);

            if (!this.country) {
                errors.country = messages.country;
            } else if (!this.regionDisabled && !this.region) {
                errors.region = messages.region;
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
                this.reveal(firstError);
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
            const input = this.$root.querySelector(`[name="${field}"]`);
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

        parallax() {
            const image = document.querySelector('[data-vl-parallax]');
            if (!image || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            let frame = null;
            window.addEventListener('scroll', () => {
                if (frame) {
                    return;
                }
                frame = requestAnimationFrame(() => {
                    frame = null;
                    image.style.transform = `translate3d(0, ${Math.min(window.scrollY, 900) * 0.25}px, 0)`;
                });
            }, { passive: true });
        },
    };
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('voucherLanding', voucherLanding);
});
