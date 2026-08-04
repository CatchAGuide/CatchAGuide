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

    /**
     * Legacy is_guide flag is active (verified guide). Treats only 1/true/'1' as guide.
     */
    public function hasActiveGuideFlag(): bool
    {
        return $this->is_guide === 1 || $this->is_guide === true || $this->is_guide === '1';
    }

    /**
     * Legacy is_guide is inactive. Both 0 and null mean non-guide (not only null).
     */
    public function hasInactiveGuideFlag(): bool
    {
        return $this->is_guide === null
            || $this->is_guide === 0
            || $this->is_guide === '0'
            || $this->is_guide === false;
    }

    public function isVerifiedGuide(): bool
    {
        if ($this->guide_status === GuideStatus::VERIFIED) {
            return true;
        }

        if ($this->guide_status === null) {
            return $this->hasActiveGuideFlag();
        }

        return false;
    }

    public function isPendingGuide(): bool
    {
        // Pending is tracked via guide_status only.
        // is_guide 0 (same as null) means normal user when guide_status is empty.
        return $this->guide_status === GuideStatus::PENDING;
    }

    public function isRejectedGuide(): bool
    {
        return $this->guide_status === GuideStatus::REJECTED;
    }

    public function hasGuideApplication(): bool
    {
        return $this->guide_status !== null || $this->hasActiveGuideFlag();
    }

    /**
     * Scope: users who are not verified guides (is_guide 0 or null).
     */
    public function scopeWhereInactiveGuideFlag($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('is_guide')
                ->orWhere('is_guide', 0)
                ->orWhere('is_guide', false)
                ->orWhere('is_guide', '0');
        });
    }

    /**
     * Scope: verified guides via guide_status or legacy is_guide = 1.
     */
    public function scopeWhereVerifiedGuide($query)
    {
        return $query->where(function ($q) {
            $q->where('guide_status', GuideStatus::VERIFIED)
                ->orWhere(function ($legacy) {
                    $legacy->whereNull('guide_status')
                        ->where(function ($flag) {
                            $flag->where('is_guide', 1)
                                ->orWhere('is_guide', true)
                                ->orWhere('is_guide', '1');
                        });
                });
        });
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
