<?php

namespace App\Services\Guide\Area;

use App\Enums\GuideType;
use App\Models\User;
use App\Models\UserInformation;
use App\Rules\VatId;
use App\Support\CountryList;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Guide Area › Account & security (spec chapter 13): personal details and the legal / billing
 * data of the account type (2.4). Used by the page (Livewire Guide\Account) and by
 * /api/guide/account*. The data lives where onboarding puts it: users.guide_type, phone and
 * tax_id (VAT ID), user_information address / country / birthday / company_name / legal_form.
 */
class GuideAccountService
{
    /** Billing fields whose change needs a new verification (the phone number doesn't). */
    private const VERIFIED_FIELDS = ['account_type', 'birthday', 'company_name', 'legal_form', 'address', 'address_number', 'postal', 'city', 'country', 'vat_id', 'vat_not_registered'];

    public function __construct(private readonly GuideAccountVerificationService $verification) {}

    /** @return array{firstname: string, lastname: string, language: string} */
    public function personal(User $guide): array
    {
        return [
            'firstname' => (string) $guide->firstname,
            'lastname' => (string) $guide->lastname,
            'language' => in_array($guide->language, ['en', 'de'], true) ? $guide->language : app()->getLocale(),
        ];
    }

    /**
     * Stored billing data. Values of the other account type stay stored (and are returned) for
     * 30 days after a switch, so switching back restores them.
     *
     * @return array<string, mixed>
     */
    public function billing(User $guide): array
    {
        $info = $guide->information;

        return [
            'account_type' => $guide->guide_type === GuideType::COMPANY ? GuideType::COMPANY : GuideType::PRIVATE,
            'birthday' => $info?->birthday?->toDateString() ?? '',
            'company_name' => (string) ($info?->company_name ?? ''),
            'legal_form' => (string) ($info?->legal_form ?? ''),
            'address' => (string) ($info?->address ?? ''),
            'address_number' => (string) ($info?->address_number ?? ''),
            'postal' => (string) ($info?->postal ?? ''),
            'city' => (string) ($info?->city ?? ''),
            'country' => CountryList::normalize($info?->country) ?? '',
            'phone_country_code' => (string) ($guide->phone_country_code ?: '+49'),
            'phone' => (string) $guide->phone,
            'vat_id' => (string) ($guide->tax_id ?? ''),
            'vat_not_registered' => (bool) ($info?->vat_not_registered ?? false),
        ];
    }

    /** @return array<string, array<int, mixed>> */
    public function personalRules(string $prefix = ''): array
    {
        return [
            $prefix.'firstname' => ['required', 'string', 'max:100'],
            $prefix.'lastname' => ['required', 'string', 'max:100'],
            $prefix.'language' => ['required', Rule::in(['en', 'de'])],
        ];
    }

