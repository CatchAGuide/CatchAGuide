<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.todos.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.todos.subtitle') }}</p>
        </div>
    </header>

    {{-- Score + jump links (spec TD-01) --}}
    <section class="ga-card">
        <div class="ga-card__head">
            <h2 class="ga-card__title">{{ __('guide_area.todos.score', ['score' => $result['score']]) }}</h2>
            <span class="ga-card__aside">{{ trans_choice('guide_area.todos.open_count', $result['open'], ['count' => $result['open']]) }}</span>
        </div>
        <div class="ga-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $result['score'] }}">
            <span class="ga-progress__bar" style="width: {{ $result['score'] }}%"></span>
        </div>
        <nav class="ga-jump" aria-label="{{ __('guide_area.todos.sections_label') }}">
            @foreach ($result['sections'] as $key => $section)
                <a href="#todo-{{ $key }}" class="ga-jump__link">
                    {{ __('guide_area.todos.sections.'.$key.'.title') }}
                    <span @class(['ga-pct', 'ga-pct--done' => $section['score'] === 100])>{{ $section['score'] }}%</span>
                </a>
            @endforeach
        </nav>
    </section>

    @if ($result['open'] === 0)
        <div class="ga-empty">
            <i class="fas fa-trophy" aria-hidden="true"></i>
            <p>{{ __('guide_area.todos.all_done') }}</p>
        </div>
    @endif

    @foreach ($result['sections'] as $key => $section)
        <section class="ga-card ga-todo-section" id="todo-{{ $key }}">
            <div class="ga-card__head">
                <div class="ga-todo-section__title">
                    <span class="ga-action-row__icon" aria-hidden="true"><i class="fas {{ ['profile' => 'fa-user', 'tours' => 'fa-fish', 'payment' => 'fa-credit-card', 'calendar' => 'fa-calendar'][$key] }}"></i></span>
                    <div>
                        <h2 class="ga-card__title">{{ __('guide_area.todos.sections.'.$key.'.title') }}</h2>
                        <p class="ga-card__subtitle">
                            @if ($key === 'profile')
                                {{ $isCompany ? __('guide_area.todos.sections.profile.company') : __('guide_area.todos.sections.profile.individual') }}
                            @else
                                {{ __('guide_area.todos.sections.'.$key.'.subtitle') }}
                            @endif
                        </p>
                    </div>
                </div>
                <span @class(['ga-pct', 'ga-pct--done' => $section['score'] === 100])>{{ __('guide_area.todos.optimized', ['score' => $section['score']]) }}</span>
            </div>

            @if ($section['checks'] !== [])
                @include('livewire.guide.partials.todo-checks', ['checks' => $section['checks']])
            @endif

            @if ($key === 'tours')
                @forelse ($section['tours'] as $tour)
                    @if ($tour['score'] === 100)
                        <div class="ga-todo-tour ga-todo-tour--done">
                            <span class="ga-todo-tour__name">{{ $tour['title'] }}</span>
                            <span class="ga-status ga-status--success">{{ __('guide_area.todos.live') }}</span>
                            <span class="ga-pct ga-pct--done">{{ __('guide_area.todos.optimized', ['score' => 100]) }}</span>
                        </div>
                    @else
                        <div class="ga-todo-tour">
                            <div class="ga-todo-tour__head">
                                <span class="ga-todo-tour__name">{{ $tour['title'] }}</span>
                                <span class="ga-status ga-status--success">{{ __('guide_area.todos.live') }}</span>
                                <span class="ga-pct">{{ __('guide_area.todos.optimized', ['score' => $tour['score']]) }}</span>
                            </div>
                            @include('livewire.guide.partials.todo-checks', ['checks' => $tour['checks']])
                        </div>
                    @endif
                @empty
                    <p class="ga-help">{{ __('guide_area.todos.no_live_tours') }}</p>
                @endforelse
            @endif
        </section>
    @endforeach
</div>
