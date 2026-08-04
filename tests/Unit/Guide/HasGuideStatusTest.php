<?php

namespace Tests\Unit\Guide;

use App\Enums\GuideStatus;
use App\Models\User;
use Tests\TestCase;

/**
 * Ensures guide-status helpers keep the expected product flow:
 * - verified / pending / rejected / customer
 * - is_guide 0 and null both mean non-guide (not pending)
 */
class HasGuideStatusTest extends TestCase
{
    private function user(array $attrs = []): User
    {
        return new User(array_merge([
            'is_guide' => null,
            'guide_status' => null,
        ], $attrs));
    }

    public function test_verified_guide_via_guide_status(): void
    {
        $user = $this->user([
            'guide_status' => GuideStatus::VERIFIED,
            'is_guide' => 1,
        ]);

        $this->assertTrue($user->isVerifiedGuide());
        $this->assertFalse($user->isPendingGuide());
        $this->assertFalse($user->isRejectedGuide());
        $this->assertTrue($user->canAccessGuideDashboard());
        $this->assertTrue($user->canPublishGuidings());
        $this->assertFalse($user->canApplyAsGuide());
        $this->assertTrue($user->hasActiveGuideFlag());
        $this->assertFalse($user->hasInactiveGuideFlag());
    }

    public function test_verified_guide_via_legacy_is_guide_one(): void
    {
        $user = $this->user([
            'guide_status' => null,
            'is_guide' => 1,
        ]);

        $this->assertTrue($user->isVerifiedGuide());
        $this->assertFalse($user->isPendingGuide());
        $this->assertTrue($user->canAccessGuideDashboard());
        $this->assertFalse($user->canApplyAsGuide());
    }

    public function test_pending_guide_requires_guide_status_pending(): void
    {
        $user = $this->user([
            'guide_status' => GuideStatus::PENDING,
            'is_guide' => 0,
        ]);

        $this->assertTrue($user->isPendingGuide());
        $this->assertFalse($user->isVerifiedGuide());
        $this->assertTrue($user->canAccessGuideDashboard());
        $this->assertFalse($user->canPublishGuidings());
        $this->assertFalse($user->canApplyAsGuide());
        $this->assertTrue($user->hasInactiveGuideFlag());
    }

    public function test_is_guide_zero_without_status_is_normal_user_not_pending(): void
    {
        $user = $this->user([
            'guide_status' => null,
            'is_guide' => 0,
        ]);

        $this->assertFalse($user->isPendingGuide(), 'is_guide=0 alone must not imply pending');
        $this->assertFalse($user->isVerifiedGuide());
        $this->assertFalse($user->canAccessGuideDashboard());
        $this->assertTrue($user->canApplyAsGuide());
        $this->assertTrue($user->hasInactiveGuideFlag());
        $this->assertFalse($user->hasGuideApplication());
    }

    public function test_is_guide_null_is_normal_user(): void
    {
        $user = $this->user([
            'guide_status' => null,
            'is_guide' => null,
        ]);

        $this->assertFalse($user->isPendingGuide());
        $this->assertFalse($user->isVerifiedGuide());
        $this->assertFalse($user->canAccessGuideDashboard());
        $this->assertTrue($user->canApplyAsGuide());
        $this->assertTrue($user->hasInactiveGuideFlag());
        $this->assertFalse($user->hasGuideApplication());
    }

    public function test_is_guide_string_zero_is_treated_like_inactive(): void
    {
        $user = $this->user([
            'guide_status' => null,
            'is_guide' => '0',
        ]);

        $this->assertTrue($user->hasInactiveGuideFlag());
        $this->assertFalse($user->isPendingGuide());
        $this->assertTrue($user->canApplyAsGuide());
    }

    public function test_rejected_guide_can_reapply_and_view_tools(): void
    {
        $user = $this->user([
            'guide_status' => GuideStatus::REJECTED,
            'is_guide' => 0,
        ]);

        $this->assertTrue($user->isRejectedGuide());
        $this->assertFalse($user->isPendingGuide());
        $this->assertFalse($user->canAccessGuideDashboard());
        $this->assertTrue($user->canViewGuideTools());
        $this->assertTrue($user->canApplyAsGuide());
        $this->assertTrue($user->hasGuideApplication());
    }

    public function test_pending_and_null_inactive_flags_do_not_break_each_other(): void
    {
        $pending = $this->user(['guide_status' => GuideStatus::PENDING, 'is_guide' => 0]);
        $customerZero = $this->user(['guide_status' => null, 'is_guide' => 0]);
        $customerNull = $this->user(['guide_status' => null, 'is_guide' => null]);

        $this->assertTrue($pending->isPendingGuide());
        $this->assertFalse($customerZero->isPendingGuide());
        $this->assertFalse($customerNull->isPendingGuide());

        $this->assertSame(
            $customerZero->hasInactiveGuideFlag(),
            $customerNull->hasInactiveGuideFlag()
        );
    }
}
