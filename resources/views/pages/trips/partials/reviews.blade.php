{{-- @if(!empty($reviewTrust))
    <div class="trip-offer-page__reviews-band">
        <section class="trip-offer-page__reviews" id="reviews" data-analytics-trip-reviews>
            <div class="trip-offer-page__reviews-card">
                <h2 class="trip-offer-page__section-title trip-offer-page__reviews-title">
                    {{ __('vacations.reviews_title') }}
                </h2>

                <div class="trip-offer-page__reviews-trust">
                    <div class="trip-offer-page__reviews-trust-score" aria-hidden="true">
                        <span class="trip-offer-page__reviews-trust-value">{{ number_format((float) $reviewTrust['rating'], 1) }}</span>
                        <span class="trip-offer-page__reviews-trust-outof">/10</span>
                    </div>

                    <div class="trip-offer-page__reviews-trust-meta">
                        <p class="trip-offer-page__reviews-trust-name">{{ $reviewTrust['name'] }}</p>
                        <p class="trip-offer-page__reviews-trust-label">
                            <i class="fas fa-star trip-offer-page__reviews-trust-icon" aria-hidden="true"></i>
                            {{ number_format((float) $reviewTrust['rating'], 1) }}★
                            <span class="trip-offer-page__reviews-trust-count">({{ $reviewTrust['count'] }} {{ strtolower(__('vacations.reviews_title')) }})</span>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endif --}}
