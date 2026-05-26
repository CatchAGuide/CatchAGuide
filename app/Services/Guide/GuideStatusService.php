<?php

namespace App\Services\Guide;

use App\Enums\GuideStatus;
use App\Models\GuideStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GuideStatusService
{
    public function transition(User $user, string $toStatus, ?int $changedBy = null, ?string $reason = null): User
    {
        if (! in_array($toStatus, GuideStatus::all(), true)) {
            throw new \InvalidArgumentException("Invalid guide status: {$toStatus}");
        }

        return DB::transaction(function () use ($user, $toStatus, $changedBy, $reason) {
            $fromStatus = $user->guide_status;

            GuideStatusLog::create([
                'user_id' => $user->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => $changedBy,
                'changed_at' => now(),
                'reason' => $reason,
            ]);

            $user->guide_status = $toStatus;
            $this->syncLegacyIsGuide($user, $toStatus);

            if ($toStatus === GuideStatus::PENDING && ! $user->guide_submitted_at) {
                $user->guide_submitted_at = now();
            }

            if ($toStatus === GuideStatus::VERIFIED) {
                $user->guide_verified_at = now();
            }

            $user->save();

            if ($user->information) {
                $user->information->update([
                    'request_as_guide' => $toStatus === GuideStatus::PENDING,
                ]);
            }

            if ($toStatus === GuideStatus::REJECTED) {
                $user->guide_verified_at = null;
            }

            return $user->fresh();
        });
    }

    public function markPending(User $user, ?int $changedBy = null, ?string $reason = 'Application submitted'): User
    {
        return $this->transition($user, GuideStatus::PENDING, $changedBy, $reason);
    }

    public function markVerified(User $user, ?int $changedBy = null, ?string $reason = 'Application approved'): User
    {
        return $this->transition($user, GuideStatus::VERIFIED, $changedBy, $reason);
    }

    public function markRejected(User $user, ?int $changedBy = null, ?string $reason = null): User
    {
        return $this->transition($user, GuideStatus::REJECTED, $changedBy, $reason);
    }

    public function syncFromLegacyIsGuide(User $user): void
    {
        if ($user->guide_status !== null) {
            return;
        }

        if ($user->is_guide === 1 || $user->is_guide === true || $user->is_guide === '1') {
            $user->guide_status = GuideStatus::VERIFIED;
            if (! $user->guide_verified_at) {
                $user->guide_verified_at = $user->updated_at;
            }
        } elseif ($user->is_guide === 0 || $user->is_guide === '0') {
            $user->guide_status = GuideStatus::PENDING;
            if (! $user->guide_submitted_at) {
                $user->guide_submitted_at = $user->updated_at;
            }
        }

        $user->saveQuietly();
    }

    protected function syncLegacyIsGuide(User $user, string $toStatus): void
    {
        $user->is_guide = match ($toStatus) {
            GuideStatus::VERIFIED => 1,
            GuideStatus::PENDING => 0,
            GuideStatus::REJECTED => 0,
            default => $user->is_guide,
        };
    }
}
