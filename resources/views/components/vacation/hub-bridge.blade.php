@props([
    'totalCamps' => 0,
    'totalTrips' => 0,
    'countryCount' => 0,
    'inspirationTiles' => collect(),
])

<section class="vacation-hub__interlude mb-5" data-analytics-vacation-rail="hub-bridge">
    <div class="vacation-hub__interlude-inner">
        <div class="vacation-hub__interlude-band">
            <header class="vacation-hub__interlude-head">
                <p class="vacation-hub__interlude-eyebrow">{{ __('vacations.hub_bridge_eyebrow') }}</p>
                <h2 class="vacation-hub__interlude-title">{{ __('vacations.hub_bridge_title') }}</h2>
                <p class="vacation-hub__interlude-lead">{{ __('vacations.hub_bridge_tagline') }}</p>
            </header>

            <div class="vacation-hub__interlude-stats">
                <div class="vacation-hub__interlude-stat vacation-hub__interlude-stat--camp">
                    <span class="vacation-hub__interlude-stat-icon" aria-hidden="true">
                        <i class="fas fa-campground"></i>
                    </span>
                    <span class="vacation-hub__interlude-stat-copy">
                        <strong>{{ $totalCamps }}</strong>
                        {{ __('vacations.hub_stat_camps_label') }}
                    </span>
                </div>

                <div class="vacation-hub__interlude-stat vacation-hub__interlude-stat--trip">
                    <span class="vacation-hub__interlude-stat-icon" aria-hidden="true">
                        <i class="fas fa-suitcase-rolling"></i>
                    </span>
                    <span class="vacation-hub__interlude-stat-copy">
                        <strong>{{ $totalTrips }}</strong>
                        {{ __('vacations.hub_stat_trips_label') }}
                    </span>
                </div>

                <div class="vacation-hub__interlude-stat vacation-hub__interlude-stat--countries">
                    <span class="vacation-hub__interlude-stat-icon" aria-hidden="true">
                        <i class="fas fa-globe-europe"></i>
                    </span>
                    <span class="vacation-hub__interlude-stat-copy">
                        <strong>{{ $countryCount }}</strong>
                        {{ __('vacations.hub_stat_countries_label') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="vacation-hub__interlude-details">
            <div class="vacation-hub__interlude-usps">
                @foreach(config('vacations.hub_value_props', []) as $prop)
                    <article class="vacation-hub__interlude-usp">
                        <span class="vacation-hub__interlude-usp-icon" aria-hidden="true">
                            <i class="fas {{ $prop['icon'] }}"></i>
                        </span>
                        <div>
                            <h3 class="vacation-hub__interlude-usp-title">{{ __($prop['title_key']) }}</h3>
                            <p class="vacation-hub__interlude-usp-text">{{ __($prop['text_key']) }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($inspirationTiles->isNotEmpty())
                <div class="vacation-hub__interlude-inspire">
                    <p class="vacation-hub__interlude-inspire-label">{{ __('vacations.hub_inspiration_title') }}</p>
                    <div class="vacation-hub__interlude-chips">
                        @foreach($inspirationTiles as $tile)
                            <a href="{{ $tile['url'] }}" class="vacation-hub__interlude-chip">{{ $tile['title'] }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
