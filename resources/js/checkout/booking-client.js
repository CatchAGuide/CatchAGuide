/**
 * Posts a checkout-style form as JSON (web stack: session + CSRF) and normalizes the outcome
 * into { ok, redirectUrl } | { ok: false, status, fieldErrors, message, retryAfter }.
 * Used by the tour checkout, the reschedule page and the guide's reject form.
 */
import { csrfToken } from './support';

/** Server field name → client error key (checkout + reschedule). */
export const CHECKOUT_FIELDS = {
    first_name: 'firstName',
    last_name: 'lastName',
    email: 'email',
    phone: 'phone',
    country_code: 'phone',
    selected_date: 'date',
    'g-recaptcha-response': 'captcha',
};

/** Only follow redirects that stay on this site. */
const sameOriginUrl = (url) => {
    try {
        const parsed = new URL(url, window.location.origin);

        return parsed.origin === window.location.origin ? parsed.href : null;
    } catch (e) {
        return null;
    }
};

export class BookingClient {
    /**
     * @param {string} url
     * @param {Object<string, string>} fieldMap server field (or its prefix before ".") → client error key
     */
    constructor(url, fieldMap = CHECKOUT_FIELDS) {
        this.url = url;
        this.fieldMap = fieldMap;
    }

    async submit(payload) {
        let response;
        try {
            response = await fetch(this.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(payload),
            });
        } catch (e) {
            return { ok: false, status: 0, fieldErrors: {}, message: null };
        }

        const body = await response.json().catch(() => ({}));

        if (response.ok && body.success) {
            const redirectUrl = sameOriginUrl(body.redirect_url);

            return redirectUrl ? { ok: true, redirectUrl } : { ok: false, status: 500, fieldErrors: {}, message: null };
        }

        const fieldErrors = {};
        const otherErrors = [];
        Object.entries(body.errors || {}).forEach(([key, messages]) => {
            const message = Array.isArray(messages) ? messages[0] : String(messages);
            // "alternative_dates.2" maps like "alternative_dates".
            const field = this.fieldMap[key] ?? this.fieldMap[key.split('.')[0]];
            if (field && !fieldErrors[field]) {
                fieldErrors[field] = message;
            } else if (!field) {
                otherErrors.push(message);
            }
        });

        return {
            ok: false,
            status: response.status,
            fieldErrors,
            message: otherErrors[0] || (response.status === 422 ? null : body.message || null),
            retryAfter: parseInt(body.retry_after ?? response.headers.get('Retry-After') ?? 0, 10) || 0,
        };
    }
}
