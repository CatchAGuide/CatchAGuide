<?php

namespace App\Services\Reviews;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Models\UserGuest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TestimonialSelector
{
    /**
     * Latest real guest reviews only: not automatic, grandtotal score > 8.
     *
     * @return Collection<int, array{quote: string, score: float, author: string, date: ?string, tour_title: ?string, tour_url: ?string}>
     */
    public function latest(int $limit = 15): Collection
    {
        return Cache::remember("testimonials_latest_v1_{$limit}_".app()->getLocale(), now()->addMinutes(30), function () use ($limit) {
            $reviews = Review::query()
                ->with([
                    'guiding:id,title,slug',
                    'booking:id,user_id,is_guest,book_date,blocked_event_id',
                    'booking.calendar_schedule:id,date',
                    'booking.blocked_event:id,from',
                    'booking.guestUser:id,firstname',
                    'booking.registeredUser:id,firstname',
                ])
                ->where(function ($q) {
                    $q->where('is_automatic', false)->orWhereNull('is_automatic');
                })
                ->where('grandtotal_score', '>', 8)
                ->where('grandtotal_score', '<=', 10)
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->where('comment', 'not like', 'Successfully completed fishing tour%')
                ->latest('id')
                ->limit($limit)
                ->get();

            return $reviews
                ->map(function (Review $review) {
                    $guiding = $review->guiding;
                    $booking = $review->booking;
                    $author = $this->testimonialAuthor($review, $booking);
                    $tourDate = $booking?->getBookingDate() ?? $review->created_at;
                    $tourTitle = $guiding?->title
                        ? translate($guiding->title)
                        : null;

                    return [
                        'quote' => Str::limit(strip_tags((string) translate($review->comment)), 180),
                        'score' => round((float) $review->grandtotal_score, 1),
                        'author' => $author ?: __('homepage.testimonial_guest'),
                        'date' => $tourDate?->translatedFormat('M Y'),
                        'tour_title' => $tourTitle ? Str::limit($tourTitle, 60) : null,
                        'tour_url' => ($guiding?->id && $guiding?->slug)
                            ? $guiding->publicShowUrl()
                            : null,
                    ];
                })
                ->filter(fn (array $item) => filled($item['quote']) && $item['score'] > 8 && $item['score'] <= 10)
                ->values();
        });
    }

    /**
     * Prefer the booking guest/registered firstname; never resolve guest IDs against users.
     */
    private function testimonialAuthor(Review $review, ?Booking $booking): ?string
    {
        $fromBooking = trim((string) ($booking?->user?->firstname ?? ''));
        if ($fromBooking !== '') {
            return $fromBooking;
        }

        if (! $review->user_id) {
            return null;
        }

        if ($booking?->is_guest) {
            return UserGuest::query()->whereKey($review->user_id)->value('firstname');
        }

        return User::query()->whereKey($review->user_id)->value('firstname');
    }
}
