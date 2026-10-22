<?php

namespace App\Presenters\Reviews;

use App\Models\Review;
use App\Models\VacationReview;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Shapes tour reviews and camp/trip reviews into one structure for the shared product page
 * review section (resources/views/partials/reviews/section.blade.php).
 *
 * Section shape: score, label, count, categories [{label, value}], disclaimer and
 * reviews [{name, initial, date, score, text, automatic}].
 */
class ReviewSectionPresenter
{
    /**
     * @param  array{count: int, averages: array{grandtotal: float, overall: float, accommodation: float, fishing: float, value: float}, reviews: Collection<int, VacationReview>}|null  $listing  VacationReviewService::listingReviews()
     */
    public function vacation(?array $listing): ?array
    {
        if ($listing === null || $listing['count'] < 1) {
            return null;
        }

        $averages = $listing['averages'];

        return $this->section(
            (float) $averages['grandtotal'],
            (int) $listing['count'],
            [
                ['label' => __('guidings.Overall'), 'value' => (float) $averages['overall']],
                ['label' => __('vacations.rating_accommodation'), 'value' => (float) $averages['accommodation']],
                ['label' => __('vacations.rating_fishing'), 'value' => (float) $averages['fishing']],
                ['label' => __('vacations.rating_value'), 'value' => (float) $averages['value']],
            ],
            __('vacations.reviews_disclaimer'),
            $listing['reviews']->map(fn (VacationReview $review) => $this->review(
                $review->displayName(),
                $review->created_at ? $this->formatDate($review->created_at) : null,
                (float) $review->grandtotal_score,
                $this->localizedComment($review->comment, $review->language),
                false,
            ))->all(),
        );
    }

    /**
     * @param  Collection<int, Review>  $reviews  with booking and user loaded
     * @param  array{grandtotal: float, overall: float, guide: float, region_water: float}  $averages
     */
    public function tour(Collection $reviews, int $count, array $averages): ?array
    {
        if ($count < 1) {
            return null;
        }

        return $this->section(
            (float) $averages['grandtotal'],
            $count,
            [
                ['label' => __('guidings.Overall'), 'value' => (float) $averages['overall']],
                ['label' => __('guidings.Guide'), 'value' => (float) $averages['guide']],
                ['label' => __('guidings.Region_Water'), 'value' => (float) $averages['region_water']],
            ],
            __('guidings.Real_experiences_description'),
            $reviews->map(function (Review $review) {
                $date = $review->booking?->getBookingDate() ?? ($review->created_at ? Carbon::parse($review->created_at) : null);

                return $this->review(
                    $review->user->firstname ?? null,
                    $date ? $this->formatDate($date) : null,
                    (float) $review->grandtotal_score,
                    filled($review->comment) ? translate($review->comment) : null,
                    (bool) $review->is_automatic,
                );
            })->all(),
        );
    }

    private function section(float $score, int $count, array $categories, string $disclaimer, array $reviews): array
    {
        return [
            'score' => $score,
            'label' => getRatingLabel($score),
            'count' => $count,
            'categories' => $categories,
            'disclaimer' => $disclaimer,
            'reviews' => $reviews,
        ];
    }

    private function review(?string $name, ?string $date, float $score, ?string $text, bool $automatic): array
    {
        $name = $name !== null && trim($name) !== '' ? trim($name) : null;

        return [
            'name' => $name,
            'initial' => $name ? mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8') : '?',
            'date' => $date,
            'score' => $score,
            'text' => filled($text) ? $text : null,
            'automatic' => $automatic,
        ];
    }

    /**
     * Comments in the page's language are shown as written; others go through the cached,
     * budgeted translate() helper.
     */
    private function localizedComment(?string $comment, ?string $language): ?string
    {
        if (! filled($comment)) {
            return null;
        }

        return $language === app()->getLocale() ? $comment : translate($comment);
    }

    private function formatDate(CarbonInterface $date): string
    {
        return $date->copy()->locale(app()->getLocale())->translatedFormat('M j, Y');
    }
}