    /**
     * @param  array<string, mixed>  $input  billing input (to pick the rules for its account type)
     * @return array<string, array<int, mixed>>
     */
    public function billingRules(array $input, string $prefix = ''): array
    {
        $company = ($input['account_type'] ?? null) === GuideType::COMPANY;
        $adultOn = now()->subYears((int) config('guide_area.account.min_age', 18))->toDateString();
        $vatNotRegistered = filter_var($input['vat_not_registered'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return [
            $prefix.'account_type' => ['required', Rule::in(GuideType::all())],
            // A company keeps its old date of birth for 30 days but doesn't send or save it.
            $prefix.'birthday' => $company ? ['nullable'] : ['required', 'date', 'after:1900-01-01', 'before_or_equal:'.$adultOn],
            $prefix.'company_name' => $company ? ['required', 'string', 'max:150'] : ['nullable', 'string', 'max:150'],
            $prefix.'legal_form' => $company ? ['required', 'string', 'max:100'] : ['nullable', 'string', 'max:100'],
            $prefix.'address' => ['required', 'string', 'max:150'],
            $prefix.'address_number' => ['required', 'string', 'max:20'],
            $prefix.'postal' => ['required', 'string', 'max:20'],
            $prefix.'city' => ['required', 'string', 'max:100'],
            $prefix.'country' => ['required', 'string', Rule::in(CountryList::codes())],
            $prefix.'phone_country_code' => ['required', 'string', Rule::in(array_keys(config('phone_country_codes', [])))],
            $prefix.'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9 +()\/-]{4,}$/'],
            $prefix.'vat_not_registered' => ['boolean'],
            $prefix.'vat_id' => $vatNotRegistered
                ? ['prohibited']
                : ['nullable', 'string', 'max:30', new VatId(strtoupper((string) ($input['country'] ?? '')))],
        ];
    }

    /** @return array<string, string> */
    public function billingMessages(string $prefix = ''): array
    {
        return [
            $prefix.'birthday.before_or_equal' => __('guide_area.account.validation.min_age', ['age' => (int) config('guide_area.account.min_age', 18)]),
            $prefix.'vat_id.prohibited' => __('guide_area.account.validation.vat_or_not_registered'),
        ];
    }

    /** @param  array{firstname: string, lastname: string, language: string}  $data  validated */
    public function savePersonal(User $guide, array $data): void
    {
        $guide->firstname = trim($data['firstname']);
        $guide->lastname = trim($data['lastname']);
        $guide->language = $data['language'];
        $guide->save();
    }

    /**
     * Saves validated billing data. A change of the account type or of any verified field puts
     * the verification back to pending and notifies (N-33, N-34); the tours stay online.
     *
     * @param  array<string, mixed>  $data  validated billing fields
     * @return array{type_changed: bool, review_requested: bool}
     */
    public function saveBilling(User $guide, array $data): array
    {
        $result = DB::transaction(function () use ($guide, $data) {
            $before = $this->billing($guide);
            $company = $data['account_type'] === GuideType::COMPANY;
            $vatNotRegistered = (bool) ($data['vat_not_registered'] ?? false);

            $information = [
                'address' => trim($data['address']),
                'address_number' => trim($data['address_number']),
                'postal' => trim($data['postal']),
                'city' => trim($data['city']),
                'country' => strtoupper($data['country']),
                'vat_not_registered' => $vatNotRegistered,
            ];
            // Only the current type's own fields are written; the other type's stay as they were.
            if ($company) {
                $information['company_name'] = trim($data['company_name']);
                $information['legal_form'] = trim($data['legal_form']);
            } else {
                $information['birthday'] = $data['birthday'];
            }

            $info = $guide->information;
            if ($info) {
                $info->update($information);
            } else {
                $info = UserInformation::create($information + ['user_id' => $guide->id]);
                $guide->user_information_id = $info->id;
            }

            $guide->guide_type = $data['account_type'];
            $guide->phone = trim($data['phone']);
            $guide->phone_country_code = $data['phone_country_code'];
            $guide->tax_id = $vatNotRegistered ? null : (VatId::normalize($data['vat_id'] ?? '') ?: null);

            $typeChanged = $before['account_type'] !== $data['account_type'];
            if ($typeChanged) {
                $guide->account_type_changed_at = now();
            }
            $guide->save();
            $guide->setRelation('information', $info->fresh());

            return [
                'type_changed' => $typeChanged,
                'review_requested' => $this->verifiedFieldsChanged($before, $this->billing($guide)),
            ];
        });

        if ($result['review_requested']) {
            $this->verification->requestReview($guide, $result['type_changed']);
        }

        return $result;
    }

    /**
     * Clears the other account type's data once the guide has kept the new type for the
     * retention period (spec 13.6). Run daily.
     */
    public function purgeRetainedTypeData(): int
    {
        $cutoff = now()->subDays((int) config('guide_area.account.retain_other_type_days', 30));
        $count = 0;

        User::query()
            ->whereNotNull('account_type_changed_at')
            ->where('account_type_changed_at', '<=', $cutoff)
            ->with('information')
            ->chunkById(100, function ($guides) use (&$count) {
                foreach ($guides as $guide) {
                    $guide->information?->update($guide->guide_type === GuideType::COMPANY
                        ? ['birthday' => null]
                        : ['company_name' => null, 'legal_form' => null]);
                    $guide->forceFill(['account_type_changed_at' => null])->save();
                    $count++;
                }
            });

        return $count;
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function verifiedFieldsChanged(array $before, array $after): bool
    {
        if ($before['account_type'] !== $after['account_type']) {
            return true;
        }

        $ignored = $after['account_type'] === GuideType::COMPANY ? ['birthday'] : ['company_name', 'legal_form'];
        foreach (array_diff(self::VERIFIED_FIELDS, $ignored) as $field) {
            $old = $field === 'vat_id' ? VatId::normalize($before[$field]) : trim((string) $before[$field]);
            $new = $field === 'vat_id' ? VatId::normalize($after[$field]) : trim((string) $after[$field]);
            if ($old !== $new) {
                return true;
            }
        }

        return false;
    }
}
