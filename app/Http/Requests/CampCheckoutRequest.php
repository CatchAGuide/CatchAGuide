<?php

namespace App\Http\Requests;

use App\Models\Camp;
use App\Rules\Recaptcha;
use App\Services\Checkout\Camp\BookableCampFinder;
use App\Services\Checkout\Camp\CampCheckoutPricing;
use App\Services\Checkout\Camp\CampCheckoutSelection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Availability request submitted from the camp checkout (pages/camp-checkout).
 * Prices are never read from the client: only the stay and the picked option ids are, and
 * CampCheckoutPricing re-quotes them on the server.
 */
class CampCheckoutRequest extends FormRequest
{
    private const PHONE_PATTERN = '/^[0-9][0-9 ()\/.-]{2,24}$/';

    private Camp|false|null $camp = null;

    private ?CampCheckoutPricing $pricing = null;

    /**
     * A camp that went offline (draft, deleted) while the page was open can't be requested.
     */
    public function authorize(): bool
    {
        return $this->camp() !== null;
    }

    protected function failedAuthorization(): never
    {
        // 410 so BookingClient shows the message instead of treating it as field errors.
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => __('checkout.camp.errors.camp_unavailable'),
        ], 410));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $pricing = $this->pricing();
        $ids = fn (array $options): array => array_keys($options);

        return [
            'arrival_date' => ['required', 'date_format:Y-m-d', 'after:today', 'before:'.now()->addYears(2)->toDateString()],
            'nights' => ['required', 'integer', 'min:1', 'max:'.CampCheckoutPricing::MAX_NIGHTS],
            'persons' => ['required', 'integer', 'min:1', 'max:'.CampCheckoutPricing::MAX_PERSONS],
            'accommodation_id' => [
                $pricing?->hasAccommodations() ? 'required' : 'nullable',
                'integer',
                Rule::in($ids($pricing?->accommodations() ?? [])),
            ],
            'rental_boat_id' => ['nullable', 'integer', Rule::in($ids($pricing?->boats() ?? []))],
            'guiding_id' => ['nullable', 'integer', Rule::in($ids($pricing?->tours() ?? []))],
            'special_offer_id' => ['nullable', 'integer', Rule::in($ids($pricing?->specials() ?? []))],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'country_code' => ['required', 'string', Rule::in(array_keys((array) config('phone_country_codes', [])))],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_PATTERN],
            'message' => ['nullable', 'string', 'max:2000'],
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
                if ($validator->errors()->isNotEmpty() || ! $pricing = $this->pricing()) {
                    return;
                }

                $minNights = $pricing->minNights($this->optionalId('accommodation_id'));
                if ((int) $this->input('nights') < $minNights) {
                    $validator->errors()->add('nights', __('checkout.camp.errors.min_nights', ['count' => $minNights]));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $selectionInvalid = __('checkout.camp.errors.selection_invalid');

        return [
            'arrival_date.required' => __('checkout.camp.errors.date_required'),
            'arrival_date.date_format' => __('checkout.camp.errors.date_required'),
            'arrival_date.after' => __('checkout.camp.errors.date_invalid'),
            'arrival_date.before' => __('checkout.camp.errors.date_invalid'),
            'accommodation_id.required' => __('checkout.camp.errors.accommodation_required'),
            'accommodation_id.in' => $selectionInvalid,
            'rental_boat_id.in' => $selectionInvalid,
            'guiding_id.in' => $selectionInvalid,
            'special_offer_id.in' => $selectionInvalid,
            'first_name.required' => __('checkout.tour.errors.first_name_required'),
            'last_name.required' => __('checkout.tour.errors.last_name_required'),
            'email.required' => __('checkout.tour.errors.email_required'),
            'email.email' => __('checkout.tour.errors.email_invalid'),
            'phone.required' => __('checkout.tour.errors.phone_required'),
            'phone.regex' => __('checkout.tour.errors.phone_invalid'),
        ];
    }

    /**
     * The camp from the route, or null when it isn't bookable (the controller answers 404).
     */
    public function camp(): ?Camp
    {
        if ($this->camp === null) {
            $this->camp = app(BookableCampFinder::class)->findBySlug((string) $this->route('slug')) ?? false;
        }

        return $this->camp ?: null;
    }

    public function pricing(): ?CampCheckoutPricing
    {
        if ($this->pricing === null && $camp = $this->camp()) {
            $this->pricing = CampCheckoutPricing::for($camp);
        }

        return $this->pricing;
    }

    public function selection(): CampCheckoutSelection
    {
        return new CampCheckoutSelection(
            nights: (int) $this->validated('nights'),
            persons: (int) $this->validated('persons'),
            accommodationId: $this->optionalId('accommodation_id'),
            rentalBoatId: $this->optionalId('rental_boat_id'),
            guidingId: $this->optionalId('guiding_id'),
            specialOfferId: $this->optionalId('special_offer_id'),
        );
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

    public function guestMessage(): string
    {
        return (string) ($this->validated('message') ?? '');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $this->only(['first_name', 'last_name', 'email', 'phone', 'message'])
        ));

        // "" from an empty <select> means "none".
        foreach (['accommodation_id', 'rental_boat_id', 'guiding_id', 'special_offer_id'] as $key) {
            if ($this->input($key) === '') {
                $this->merge([$key => null]);
            }
        }
    }

    private function optionalId(string $key): ?int
    {
        $value = filter_var($this->input($key), FILTER_VALIDATE_INT);

        return $value === false ? null : $value;
    }
}
