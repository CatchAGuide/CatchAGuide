<?php

namespace Tests\Unit\Guide;

use App\Enums\GuideStatus;
use App\Models\GuideRequest;
use App\Models\User;
use App\Models\UserInformation;
use App\Services\Guide\GuideStatusService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * DB-backed checks for guide status transitions used by admin/onboarding.
 * Uses transactions so existing data is not left dirty.
 */
class GuideStatusServiceTest extends TestCase
{
    use DatabaseTransactions;

    private GuideStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(GuideStatusService::class);
    }

    private function makeUser(array $attrs = []): User
    {
        $info = UserInformation::create([
            'request_as_guide' => false,
            'country' => 'DE',
        ]);

        return User::create(array_merge([
            'firstname' => 'Test',
            'lastname' => 'Guide',
            'email' => 'guide-status-' . uniqid('', true) . '@example.test',
            'password' => bcrypt('password'),
            'user_information_id' => $info->id,
            'is_guide' => null,
            'guide_status' => null,
        ], $attrs));
    }

    public function test_mark_pending_sets_legacy_is_guide_to_zero_and_keeps_pending_flow(): void
    {
        $user = $this->makeUser();

        $user = $this->service->markPending($user, $user->id, 'Application submitted');

        $this->assertSame(GuideStatus::PENDING, $user->guide_status);
        $this->assertTrue(in_array($user->is_guide, [0, '0', false], true) || (int) $user->is_guide === 0);
        $this->assertTrue($user->isPendingGuide());
        $this->assertTrue($user->canAccessGuideDashboard());
        $this->assertFalse($user->canApplyAsGuide());
        $this->assertTrue((bool) $user->information->fresh()->request_as_guide);
    }

    public function test_mark_verified_sets_is_guide_one(): void
    {
        $user = $this->makeUser(['is_guide' => 0, 'guide_status' => GuideStatus::PENDING]);

        $user = $this->service->markVerified($user, $user->id, 'Approved');

        $this->assertSame(GuideStatus::VERIFIED, $user->guide_status);
        $this->assertTrue($user->isVerifiedGuide());
        $this->assertTrue($user->canPublishGuidings());
        $this->assertFalse($user->canApplyAsGuide());
        $this->assertNotNull($user->guide_verified_at);
        $this->assertFalse((bool) $user->information->fresh()->request_as_guide);
    }

    public function test_mark_rejected_allows_reapply(): void
    {
        $user = $this->makeUser(['is_guide' => 0, 'guide_status' => GuideStatus::PENDING]);

        $user = $this->service->markRejected($user, $user->id, 'Incomplete docs');

        $this->assertSame(GuideStatus::REJECTED, $user->guide_status);
        $this->assertTrue($user->isRejectedGuide());
        $this->assertTrue($user->canApplyAsGuide());
        $this->assertFalse($user->canAccessGuideDashboard());
        $this->assertTrue($user->canViewGuideTools());
    }

    public function test_admin_deactivation_converts_verified_guide_to_normal_user_not_pending(): void
    {
        $user = $this->makeUser([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
            'guide_verified_at' => now(),
        ]);
        $user->information->update(['request_as_guide' => false]);

        $user = $this->service->markAsCustomer($user, $user->id, 'Admin deactivated guide status');

        $this->assertNull($user->guide_status);
        $this->assertNull($user->is_guide);
        $this->assertNull($user->guide_verified_at);
        $this->assertFalse($user->isPendingGuide(), 'Deactivation must not leave user pending');
        $this->assertFalse($user->isVerifiedGuide());
        $this->assertTrue($user->canApplyAsGuide());
        $this->assertFalse($user->canAccessGuideDashboard());
        $this->assertFalse((bool) $user->information->fresh()->request_as_guide);

        $this->assertDatabaseHas('guide_status_log', [
            'user_id' => $user->id,
            'to_status' => 'customer',
            'reason' => 'Admin deactivated guide status',
        ]);
    }

    public function test_admin_deactivation_of_pending_clears_pending_requests(): void
    {
        $user = $this->makeUser([
            'is_guide' => 0,
            'guide_status' => GuideStatus::PENDING,
            'guide_submitted_at' => now(),
        ]);
        $user->information->update(['request_as_guide' => true]);

        $request = GuideRequest::create([
            'user_id' => $user->id,
            'submitted_at' => now(),
            'decision' => 'pending',
        ]);

        $user = $this->service->markAsCustomer($user, $user->id, 'Admin deactivated guide status');

        $this->assertNull($user->guide_status);
        $this->assertNull($user->is_guide);
        $this->assertFalse($user->isPendingGuide());
        $this->assertTrue($user->canApplyAsGuide());

        $this->assertSame('rejected', $request->fresh()->decision);
        $this->assertFalse((bool) $user->information->fresh()->request_as_guide);
    }

    public function test_customer_with_is_guide_zero_can_still_be_activated_to_verified(): void
    {
        $user = $this->makeUser([
            'is_guide' => 0,
            'guide_status' => null,
        ]);

        $this->assertTrue($user->hasInactiveGuideFlag());
        $this->assertTrue($user->canApplyAsGuide());

        $user = $this->service->markVerified($user, $user->id, 'Admin activated guide status');

        $this->assertTrue($user->isVerifiedGuide());
        $this->assertSame(GuideStatus::VERIFIED, $user->guide_status);
    }
}
