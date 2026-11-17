<?php

namespace App\Enums;

enum CertificateStatus: string
{
    case InReview = 'in_review';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function labelKey(): string
    {
        return 'guide_area.certificates.status.'.$this->value;
    }
}
