<?php

namespace App\Enums;

final class GuideStatus
{
    public const PENDING = 'pending';
    public const VERIFIED = 'verified';
    public const REJECTED = 'rejected';

    public static function all(): array
    {
        return [self::PENDING, self::VERIFIED, self::REJECTED];
    }
}
