<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\UserInformation;
use App\Services\Guide\GuideStatusService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates a customer/user from Admin › Customers (person or company contact).
 * Role defaults to provider (vacation host); admin may choose customer or guide.
 */
class CustomerCreator
{
    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_PROVIDER = 'provider';

    public const ROLE_GUIDE = 'guide';

    public function __construct(
        private readonly GuideStatusService $guideStatus,
    ) {}

    /**
     * @param  array{
     *     account_type: string,
     *     role?: string,
     *     firstname?: string|null,
     *     lastname?: string|null,
     *     company_name?: string|null,
     *     email?: string|null,
     *     language: string,
     *     phone_country_code: string,
     *     information: array<string, mixed>
     * }  $data
     */
    public function create(array $data, ?int $createdBy = null): User
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $isCompany = ($data['account_type'] ?? 'person') === 'company';
            $companyName = trim((string) ($data['company_name'] ?? ''));
            $firstname = $isCompany ? $companyName : trim((string) ($data['firstname'] ?? ''));
            $lastname = $isCompany ? '' : trim((string) ($data['lastname'] ?? ''));
            $phone = trim((string) ($data['information']['phone'] ?? ''));
            $phoneCode = trim((string) ($data['phone_country_code'] ?? '+49'));
            $role = $this->normalizeRole($data['role'] ?? self::ROLE_PROVIDER);

            $information = UserInformation::create([
                'company_name' => $isCompany ? $companyName : null,
                'phone' => $phone,
                'phone_country_code' => $phoneCode,
                'address' => trim((string) ($data['information']['address'] ?? '')),
                'address_number' => trim((string) ($data['information']['address_number'] ?? '')),
                'postal' => trim((string) ($data['information']['postal'] ?? '')),
                'city' => trim((string) ($data['information']['city'] ?? '')),
                'country' => trim((string) ($data['information']['country'] ?? '')) ?: null,
            ]);

            $user = User::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $data['email'] ?? null,
                'phone' => $phone,
                'phone_country_code' => $phoneCode,
                'language' => $data['language'] ?? 'de',
                'password' => Hash::make(Str::random(24)),
                'is_temp_password' => true,
                'is_active' => true,
                'is_guide' => null,
                'is_provider' => $role === self::ROLE_PROVIDER,
                'user_information_id' => $information->id,
            ]);

            if ($role === self::ROLE_GUIDE) {
                $user = $this->guideStatus->markVerified(
                    $user,
                    $createdBy,
                    'Admin created verified guide via Customers'
                );
            }

            return $user->load('information');
        });
    }

    private function normalizeRole(string $role): string
    {
        return in_array($role, [self::ROLE_CUSTOMER, self::ROLE_PROVIDER, self::ROLE_GUIDE], true)
            ? $role
            : self::ROLE_PROVIDER;
    }
}
