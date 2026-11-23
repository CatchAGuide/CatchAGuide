<?php

namespace App\Services\Guide\Area;

use App\Enums\AccountVerificationStatus;
use App\Mail\Guide\GuideAccountVerificationMail;
use App\Mail\Guide\GuideBillingChangedMail;
use App\Mail\Guide\GuideBillingReviewAdminMail;
use App\Mail\Guide\GuideCertificateSubmittedAdminMail;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Support\Facades\Mail;

/**
 * Billing verification of a guide (spec 2.4 / 13.4). A change to the legal or billing data puts it
 * back to pending (tours stay online), mails the guide a security notice (N-33) and gives ops a
 * review task (N-34). Admin › Guides › Billing verification approves or rejects it (N-35).
 */
class GuideAccountVerificationService
{
    public const NOTIFICATION_TYPE = 'guide_billing_review';

    public function __construct(private readonly AdminNotificationService $adminNotifications) {}

    /**
     * Back to pending after the guide changed their billing data. Ops is told unless a review
     * was already waiting.
     */
    public function requestReview(User $guide, bool $typeChanged): void
    {
        $wasPending = $guide->account_verification_status === AccountVerificationStatus::Pending;

        $guide->forceFill([
            'account_verification_status' => AccountVerificationStatus::Pending,
            'account_verified_at' => null,
            'account_verified_by' => null,
            'account_rejection_reason' => null,
        ])->save();

        Mail::to($guide)->queue((new GuideBillingChangedMail($guide, $typeChanged))->locale($this->locale($guide)));

        if ($wasPending) {
            return;
        }

        $this->adminNotifications->notify(
            self::NOTIFICATION_TYPE,
            __('guide_area.account.admin.notification_title', ['name' => $this->name($guide)], 'en'),
            $typeChanged ? __('guide_area.account.admin.notification_type_changed', [], 'en') : null,
            ['link' => route('admin.guide-billing.index'), 'meta' => ['user_id' => $guide->id], 'created_by' => null]
        );

        $recipients = GuideCertificateSubmittedAdminMail::recipients();
        Mail::to(array_shift($recipients))->cc($recipients)->queue(
            (new GuideBillingReviewAdminMail($guide, $typeChanged))->locale('de')
        );
    }

    public function approve(User $guide, int $employeeId): void
    {
        $guide->forceFill([
            'account_verification_status' => AccountVerificationStatus::Verified,
            'account_verified_at' => now(),
            'account_verified_by' => $employeeId,
            'account_rejection_reason' => null,
        ])->save();

        Mail::to($guide)->queue((new GuideAccountVerificationMail($guide))->locale($this->locale($guide)));
    }

    public function reject(User $guide, int $employeeId, string $reason): void
    {
        $guide->forceFill([
            'account_verification_status' => AccountVerificationStatus::Rejected,
            'account_verified_at' => null,
            'account_verified_by' => $employeeId,
            'account_rejection_reason' => trim($reason),
        ])->save();

        Mail::to($guide)->queue((new GuideAccountVerificationMail($guide))->locale($this->locale($guide)));
    }

    private function name(User $guide): string
    {
        return trim($guide->firstname.' '.$guide->lastname) ?: (string) $guide->email;
    }

    private function locale(User $user): string
    {
        return in_array($user->language, ['de', 'en'], true) ? $user->language : 'de';
    }
}
