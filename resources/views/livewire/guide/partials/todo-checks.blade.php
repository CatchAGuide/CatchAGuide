{{-- Optimization checks: passed ones ticked, open ones with hint and "Fix" (spec TD-06). --}}
<ul class="ga-checks">
    @foreach ($checks as $check)
        <li @class(['ga-checks__item', 'is-open' => ! $check['passed']])>
            <span class="ga-checks__mark" aria-hidden="true">@if ($check['passed'])<i class="fas fa-check"></i>@endif</span>
            <span class="ga-checks__text">
                <span class="ga-checks__label">{{ $check['label'] }}</span>
                @if (! $check['passed'] && $check['hint'])
                    <span class="ga-checks__hint">{{ $check['hint'] }}</span>
                @endif
            </span>
            <span class="ga-visually-hidden">{{ $check['passed'] ? __('guide_area.todos.done') : __('guide_area.todos.open') }}</span>
            @unless ($check['passed'])
                <a href="{{ $check['fix_url'] }}" class="ga-btn ga-btn--dark ga-btn--sm">{{ $check['fix_label'] }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
            @endunless
        </li>
    @endforeach
</ul>
