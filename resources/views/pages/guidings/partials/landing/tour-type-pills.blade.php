@php $pills = $pills ?? collect(); @endphp
@if($pills->isNotEmpty())
<section class="cag-home-section cag-home-species" data-cag-reveal data-gl-pills>
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-reveal__header">
            <div class="cag-home-species__heading">
                <h2 class="cag-home-section__title">{{ __('homepage.landing_pills_title') }}</h2>
                <p class="cag-home-species__subtitle">{{ __('homepage.landing_pills_subtitle') }}</p>
            </div>
        </div>

        <div class="gl-pills" role="tablist" aria-label="{{ __('homepage.landing_pills_title') }}">
            @foreach($pills as $pill)
                <button
                    type="button"
                    class="gl-pills__btn{{ $loop->first ? ' is-active' : '' }}"
                    data-gl-pill-btn="{{ $pill['key'] }}"
                    role="tab"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                >{{ $pill['label'] }}</button>
            @endforeach
        </div>

        @foreach($pills as $pill)
            <div class="gl-pill-panel{{ $loop->first ? ' is-active' : '' }}" data-gl-pill-panel="{{ $pill['key'] }}" role="tabpanel">
                <div class="cag-home-offers__viewport" data-offer-rail="pill-{{ $pill['key'] }}">
                    <div class="cag-home-offers__rail" role="list">
                        @foreach($pill['cards'] as $card)
                            @include('pages.home.partials.offer-card', [
                                'card' => $card,
                                'type' => 'tour',
                                'revealIndex' => min($loop->index, 6),
                            ])
                        @endforeach
                    </div>
                </div>
                <div class="cag-home-offers__module-browse-mobile">
                    <a href="{{ route('guidings.index') }}" data-home-analytics="guidings_landing_pill_see_all" data-product-type="{{ $pill['key'] }}">
                        {{ __('homepage.landing_pills_see_all') }}
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
