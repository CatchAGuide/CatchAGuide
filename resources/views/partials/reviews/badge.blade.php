{{--
    Review score pill for a camp/trip product page title (same look as the listing card score):
    $stats from VacationReviewStats::forListing() ({rating, count}, or null to render nothing),
    $href the review section anchor.
--}}
@if(!empty($stats['count']))
    @php $reviewsLabel = trans_choice('offers.reviews_count', $stats['count'], ['count' => $stats['count']]); @endphp
    <a href="{{ $href ?? '#reviews' }}"
       class="listing-review-badge {{ $class ?? '' }}"
       title="{{ $reviewsLabel }}"
       aria-label="{{ number_format($stats['rating'], 1) }}/10, {{ $reviewsLabel }}">
        <span class="listing-review-badge__value">{{ number_format($stats['rating'], 1) }}</span>
        <span class="listing-review-badge__count">({{ $stats['count'] }})</span>
    </a>
@endif
