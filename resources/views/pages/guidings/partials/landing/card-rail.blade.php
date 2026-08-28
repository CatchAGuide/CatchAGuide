{{--
    Single horizontal tour-card rail (most-booked / new tours). Reuses the
    cag-home-offers__* rail styles + offer-card partial from the homepage.
    Props: title, subtitle, cards (Collection), railKey (string, unique per section),
    seeAllUrl (optional), seeAllLabel (optional)
--}}
@php $cards = $cards ?? collect(); @endphp
@if($cards->isNotEmpty())
<section class="cag-home-section cag-home-offers" data-cag-reveal>
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-reveal__header">
            <div class="cag-home-section__heading">
                <h2 class="cag-home-section__title">{{ $title }}</h2>
                @if(!empty($subtitle))
                    <x-title-rule />
                    <p class="cag-home-section__subtitle">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        <div class="cag-home-offers__viewport" data-offer-rail="{{ $railKey }}">
            <div class="cag-home-offers__rail" role="list">
                @foreach($cards as $card)
                    @include('pages.home.partials.offer-card', [
                        'card' => $card,
                        'type' => 'tour',
                        'revealIndex' => min($loop->index, 6),
                    ])
                @endforeach
            </div>
        </div>

        @if(!empty($seeAllUrl))
            <div class="cag-home-offers__module-browse-mobile">
                <a href="{{ $seeAllUrl }}" data-home-analytics="guidings_landing_rail_see_all" data-product-type="{{ $railKey }}">
                    {{ $seeAllLabel }}
                </a>
            </div>
        @endif
    </div>
</section>
@endif
