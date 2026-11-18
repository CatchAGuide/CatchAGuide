<?php

namespace App\Services\Customer;

use App\Models\User;
use App\Models\UserInformation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Personal details and password of /profile/settings and /profile/password (customer pages in
 * the Guide Area design). Same fields the old account form saved; the email stays read-only.
 */
class CustomerAccountService
{
    /** Address fields on user_information. */
    private const INFORMATION_FIELDS = ['birthday', 'address', 'address_number', 'postal', 'city'];

    /** @return array<string, string> form state for the Account page */
    public function state(User $user): array
    {
        $info = $user->information;

        return [
            'firstname' => (string) $user->firstname,
            'lastname' => (string) $user->lastname,
            'companyName' => (string) $user->company_name,
            'phoneCountryCode' => (string) ($user->phone_country_code ?: '+49'),
            'phone' => (string) $user->phone,
            'language' => in_array($user->language, ['en', 'de'], true) ? $user->language : app()->getLocale(),
            'birthday' => $info?->birthday ? substr((string) $info->birthday, 0, 10) : '',
            'address' => (string) ($info?->address ?? ''),
            'addressNumber' => (string) ($info?->address_number ?? ''),
            'postal' => (string) ($info?->postal ?? ''),
            'city' => (string) ($info?->city ?? ''),
            'taxId' => (string) ($user->tax_id ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $data  validated Account form fields
     */
    public function saveDetails(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $information = [
                'birthday' => ($data['birthday'] ?? '') ?: null,
                'address' => $data['address'] ?? null,
                'address_number' => $data['addressNumber'] ?? null,
                'postal' => $data['postal'] ?? null,
                'city' => $data['city'] ?? null,
            ];

            // user_information is linked from users.user_information_id; create the row once
            // for accounts that never had one (the old form failed on those).
            $info = $user->information;
            if ($info) {
                $info->update(array_intersect_key($information, array_flip(self::INFORMATION_FIELDS)));
            } else {
                $info = UserInformation::create($information + ['user_id' => $user->id]);
                $user->user_information_id = $info->id;
            }

            if ($user->is_company) {
                $user->company_name = $data['companyName'] ?? $user->company_name;
            } else {
                $user->firstname = $data['firstname'];
                $user->lastname = $data['lastname'];
            }

            $user->phone = $data['phone'];
            $user->phone_country_code = $data['phoneCountryCode'];
            $user->language = $data['language'];
            if (array_key_exists('taxId', $data)) {
                $user->tax_id = ($data['taxId'] ?? '') ?: null;
            }

            $user->save();
        });
    }

    public function changePassword(User $user, string $newPassword): void
    {
        $user->password = Hash::make($newPassword);
        $user->save();
    }
}
