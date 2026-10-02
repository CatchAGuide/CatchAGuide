/**
 * Client-side contact checks for instant feedback. The same rules are enforced by
 * App\Http\Requests\TourCheckoutRequest, which stays authoritative.
 */
const EMAIL_PATTERN = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
const PHONE_PATTERN = /^[0-9][0-9 ()/.-]{2,24}$/;

export const CONTACT_FIELDS = ['firstName', 'lastName', 'email', 'phone'];

export class ContactValidator {
    /**
     * @param {Object<string, string>} messages checkout.tour.errors translations
     */
    constructor(messages) {
        this.messages = messages || {};
    }

    /**
     * @param {{ firstName: string, lastName: string, email: string, phone: string }} contact
     * @returns {Object<string, string>} field => message, empty when valid
     */
    validate(contact) {
        const errors = {};
        const value = (key) => String(contact[key] ?? '').trim();

        if (!value('firstName')) errors.firstName = this.messages.firstName;
        if (!value('lastName')) errors.lastName = this.messages.lastName;

        if (!value('email')) errors.email = this.messages.emailRequired;
        else if (!EMAIL_PATTERN.test(value('email'))) errors.email = this.messages.emailInvalid;

        if (!value('phone')) errors.phone = this.messages.phoneRequired;
        else if (!PHONE_PATTERN.test(value('phone'))) errors.phone = this.messages.phoneInvalid;

        return errors;
    }
}
