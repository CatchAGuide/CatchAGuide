<?php

namespace App\Presenters\Vacation;

use App\Models\Review;
use App\Models\Trip;
use App\Models\User;

class TripTrustSignalResolver
{
    public function resolve(Trip $trip): ?array
    {
        if (! $trip->user_id) {
            return null;
        }

        $reviews = Review::query()->where('guide_id', $trip->user_id)->get();
        if ($reviews->isEmpty()) {
            return null;
        }

        $guide = User::find($trip->user_id);
        $avg = round((float) $reviews->avg('grandtotal_score'), 1);
        $count = $reviews->count();
        $name = $trip->provider_name ?: ($guide?->name ?? __('vacations.trust_guide_fallback'));

        return [
            'label' => __('vacations.trust_guide_rating', [
                'name' => $name,
                'rating' => $avg,
                'count' => $count,
            ]),
            'rating' => $avg,
            'count' => $count,
            'name' => $name,
        ];
    }
}
