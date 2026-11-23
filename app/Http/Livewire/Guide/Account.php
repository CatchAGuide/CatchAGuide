<?php

namespace App\Http\Livewire\Guide;

use App\Enums\AccountVerificationStatus;
use App\Enums\GuideType;
use App\Services\Customer\CustomerAccountService;
use App\Services\Guide\Area\GuideAccountService;
use App\Services\Guide\Area\GuideOptimizationService;
use App\Support\CountryList;
use App\Support\GuideAreaNav;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

/**
 * Guide Area › Account & security (spec chapter 13, prototype board "Account & security"):
 * personal details, verification & billing (account type 2.4), password and cookie settings,
 * one card each with its own save. Shown on /profile/settings and /profile/password for guides
 * on the Guide Area; customers keep Customer\Account.
 */
class Account extends Component
{
    /** Select value of "Other" in the legal form select. */
    public const LEGAL_FORM_OTHER = 'other';

    /** @var array{firstname: string, lastname: string, language: string} */
    public array $personal = [];

    /** @var array<string, mixed> GuideAccountService::billing() */
    public array $billing = [];

    /** "CC|Form" from the grouped select, or "other". */
    public string $legalFormChoice = '';

    public string $legalFormOther = '';

    public bool $passwordOpen = false;

    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    public function mount(GuideAccountService $account, string $section = 'details'): void
    {
        $guide = auth()->user();
        $this->personal = $account->personal($guide);
        $this->billing = $account->billing($guide);
        $this->fillLegalFormChoice();
        $this->passwordOpen = $section === 'password';
    }

    public function savePersonal(GuideAccountService $account): void
    {
        $data = $this->validate($account->personalRules('personal.'), [], $this->attributeNames())['personal'];

        $account->savePersonal(auth()->user(), $data);
        $this->dispatch('guide-area-toast', message: __('guide_area.account.personal_saved'));
    }

    public function saveBilling(GuideAccountService $account, GuideOptimizationService $optimization): void
    {
        $this->billing['legal_form'] = $this->legalFormChoice === self::LEGAL_FORM_OTHER
            ? trim($this->legalFormOther)
            : (string) (explode('|', $this->legalFormChoice, 2)[1] ?? '');
        $this->billing['vat_not_registered'] = (bool) $this->billing['vat_not_registered'];

        $data = $this->validate(
            $account->billingRules($this->billing, 'billing.'),
            $account->billingMessages('billing.'),
            $this->attributeNames()
        )['billing'];

        $guide = auth()->user();
        $result = $account->saveBilling($guide, $data);

        $this->billing = $account->billing($guide->refresh());
        $this->fillLegalFormChoice();
        $optimization->forget($guide);

        $this->dispatch('guide-area-toast', message: __($result['review_requested'] ? 'guide_area.account.billing_saved_review' : 'guide_area.account.billing_saved'));
        $this->dispatch('guide-area-badges', badges: GuideAreaNav::badges($guide));
    }

    public function updatedBillingVatNotRegistered($value): void
    {
        if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
            $this->billing['vat_id'] = '';
            $this->resetValidation('billing.vat_id');
        }
    }

    public function changePassword(CustomerAccountService $account): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password:web'],
            'newPassword' => ['required', 'string', 'different:currentPassword', 'same:newPasswordConfirmation', Password::min((int) config('guide_area.customer.password_min', 8))],
            'newPasswordConfirmation' => ['required'],
        ], [], $this->attributeNames());

        $account->changePassword(auth()->user(), $this->newPassword);
        $this->reset('currentPassword', 'newPassword', 'newPasswordConfirmation', 'passwordOpen');
        $this->dispatch('guide-area-toast', message: __('guide_area.customer.account.password_saved'));
    }

    public function cancelPassword(): void
    {
        $this->reset('currentPassword', 'newPassword', 'newPasswordConfirmation', 'passwordOpen');
        $this->resetValidation(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
    }

    public function render()
    {
        $guide = auth()->user();
        $storedType = $guide->guide_type === GuideType::COMPANY ? GuideType::COMPANY : GuideType::PRIVATE;
        $status = $guide->account_verification_status ?? AccountVerificationStatus::Pending;

        return view('livewire.guide.account', [
            'user' => $guide,
            'storedType' => $storedType,
            'isCompany' => ($this->billing['account_type'] ?? $storedType) === GuideType::COMPANY,
            'status' => $status,
            'rejectionReason' => $status === AccountVerificationStatus::Rejected ? $guide->account_rejection_reason : null,
            'countries' => CountryList::options(),
            'countryCodes' => config('phone_country_codes', []),
            'legalFormGroups' => $this->legalFormGroups(),
            'passwordChangedAt' => $guide->password_changed_at,
            'minBirthday' => now()->subYears((int) config('guide_area.account.min_age', 18))->toDateString(),
        ]);
    }

    /** @return array<string, array{label: string, forms: array<string, string>}> grouped select options */
    private function legalFormGroups(): array
    {
        $groups = [];
        foreach ((array) config('guide_area.account.legal_forms', []) as $country => $forms) {
            $groups[$country] = [
                'label' => CountryList::name($country) ?? $country,
                'forms' => collect($forms)->mapWithKeys(fn (string $form) => [$country.'|'.$form => $form])->all(),
            ];
        }

        return $groups;
    }

    /** Picks the select entry for the stored legal form: the guide's own country first. */
    private function fillLegalFormChoice(): void
    {
        $stored = trim((string) ($this->billing['legal_form'] ?? ''));
        $this->legalFormChoice = '';
        $this->legalFormOther = '';
        if ($stored === '') {
            return;
        }

        $forms = (array) config('guide_area.account.legal_forms', []);
        $country = (string) ($this->billing['country'] ?? '');
        $countries = array_unique(array_filter([$country, ...array_keys($forms)]));
        foreach ($countries as $code) {
            foreach ((array) ($forms[$code] ?? []) as $form) {
                if (mb_strtolower($form) === mb_strtolower($stored)) {
                    $this->legalFormChoice = $code.'|'.$form;

                    return;
                }
            }
        }

        $this->legalFormChoice = self::LEGAL_FORM_OTHER;
        $this->legalFormOther = $stored;
    }

    /** @return array<string, string> */
    private function attributeNames(): array
    {
        $names = [];
        foreach (['firstname', 'lastname', 'language'] as $field) {
            $names['personal.'.$field] = __('guide_area.account.fields.'.$field);
        }
        foreach (['account_type', 'birthday', 'company_name', 'legal_form', 'address', 'address_number', 'postal', 'city', 'country', 'phone_country_code', 'phone', 'vat_id', 'vat_not_registered'] as $field) {
            $names['billing.'.$field] = __('guide_area.account.fields.'.$field);
        }
        foreach (['currentPassword', 'newPassword', 'newPasswordConfirmation'] as $field) {
            $names[$field] = __('guide_area.customer.account.fields.'.$field);
        }

        return $names;
    }
}
