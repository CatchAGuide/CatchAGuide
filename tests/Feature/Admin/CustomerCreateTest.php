<?php

namespace Tests\Feature\Admin;

use App\Enums\GuideStatus;
use App\Models\Employee;
use App\Models\User;
use App\Services\Admin\CustomerCreator;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerCreateTest extends TestCase
{
    use DatabaseTransactions;

    private Employee $employee;

    private string $uniqueLast;

    protected function setUp(): void
    {
        parent::setUp();

        // Scheme-less APP_URL turns "/admin/..." into a bad host path and 404s in tests.
        URL::forceRootUrl('http://localhost');

        $this->uniqueLast = 'Create'.Str::random(8);
        $this->employee = Employee::factory()->create([
            'name' => 'Admin Tester',
            'email' => 'admin-customer-'.Str::random(6).'@example.com',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'account_type' => 'person',
            'role' => 'provider',
            'firstname' => 'Finn',
            'lastname' => $this->uniqueLast,
            'email' => null,
            'language' => 'de',
            'phone_country_code' => '+49',
            'information' => [
                'phone' => '170 1234567',
                'address' => 'Hafenstraße',
                'address_number' => '12',
                'postal' => '40213',
                'city' => 'Düsseldorf',
                'country' => 'Deutschland',
            ],
        ], $overrides);
    }

    private function latestCreatedPerson(): ?User
    {
        return User::query()
            ->where('firstname', 'Finn')
            ->where('lastname', $this->uniqueLast)
            ->latest('id')
            ->first();
    }

    public function test_create_page_requires_an_employee_and_defaults_to_provider(): void
    {
        $this->get(route('admin.customers.create'))->assertRedirect();

        $this->actingAs($this->employee, 'employees')
            ->get(route('admin.customers.create'))
            ->assertOk()
            ->assertSee(__('admin.customers.create_title'))
            ->assertSee('value="provider"', false)
            ->assertSee(__('admin.customers.role_provider'));
    }

    public function test_store_creates_a_provider_without_email(): void
    {
        $response = $this->actingAs($this->employee, 'employees')
            ->post(route('admin.customers.store'), $this->payload());

        $user = $this->latestCreatedPerson();

        $this->assertNotNull($user);
        $response->assertRedirect(route('admin.customers.edit', $user));
        $this->assertNull($user->email);
        $this->assertTrue($user->isProvider());
        $this->assertFalse($user->isVerifiedGuide());
        $this->assertSame('170 1234567', $user->information?->phone);
        $this->assertSame('Hafenstraße', $user->information?->address);
        $this->assertSame('12', $user->information?->address_number);
        $this->assertSame('40213', $user->information?->postal);
        $this->assertSame('Düsseldorf', $user->information?->city);
    }

    public function test_store_creates_a_company_provider(): void
    {
        $this->actingAs($this->employee, 'employees')
            ->post(route('admin.customers.store'), $this->payload([
                'account_type' => 'company',
                'firstname' => null,
                'lastname' => null,
                'company_name' => 'Fishcoast Lodge',
            ]))
            ->assertRedirect();

        $user = User::query()->where('firstname', 'Fishcoast Lodge')->latest('id')->first();

        $this->assertNotNull($user);
        $this->assertSame('', (string) $user->lastname);
        $this->assertSame('Fishcoast Lodge', $user->information?->company_name);
        $this->assertTrue($user->isProvider());
    }

    public function test_store_creates_a_verified_guide(): void
    {
        $this->actingAs($this->employee, 'employees')
            ->post(route('admin.customers.store'), $this->payload([
                'role' => 'guide',
                'email' => 'guide-create-'.Str::random(6).'@example.com',
            ]))
            ->assertRedirect();

        $user = $this->latestCreatedPerson();

        $this->assertNotNull($user);
        $this->assertFalse($user->isProvider());
        $this->assertTrue($user->isVerifiedGuide());
        $this->assertSame(GuideStatus::VERIFIED, $user->guide_status);
    }

    public function test_store_requires_address_and_phone(): void
    {
        $this->actingAs($this->employee, 'employees')
            ->from(route('admin.customers.create'))
            ->post(route('admin.customers.store'), $this->payload([
                'information' => [
                    'phone' => '',
                    'address' => '',
                    'address_number' => '',
                    'postal' => '',
                    'city' => '',
                ],
            ]))
            ->assertRedirect(route('admin.customers.create'))
            ->assertSessionHasErrors([
                'information.phone',
                'information.address',
                'information.address_number',
                'information.postal',
                'information.city',
            ]);
    }

    public function test_customer_creator_defaults_role_to_provider(): void
    {
        $user = app(CustomerCreator::class)->create($this->payload([
            'role' => 'not-a-role',
        ]));

        $this->assertTrue($user->isProvider());
        $this->assertFalse($user->isVerifiedGuide());
    }
}
