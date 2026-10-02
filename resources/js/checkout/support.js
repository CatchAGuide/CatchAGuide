/**
 * Small helpers shared by the checkout-style pages (tour checkout, reschedule, guide reject).
 */

/** Boot config rendered by the page as <script type="application/json" id="...">. */
export const readConfig = (elementId) => {
    try {
        return JSON.parse(document.getElementById(elementId)?.textContent || '{}');
    } catch (e) {
        return {};
    }
};

/** Laravel-style ":name" replacements. */
export const trans = (template, replacements = {}) => Object.entries(replacements)
    .reduce((text, [key, value]) => text.split(`:${key}`).join(String(value)), String(template ?? ''));

/** Laravel-style "one|many" pluralisation with :count. */
export const choice = (template, count, replacements = {}) => {
    const [one, many = one] = String(template ?? '').split('|');

    return trans(count === 1 ? one : many, { count, ...replacements });
};

export const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

/** Format a Y-m-d string without letting the visitor's timezone shift the day. */
export const formatIsoDate = (formatter, iso) => formatter.format(new Date(`${iso}T00:00:00Z`));
