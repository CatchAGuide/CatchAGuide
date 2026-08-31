@if(($targetSpecies ?? collect())->isNotEmpty())
<section class="cag-home-section cag-home-species" data-species-spotlight>
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-home-species__header">
            <div class="cag-home-species__heading">
                <h2 class="cag-home-section__title">{{ __('homepage.species_title') }}</h2>
                <p class="cag-home-species__subtitle">{{ __('homepage.species_subtitle') }}</p>
                <a href="{{ route('targets.index') }}" class="cag-home-section__link cag-home-section__link--mobile">
                    {{ __('homepage.species_view_all') }}
                </a>
            </div>
            <a href="{{ route('targets.index') }}" class="cag-home-section__link d-none d-md-inline">
                {{ __('homepage.species_view_all') }}
            </a>
        </div>

        <div class="cag-home-species__viewport" data-species-viewport>
            <div class="cag-home-species__rail" role="list">
                @foreach($targetSpecies as $species)
                    <a
                        href="{{ $species['url'] }}"
                        class="cag-home-species__card"
                        role="listitem"
                        style="--species-i: {{ $loop->index }}"
                    >
                        <img
                            class="cag-home-species__img"
                            src="{{ $species['thumbnail'] }}"
                            alt="{{ $species['name'] }}"
                            loading="lazy"
                            draggable="false"
                            width="320"
                            height="240"
                        >
                        <span class="cag-home-species__fade" aria-hidden="true"></span>
                        <span class="cag-home-species__shine" aria-hidden="true"></span>
                        <span class="cag-home-species__meta">
                            <span class="cag-home-species__name">{{ $species['name'] }}</span>
                            <span class="cag-home-species__cta">
                                {{ __('homepage.species_explore') }}
                                <i class="fas fa-arrow-right" aria-hidden="true"></i>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
