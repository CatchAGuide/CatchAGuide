<?php

namespace App\Traits;

use App\Enums\GuideStatus;
use App\Models\GuideRequest;
use App\Models\GuideStatusLog;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasGuideStatus
{
    public function guideRequests(): HasMany
    {
        return $this->hasMany(GuideRequest::class);
    }

    public function guideStatusLogs(): HasMany
    {
        return $this->hasMany(GuideStatusLog::class);
    }

    public function isVerifiedGuide(): bool
    {
        if ($this->guide_status === GuideStatus::VERIFIED) {
            return true;
        }

        if ($this->guide_status === null) {
            return $this->is_guide === 1 || $this->is_guide === true || $this->is_guide === '1';
        }

        return false;
    }

    public function isPendingGuide(): bool
    {
        if ($this->guide_status === GuideStatus::PENDING) {
            return true;
        }

        if ($this->guide_status === null) {
            return $this->is_guide === 0 || $this->is_guide === '0';
        }

        return false;
    }

    public function isRejectedGuide(): bool
    {
        return $this->guide_status === GuideStatus::REJECTED;
    }

    public function hasGuideApplication(): bool
    {
        return $this->guide_status !== null
            || $this->is_guide === 0
            || $this->is_guide === '0'
            || $this->is_guide === 1
            || $this->is_guide === true
            || $this->is_guide === '1';
    }

    public function canAccessGuideDashboard(): bool
    {
        return $this->isVerifiedGuide() || $this->isPendingGuide();
    }

    /**
     * Access guide profile / own guidings list (e.g. drafts after a rejected application).
     */
    public function canViewGuideTools(): bool
    {
        return $this->canAccessGuideDashboard() || $this->isRejectedGuide();
    }

    public function canApplyAsGuide(): bool
    {
        return ! $this->isVerifiedGuide() && ! $this->isPendingGuide();
    }

    public function canPublishGuidings(): bool
    {
        return $this->isVerifiedGuide();
    }

    public function latestRejectedGuideRequest(): ?GuideRequest
    {
        return $this->guideRequests()
            ->where('decision', 'rejected')
            ->latest('reviewed_at')
            ->first();
    }
}
