<?php

namespace App\Services\Guide\Area;

use App\Models\Review;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Guide Area › Reviews (spec 10), Overview "Latest review" and the sidebar badge. Scores are
 * the 1–10 averages incl. automatic reviews; "verified" counts guest reviews of a booking
 * (automatic ones are left out, spec Q-01).
 */
class GuideReviewService
{
    public const PER_PAGE = 8;

    /**
     * @return array{count: int, verified: int, overall: ?float, guide: ?float, region: ?float, label: ?string}
     */
    public function summary(User $guide): array
    {
        $row = $this->query($guide)
            ->toBase()
            ->selectRaw('COUNT(*) AS review_count')
            ->selectRaw('SUM(CASE WHEN is_automatic = 0 AND booking_id IS NOT NULL THEN 1 ELSE 0 END) AS verified_count')
            ->selectRaw('AVG(overall_score) AS overall_avg, AVG(guide_score) AS guide_avg, AVG(region_water_score) AS region_avg')
            ->first();

        $count = (int) ($row->review_count ?? 0);
        $overall = $count ? round((float) $row->overall_avg, 1) : null;

        return [
            'count' => $count,
            'verified' => (int) ($row->verified_count ?? 0),
            'overall' => $overall,
            'guide' => $count ? round((float) $row->guide_avg, 1) : null,
            'region' => $count ? round((float) $row->region_avg, 1) : null,
            'label' => $overall === null ? null : $this->label($overall),
        ];
    }

    public function paginate(User $guide, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->query($guide)
            ->with(['guiding:id,title', 'booking.registeredUser:id,firstname,lastname', 'booking.guestUser:id,firstname,lastname'])
            ->newestFirst()
            ->paginate($perPage);
    }

    public function latest(User $guide): ?Review
    {
        return $this->query($guide)
            ->with(['guiding:id,title', 'booking.registeredUser:id,firstname,lastname', 'booking.guestUser:id,firstname,lastname'])
            ->newestFirst()
            ->first();
    }

    public function unseenCount(User $guide): int
    {
        return $this->query($guide)->whereNull('seen_by_guide_at')->count();
    }

    /** Opening the page marks the reviews shown as seen (spec RV-04). */
    public function markSeen(User $guide, array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        return $ids === [] ? 0 : $this->query($guide)->whereKey($ids)->whereNull('seen_by_guide_at')->update(['seen_by_guide_at' => Carbon::now()]);
    }

    /** ≥ 9.0 Excellent · ≥ 8.0 Very good · ≥ 7.0 Good · below: Fair (spec RV-01). */
    public function label(float $score): string
    {
        $key = match (true) {
            $score >= 9.0 => 'excellent',
            $score >= 8.0 => 'very_good',
            $score >= 7.0 => 'good',
            default => 'fair',
        };

        return __('guide_area.reviews.labels.'.$key);
    }

    /** First name + last initial of the guest who wrote it. */
    public function guestName(Review $review): string
    {
        $customer = $review->booking?->user ?? $review->user;
        $first = trim((string) ($customer?->firstname ?? ''));
        $last = trim((string) ($customer?->lastname ?? ''));

        if ($first === '') {
            return __('guide_area.reviews.guest');
        }

        return $last === '' ? $first : $first.' '.mb_substr($last, 0, 1).'.';
    }

    private function query(User $guide): Builder
    {
        return Review::query()->where('guide_id', $guide->id);
    }
}
