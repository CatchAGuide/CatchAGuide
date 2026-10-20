<?php

namespace App\Services\Vacation;

use App\Models\VacationReview;

/**
 * Average score and review count per camp/trip, for listing cards and the product page badge.
 * One grouped query per request (bound as scoped), however many cards a page renders.
 */
class VacationReviewStats
{
    /** @var array<string, array{rating: float, count: int}>|null */
    private ?array $stats = null;

    /**
     * @return array{rating: float, count: int}|null  null when the listing has no reviews
     */
    public function forListing(string $listingType, int $listingId): ?array
    {
        return $this->all()[$listingType.':'.$listingId] ?? null;
    }

    /**
     * @return array<string, array{rating: float, count: int}>
     */
    private function all(): array
    {
        if ($this->stats !== null) {
            return $this->stats;
        }

        $this->stats = VacationReview::query()
            ->selectRaw('listing_type, listing_id, AVG(grandtotal_score) as avg_score, COUNT(*) as review_count')
            ->groupBy('listing_type', 'listing_id')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->listing_type.':'.$row->listing_id => [
                    'rating' => round((float) $row->avg_score, 1),
                    'count' => (int) $row->review_count,
                ],
            ])
            ->all();

        return $this->stats;
    }
}
