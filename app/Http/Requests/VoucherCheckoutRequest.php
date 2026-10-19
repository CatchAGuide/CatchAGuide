<?php

namespace App\Http\Requests;

use App\Models\Guiding;
use App\Rules\Recaptcha;
use App\Services\Checkout\TourCheckoutPricing;
use App\Services\Checkout\TourCheckoutQuote;
use App\Services\Voucher\VoucherTourFinder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Gift voucher order from the voucher checkout (pages/voucher-checkout) for the tour in the
 * route's {slug}. Same contact rules as TourCheckoutRequest, no date, plus the voucher fields.
 * The price is never read from the client: TourCheckoutPricing re-quotes the guest count.
 */
class VoucherCheckoutRequest extends FormRequest
{
    private const PHONE_PATTERN = '/^[0-9][0-9 ()\/.-]{2,24}$/';

    private Guiding|false|null $guiding = null;

    private ?TourCheckoutPricing $pricing = null;

    public function authorize(): bool
    {
        // Unknown, unpublished or voucher-disabled tour: same 404 as the checkout page.
        abort_if($this->guiding() === null, 404);

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'persons' => ['required', 'integer', 'min:1', 'max:'.($this->pricing()?->maxGuests() ?? 1)],
            'recipient_names' => ['required', 'string', 'max:100'],
            'sender_name' => ['nullable', 'string', 'max:100'],
            'personal_message' => ['nullable', 'string', 'max:200'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'country_code' => ['required', 'string', Rule::in(array_keys((array) config('phone_country_codes', [])))],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_PATTERN],
            'g-recaptcha-response' => Recaptcha::production(invisible: true),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipient_names.required' => __('checkout.voucher.errors.recipients_required'),
            'recipient_names.max' => __('checkout.voucher.errors.recipients_too_long'),
            'sender_name.max' => __('checkout.voucher.errors.sender_too_long'),
            'personal_message.max' => __('checkout.voucher.errors.message_too_long'),
            'first_name.required' => __('checkout.tour.errors.first_name_required'),
            'last_name.required' => __('checkout.tour.errors.last_name_required'),
            'email.required' => __('checkout.tour.errors.email_required'),
            'email.email' => __('checkout.tour.errors.email_invalid'),
            'phone.required' => __('checkout.tour.errors.phone_required'),
            'phone.regex' => __('checkout.tour.errors.phone_invalid'),
        ];
    }

    /**
     * The tour from the route, only while it's published and sold as a voucher.
     */
    public function guiding(): ?Guiding
    {
        if ($this->guiding === null) {
            $this->guiding = app(VoucherTourFinder::class)->findBySlug((string) $this->route('slug')) ?? false;
        }

        return $this->guiding ?: null;
    }

    public function pricing(): ?TourCheckoutPricing
    {
        if ($this->pricing === null && $guiding = $this->guiding()) {
            $this->pricing = TourCheckoutPricing::for($guiding);
        }

        return $this->pricing;
    }

    /**
     * Voucher price = the regular tour price for the guest count (no extras on vouchers).
     */
    public function quote(): TourCheckoutQuote
    {
        return $this->pricing()->quote((int) $this->validated('persons'), []);
    }

    /**
     * @return array{recipient_names: string, sender_name: ?string, personal_message: ?string}
     */
    public function voucher(): array
    {
        return [
            'recipient_names' => (string) $this->validated('recipient_names'),
            'sender_name' => $this->validated('sender_name') ?: null,
            'personal_message' => $this->validated('personal_message') ?: null,
        ];
    }

    /**
     * @return array{first_name: string, last_name: string, email: string, country_code: string, phone: string}
     */
    public function contact(): array
    {
        return [
            'first_name' => (string) $this->validated('first_name'),
            'last_name' => (string) $this->validated('last_name'),
            'email' => (string) $this->validated('email'),
            'country_code' => (string) $this->validated('country_code'),
            'phone' => (string) $this->validated('phone'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $this->only(['recipient_names', 'sender_name', 'personal_message', 'first_name', 'last_name', 'email', 'phone'])
        ));
    }
}
