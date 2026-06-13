<?php

namespace App\Domain\Vacation;

use App\Models\Camp;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Model;

final class BookableListingPolicy
{
    public const ACTIVE_STATUS = 'active';

    public function isBookable(Model $listing): bool
    {
        if ($listing instanceof Camp || $listing instanceof Trip) {
            return (string) $listing->status === self::ACTIVE_STATUS;
        }

        return false;
    }

    public function activeStatus(): string
    {
        return self::ACTIVE_STATUS;
    }
}
