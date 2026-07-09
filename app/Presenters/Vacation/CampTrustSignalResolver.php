<?php

namespace App\Presenters\Vacation;

use App\Models\Camp;
use App\Models\Review;
use App\Models\User;

class CampTrustSignalResolver
{
    public function resolve(Camp $camp): ?array
    {
        $guideUserId = $camp->user_id;

        if (! $guideUserId) {
            $guiding = $camp->guidings()->first();
            $guideUserId = $guiding?->user_id;
        }

        if (! $guideUserId) {
            return null;
        }

        $reviews = Review::query()->where('guide_id', $guideUserId)->get();
        if ($reviews->isEmpty()) {
            return null;
        }

        $guide = User::find($guideUserId);
        $avg = round((float) $reviews->avg('grandtotal_score'), 1);
        $count = $reviews->count();

        return [
            'label' => __('vacations.trust_guide_rating', [
                'name' => $guide?->name ?? __('vacations.trust_guide_fallback'),
                'rating' => $avg,
                'count' => $count,
            ]),
            'rating' => $avg,
            'count' => $count,
        ];
    }
}
