<?php

namespace App\Http\Requests;

use App\Rules\Recaptcha;
use App\Services\Voucher\VoucherRequestService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Gift voucher request from the /guidings/voucher landing page. Destination comes from the
 * Google Places search (country / region / city hidden fields) or VoucherRequestService::OPEN
 * ("still open"). Same phone rule as the checkouts.
 */
class VoucherLandingRequest extends FormRequest
{
    private const PHONE_PATTERN = '/^[0-9][0-9 ()\/.-]{2,24}$/';

    private const COPY = 'guidings.voucher_landing.errors.';

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
            'country' => ['required', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', Rule::in([...array_map('strval', VoucherRequestService::PRESET_AMOUNTS), 'custom'])],
            'custom_amount' => ['exclude_unless:amount,custom', 'required', 'integer', 'between:'.VoucherRequestService::MIN_AMOUNT.','.VoucherRequestService::MAX_AMOUNT],
            'recipient' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:100'],
            'country_code' => ['required', 'string', Rule::in(array_keys((array) config('phone_country_codes', [])))],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_PATTERN],
            'email' => ['required', 'string', 'email', 'max:255'],
            'consent' => ['accepted'],
            'g-recaptcha-response' => Recaptcha::production(invisible: true),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $range = ['min' => VoucherRequestService::MIN_AMOUNT.' €', 'max' => VoucherRequestService::MAX_AMOUNT.' €'];

        return [
            'country.required' => __(self::COPY.'country'),
            'custom_amount.required' => __(self::COPY.'custom_amount'),
            'custom_amount.integer' => __(self::COPY.'custom_amount'),
            'custom_amount.between' => __(self::COPY.'custom_amount_range', $range),
            'name.required' => __(self::COPY.'name'),
            'phone.required' => __(self::COPY.'phone'),
            'phone.regex' => __(self::COPY.'phone_invalid'),
            'email.required' => __(self::COPY.'email'),
            'email.email' => __(self::COPY.'email_invalid'),
            'consent.accepted' => __(self::COPY.'consent'),
        ];
    }

    /**
     * Country, region and city of the request; null for "still open" or blank.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string}
     */
    public function place(): array
    {
        $value = fn (string $key): ?string => ($v = trim((string) $this->input($key))) === '' || $v === VoucherRequestService::OPEN ? null : $v;

        $country = $value('country');
        $region = $country !== null ? $value('region') : null;

        return [$country, $region, $region !== null ? $value('city') : null];
    }

    public function amount(): int
    {
        return $this->validated('amount') === 'custom'
            ? (int) $this->validated('custom_amount')
            : (int) $this->validated('amount');
    }

    /**
     * @return array{name: string, email: string, country_code: string, phone: string, recipient: ?string}
     */
    public function contact(): array
    {
        return [
            'name' => (string) $this->validated('name'),
            'email' => (string) $this->validated('email'),
            'country_code' => (string) $this->validated('country_code'),
            'phone' => (string) $this->validated('phone'),
            'recipient' => $this->validated('recipient') ?: null,
        ];
    }

    /** Back to the form, not the top of the page. */
    protected function getRedirectUrl(): string
    {
        return route('guidings.voucher').'#gutschein';
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $this->only(['country', 'region', 'city', 'custom_amount', 'recipient', 'name', 'email', 'phone'])
        ));
    }
}
