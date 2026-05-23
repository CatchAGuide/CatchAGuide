<?php

namespace App\Services\Guide;

use App\Mail\Guide\GuideApplicationRejectedMail;
use App\Mail\Guide\GuideApprovedMail;
use App\Models\GuideRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GuideVerificationService
{
    public function __construct(
        protected GuideStatusService $guideStatusService,
    ) {}

    public function approve(GuideRequest $guideRequest, int $reviewerId, ?string $notes = null): User
    {
        return DB::transaction(function () use ($guideRequest, $reviewerId, $notes) {
            if ($guideRequest->decision !== 'pending') {
                throw new \RuntimeException('This request has already been reviewed.');
            }

            $guideRequest->update([
                'decision' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => $reviewerId,
                'internal_notes' => $notes,
                'version' => $guideRequest->version + 1,
            ]);

            $user = $this->guideStatusService->markVerified(
                $guideRequest->user,
                $reviewerId,
                'Approved via admin panel'
            );

            if ($user->information) {
                $user->information->update(['request_as_guide' => false]);
            }

            Mail::send(new GuideApprovedMail($user));

            return $user;
        });
    }

    public function reject(GuideRequest $guideRequest, int $reviewerId, ?string $reason = null, ?string $notes = null): User
    {
        return DB::transaction(function () use ($guideRequest, $reviewerId, $reason, $notes) {
            if ($guideRequest->decision !== 'pending') {
                throw new \RuntimeException('This request has already been reviewed.');
            }

            $guideRequest->update([
                'decision' => 'rejected',
                'reviewed_at' => now(),
                'reviewed_by' => $reviewerId,
                'rejection_reason' => $reason,
                'internal_notes' => $notes,
                'version' => $guideRequest->version + 1,
            ]);

            $user = $this->guideStatusService->markRejected(
                $guideRequest->user,
                $reviewerId,
                $reason ?? 'Rejected via admin panel'
            );

            try {
                Mail::send(new GuideApplicationRejectedMail($user, $reason));
            } catch (\Throwable $e) {
                Log::error('Guide application rejected email failed', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $user;
        });
    }

    public function approveUserLegacy(User $user, int $reviewerId): User
    {
        if ($user->isRejectedGuide()) {
            throw new \RuntimeException('This applicant was rejected. They must submit a new guide application before you can approve them.');
        }

        if ($user->isVerifiedGuide()) {
            throw new \RuntimeException('This user is already a verified guide.');
        }

        $pendingRequest = GuideRequest::query()
            ->where('user_id', $user->id)
            ->where('decision', 'pending')
            ->latest('submitted_at')
            ->first();

        if ($pendingRequest) {
            return $this->approve($pendingRequest, $reviewerId);
        }

        if (! $user->isPendingGuide()) {
            throw new \RuntimeException('No pending guide application found for this user.');
        }

        $user = $this->guideStatusService->markVerified($user, $reviewerId, 'Legacy approve action');

        if ($user->information) {
            $user->information->update(['request_as_guide' => false]);
        }

        Mail::send(new GuideApprovedMail($user));

        return $user;
    }
}
