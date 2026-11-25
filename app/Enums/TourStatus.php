<?php

namespace App\Enums;

use App\Models\Guiding;

/**
 * A tour's publication state, read from the stored guidings.status (no data rewrite):
 * 2 = draft (never published), 1 = live, 0 = deactivated by the guide or an admin.
 */
enum TourStatus: int
{
    case Draft = 2;
    case Live = 1;
    case Deactivated = 0;

    public static function fromGuiding(Guiding $guiding): self
    {
        return self::tryFrom((int) $guiding->status) ?? self::Draft;
    }

    public static function fromKey(string $key): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->key() === $key) {
                return $case;
            }
        }

        return null;
    }

    /** Stable string used in URLs and the API (`draft`, `live`, `deactivated`). */
    public function key(): string
    {
        return strtolower($this->name);
    }

    public function label(): string
    {
        return __('guide_area.tours.status.'.$this->key());
    }
}
