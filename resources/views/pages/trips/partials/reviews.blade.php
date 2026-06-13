<section class="trip-offer-page__reviews mb-4" id="reviews" data-analytics-trip-reviews>
    <h2 class="trip-offer-page__section-title">{{ __('vacations.reviews_title') }}</h2>

    @if(!empty($reviewTrust))
        <p class="trip-offer-page__reviews-summary">
            {{ __('vacations.trust_guide_rating', [
                'name' => $reviewTrust['name'],
                'rating' => $reviewTrust['rating'],
                'count' => $reviewTrust['count'],
            ]) }}
        </p>
    @else
        <p class="text-muted">{{ __('vacations.reviews_empty') }}</p>
    @endif
</section>
