<?php

namespace Tests\Feature\Profile;

use App\Enums\GuideType;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuideOnboardingInformationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
        Mail::fake();
        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        ]);
    }

    private function applicant(): User
    {
        $user = User::factory()->create(['is_guide' => 0, 'guide_status' => null, 'language' => 'en'])->fresh();
        $user->information->update([
            'about_me' => 'Original about me',
            'trade_register_number' => 'HRB 1',
            'company_profile' => ['crew' => 2],
        ]);

        return $user->fresh(['information']);
    }

    private function payload(array $information = []): array
    {
        return [
            'guide_type' => GuideType::PRIVATE,
            'information' => array_merge([
                'address' => 'Hafenstraße',
                'address_number' => '12',
                'postal' => '20457',
                'city' => 'Hamburg',
                'country' => 'DE',
                'phone' => '151 2345678',
            ], $information),
            'lawcard' => 1,
            'lawcard_nature' => 1,
            'lawcard_truthful' => 1,
        ];
    }

    public function test_verification_fields_are_saved_and_others_ignored(): void
    {
        $user = $this->applicant();

        $this->actingAs($user)
            ->post(route('guide.onboarding.store'), $this->payload([
                'about_me' => 'Injected',
                'trade_register_number' => 'HRB 999',
                'contact_position' => 'CEO',
                'fishing_start_year' => 1900,
            ]))
            ->assertSessionHasNoErrors();

        $info = $user->fresh()->information;
        $this->assertSame('Hafenstraße', $info->address);
        $this->assertSame('Hamburg', $info->city);
        $this->assertSame('151 2345678', $info->phone);
        $this->assertSame('Original about me', $info->about_me);
        $this->assertSame('HRB 1', $info->trade_register_number);
        $this->assertNull($info->contact_position);
        $this->assertNull($info->fishing_start_year);
        $this->assertSame(['crew' => 2], $info->company_profile);
        $this->assertEquals(1, (int) $info->request_as_guide);
    }
}
