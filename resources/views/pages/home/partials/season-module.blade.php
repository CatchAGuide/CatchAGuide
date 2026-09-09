@if(!empty($season))
<section class="cag-home-section cag-home-season" data-cag-reveal>
    <div class="cag-home-container">
        <div class="cag-home-season__header cag-reveal__header">
            <div class="cag-home-season__heading">
                <h2 class="cag-home-season__title">{{ $season['title'] }}</h2>
                <p class="cag-home-season__text">{{ $season['text'] }}</p>
            </div>
        </div>

        @if(($season['species'] ?? collect())->isNotEmpty())
            <div class="cag-home-season__grid">
                @foreach($season['species'] as $species)
                    <a
                        href="{{ $species['url'] }}"
                        class="cag-home-season__card cag-reveal__item"
                        style="--reveal-i: {{ $loop->index + 1 }}"
                    >
                        <img
                            class="cag-home-season__img"
                            src="{{ $species['thumbnail'] }}"
                            alt="{{ $species['name'] }}"
                            loading="lazy"
                            width="480"
                            height="270"
                        >
                        <span class="cag-home-season__fade" aria-hidden="true"></span>
                        <span class="cag-home-season__meta">
                            @if(!empty($species['country']))
                                <span class="cag-home-season__badge">{{ $species['country'] }}</span>
                            @endif
                            <span class="cag-home-season__name">{{ $species['name'] }}</span>
                        </span>
                        <span class="cag-home-season__arrow" aria-hidden="true">
                            @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 16])
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif
