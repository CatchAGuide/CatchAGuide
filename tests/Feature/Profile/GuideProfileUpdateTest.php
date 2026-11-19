<?php

namespace Tests\Feature\Profile;

use App\Enums\GuideStatus;
use App\Models\User;
use App\Models\UserInformation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class GuideProfileUpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
    }

    private function guide(): User
    {
        $guide = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
            'language' => 'en',
        ])->fresh();

        $guide->information->update([
            'about_me' => 'Old text',
            'company_name' => 'Original GmbH',
            'tax_number' => '12/345/67890',
            'request_as_guide' => 0,
        ]);

        return $guide->fresh(['information']);
    }

    private function validPayload(array $information = []): array
    {
        return [
            'information' => array_merge([
                'languages' => 'German, English',
                'about_me' => 'I guide on the Elbe.',
                'favorite_fish' => 'Zander',
                'fishing_start_year' => 1990,
            ], $information),
            'bar_allowed' => 1,
        ];
    }

    public function test_guide_can_update_the_profile_fields(): void
    {
        $guide = $this->guide();

        $this->actingAs($guide)
            ->put(route('profile.guide-profile.update'), $this->validPayload())
            ->assertRedirect(route('profile.guide-profile'));

        $info = $guide->fresh()->information;
        $this->assertSame('I guide on the Elbe.', $info->about_me);
        $this->assertSame('German, English', $info->languages);
        $this->assertSame('Zander', $info->favorite_fish);
        $this->assertSame(1990, (int) $info->fishing_start_year);
    }

    public function test_unlisted_information_fields_are_ignored(): void
    {
        $guide = $this->guide();
        $infoId = $guide->information->id;

        $this->actingAs($guide)
            ->put(route('profile.guide-profile.update'), $this->validPayload([
                'user_id' => 999999,
                'request_as_guide' => 1,
                'company_name' => 'Hijacked Ltd',
                'tax_number' => '00/000/00000',
            ]))
            ->assertRedirect(route('profile.guide-profile'));

        $info = UserInformation::find($infoId);
        $this->assertSame('I guide on the Elbe.', $info->about_me);
        $this->assertSame('Original GmbH', $info->company_name);
        $this->assertSame('12/345/67890', $info->tax_number);
        $this->assertEquals(0, (int) $info->request_as_guide);
    }
}
