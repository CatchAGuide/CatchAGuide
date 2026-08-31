@props([
    'tile',
    'clone' => false,
    'showCount' => true,
])

<a
    href="{{ $tile['url'] }}"
    class="vacation-fish-rail__tile {{ $showCount ? '' : 'vacation-fish-rail__tile--compact' }}"
    role="listitem"
    @if($clone) aria-hidden="true" tabindex="-1" @endif
    data-analytics-vacation-target-fish="{{ $tile['slug'] }}"
>
    @if(!empty($tile['thumbnail']))
        <img
            src="{{ media_url($tile['thumbnail']) }}"
            alt="{{ $clone ? '' : $tile['name'] }}"
            class="vacation-fish-rail__img"
            loading="lazy"
            width="240"
            height="180"
        >
    @else
        <span class="vacation-fish-rail__placeholder" aria-hidden="true">
            <i class="fas fa-fish"></i>
        </span>
    @endif

    <span class="vacation-fish-rail__fade"></span>
    <span class="vacation-fish-rail__meta">
        <span class="vacation-fish-rail__name">{{ $tile['name'] }}</span>
        @if($showCount)
            <span class="vacation-fish-rail__count">{{ __('vacations.hub_target_fish_count', ['count' => $tile['count']]) }}</span>
        @endif
    </span>
</a>
