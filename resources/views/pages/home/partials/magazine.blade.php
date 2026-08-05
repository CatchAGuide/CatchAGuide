@if(($magazineThreads ?? collect())->isNotEmpty())
<section class="cag-home-section cag-home-magazine" data-cag-reveal>
    <div class="cag-home-container">
        <div class="cag-home-section__header cag-reveal__header">
            <h2 class="cag-home-section__title">{{ __('homepage.magazine_section_title') }}</h2>
            <a href="{{ route($blogPrefix.'.index') }}" class="cag-home-section__link">{{ __('homepage.see-more') }}</a>
        </div>
        <div class="cag-home-magazine__grid">
            @foreach($magazineThreads as $thread)
                <a
                    href="{{ route($blogPrefix.'.thread.show', [$thread->slug]) }}"
                    class="cag-home-magazine__card cag-reveal__item"
                    style="--reveal-i: {{ $loop->index }}"
                >
                    <div class="cag-home-magazine__thumb" style="background-image: url('{{ $thread->getThumbnailPath() }}');"></div>
                    <h3 class="cag-home-magazine__card-title">{{ $thread->title }}</h3>
                    @if(!empty($thread->excerpt))
                        <p class="cag-home-magazine__excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($thread->excerpt), 90) }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
