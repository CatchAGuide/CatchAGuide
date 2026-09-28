<?php

namespace App\Http\Requests;

use App\Models\Guiding;
use App\Rules\Recaptcha;
use App\Services\Checkout\TourCheckoutPricing;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Booking request submitted from the tour checkout (pages/modern-checkout).
 * Prices are never read from the client: only the guest count and extra positions are,
 * and TourCheckoutPricing re-quotes them on the server.
 */
class TourCheckoutRequest extends FormRequest
{
    private const PHONE_PATTERN = '/^[0-9][0-9 ()\/.-]{2,24}$/';

    private Guiding|false|null $guiding = null;

    private ?TourCheckoutPricing $pricing = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'guiding_id' => ['required', 'integer', function (string $attribute, mixed $value, Closure $fail): void {
                if ($this->guiding() === null) {
                    $fail(__('checkout.tour.errors.tour_unavailable'));
                }
            }],
            'persons' => ['required', 'integer', 'min:1', 'max:'.($this->pricing()?->maxGuests() ?? 1)],
            'selected_date' => ['required', 'date_format:Y-m-d', 'after:today'],
            'extras' => ['sometimes', 'array', 'max:50'],
            'extras.*' => ['integer', 'min:0', 'distinct'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'country_code' => ['required', 'string', Rule::in(array_keys((array) config('phone_country_codes', [])))],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_PATTERN],
            'g-recaptcha-response' => Recaptcha::production(invisible: true),
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $guiding = $this->guiding()) {
                    return;
                }

                if ($guiding->isDateBlocked((string) $this->input('selected_date'))) {
                    $validator->errors()->add('selected_date', __('checkout.tour.errors.date_unavailable'));
                }

                $known = array_column($this->pricing()->extras(), 'index');
                if (array_diff($this->extraIndexes(), $known) !== []) {
                    $validator->errors()->add('extras', __('checkout.tour.errors.extras_invalid'));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'selected_date.required' => __('checkout.tour.errors.date_required'),
            'selected_date.after' => __('checkout.tour.errors.date_unavailable'),
            'first_name.required' => __('checkout.tour.errors.first_name_required'),
            'last_name.required' => __('checkout.tour.errors.last_name_required'),
            'email.required' => __('checkout.tour.errors.email_required'),
            'email.email' => __('checkout.tour.errors.email_invalid'),
            'phone.required' => __('checkout.tour.errors.phone_required'),
            'phone.regex' => __('checkout.tour.errors.phone_invalid'),
        ];
    }

    public function guiding(): ?Guiding
    {
        if ($this->guiding === null) {
            $id = filter_var($this->input('guiding_id'), FILTER_VALIDATE_INT);
            // Same rule as the tour page: only published tours of verified guides are bookable.
            $this->guiding = ($id ? Guiding::publiclyVisible()->with('user')->find($id) : null) ?? false;
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
     * @return list<int>
     */
    public function extraIndexes(): array
    {
        return array_values(array_map('intval', (array) $this->input('extras', [])));
    }

    /**
     * @return array{first_name: string, last_name: string, email: string, country_code: string, phone: string}
     */
    public function contact(): array
    {
        return [
            'first_name' => trim((string) $this->validated('first_name')),
            'last_name' => trim((string) $this->validated('last_name')),
            'email' => trim((string) $this->validated('email')),
            'country_code' => (string) $this->validated('country_code'),
            'phone' => trim((string) $this->validated('phone')),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $this->only(['first_name', 'last_name', 'email', 'phone'])
        ));
    }
}
