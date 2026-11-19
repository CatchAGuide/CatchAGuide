<?php

namespace App\Http\Livewire\Customer;

use App\Services\Customer\CustomerAccountService;
use App\Services\Media\GuideProfilePhotoUploadService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Customer area › Account (/profile/settings = Personal details, /profile/password = Password
 * & security, plus the cookie settings). One page with three tabs, switched in the browser so
 * the cookie panel's script keeps working. Guides open it from the Guide Area's
 * "Account & security".
 */
class Account extends Component
{
    use WithFileUploads;

    public const TABS = ['details', 'password', 'privacy'];

    public string $tab = 'details';

    public string $firstname = '';
    public string $lastname = '';
    public string $companyName = '';
    public string $phoneCountryCode = '+49';
    public string $phone = '';
    public string $language = 'de';
    public string $birthday = '';
    public string $address = '';
    public string $addressNumber = '';
    public string $postal = '';
    public string $city = '';
    public string $taxId = '';

    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';

    public $photo = null;

    public function mount(CustomerAccountService $account, string $tab = 'details'): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'details';
        $this->fill($account->state(auth()->user()));
    }

    public function saveDetails(CustomerAccountService $account): void
    {
        $user = auth()->user();
        $data = $this->validate([
            'firstname' => [Rule::requiredIf(! $user->is_company), 'nullable', 'string', 'max:100'],
            'lastname' => [Rule::requiredIf(! $user->is_company), 'nullable', 'string', 'max:100'],
            'companyName' => [Rule::requiredIf((bool) $user->is_company), 'nullable', 'string', 'max:150'],
            'phoneCountryCode' => ['required', 'string', Rule::in(array_keys(config('phone_country_codes', [])))],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9 +()\/-]{4,}$/'],
            'language' => ['required', Rule::in(['en', 'de'])],
            'birthday' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:150'],
            'addressNumber' => ['nullable', 'string', 'max:20'],
            'postal' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
            'taxId' => ['nullable', 'string', 'max:50'],
        ], [], $this->attributeNames());

        if (! $user->canViewGuideTools()) {
            unset($data['taxId']);
        }

        $account->saveDetails($user, $data);
        $this->dispatch('guide-area-toast', message: __('guide_area.customer.account.saved'));
    }

    public function changePassword(CustomerAccountService $account): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password:web'],
            'newPassword' => ['required', 'string', 'different:currentPassword', 'same:newPasswordConfirmation', Password::min((int) config('guide_area.customer.password_min', 8))],
            'newPasswordConfirmation' => ['required'],
        ], [], $this->attributeNames());

        $account->changePassword(auth()->user(), $this->newPassword);
        $this->reset('currentPassword', 'newPassword', 'newPasswordConfirmation');
        $this->dispatch('guide-area-toast', message: __('guide_area.customer.account.password_saved'));
    }

    public function updatedPhoto(GuideProfilePhotoUploadService $photos): void
    {
        $this->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('guide_area.photo.max_kb', 8192)],
        ], [], $this->attributeNames());

        $photos->store(auth()->user(), $this->photo);
        $this->reset('photo');
        $this->dispatch('guide-area-toast', message: __('guide_area.profile.photo_saved'));
    }

    public function removePhoto(GuideProfilePhotoUploadService $photos): void
    {
        $photos->remove(auth()->user());
        $this->dispatch('guide-area-toast', message: __('guide_area.profile.photo_removed'));
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.customer.account', [
            'user' => $user,
            'tabs' => self::TABS,
            'photoUrl' => filled($user->profil_image) ? guide_profile_photo_url($user) : null,
            'countryCodes' => config('phone_country_codes', []),
            'showTaxId' => $user->canViewGuideTools(),
            'tabUrls' => [
                'details' => route('profile.settings'),
                'password' => route('profile.password'),
                'privacy' => route('profile.settings', ['tab' => 'privacy']),
            ],
        ]);
    }

    /** @return array<string, string> */
    private function attributeNames(): array
    {
        return collect(['firstname', 'lastname', 'companyName', 'phoneCountryCode', 'phone', 'language', 'birthday', 'address', 'addressNumber', 'postal', 'city', 'taxId', 'currentPassword', 'newPassword', 'newPasswordConfirmation', 'photo'])
            ->mapWithKeys(fn (string $f) => [$f => __('guide_area.customer.account.fields.'.$f)])
            ->all();
    }
}
