<?php

namespace App\Enums;

/**
 * Verification of a guide's legal and billing data (spec 2.5.4 "Account verification"). Separate
 * from GuideStatus: a pending re-verification keeps the guide's tours online.
 */
enum AccountVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function labelKey(): string
    {
        return 'guide_area.account.status.'.$this->value;
    }

    /** Tone of the ga-status badge. */
    public function tone(): string
    {
        return match ($this) {
            self::Verified => 'success',
            self::Pending => 'warning',
            self::Rejected => 'danger',
        };
    }
}
