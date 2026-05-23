<?php

namespace App\Enums;

final class GuideType
{
    public const PRIVATE = 'private';
    public const COMPANY = 'company';

    public static function all(): array
    {
        return [self::PRIVATE, self::COMPANY];
    }
}
