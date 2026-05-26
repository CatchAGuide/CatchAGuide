<?php

namespace App\Traits;

use App\Enums\GuideStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait ScopesPubliclyVisibleGuidings
{
    public function scopePubliclyVisible(Builder $query): Builder
    {
        $query = $query->where($query->getModel()->getTable() . '.status', 1);

        if (! Schema::hasColumn('users', 'guide_status')) {
            return $query->whereHas('user', function (Builder $userQuery) {
                $userQuery->whereIn('is_guide', [1, '1', true]);
            });
        }

        return $query->whereHas('user', function (Builder $userQuery) {
            $userQuery->where(function (Builder $verified) {
                $verified->where('guide_status', GuideStatus::VERIFIED)
                    ->orWhere(function (Builder $legacy) {
                        $legacy->whereNull('guide_status')
                            ->whereIn('is_guide', [1, '1', true]);
                    });
            });
        });
    }

    public static function publiclyVisibleQuery(): Builder
    {
        return static::query()->publiclyVisible();
    }
}
