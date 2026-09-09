@php
    $currentMonth = (int) now()->month;
    $sentences = collect(range(1, 12))->map(fn ($m) => __('vacations.hub_season_month_'.$m))->all();
    $monthLabels = collect(range(1, 12))->map(
        fn ($m) => \Carbon\Carbon::create(null, $m, 1)->locale(app()->getLocale())->translatedFormat('M')
    )->all();
@endphp

<section
    class="vacation-hub__season"
    data-vacation-season-picker
    data-sentences="{{ json_encode($sentences) }}"
    data-analytics-vacation-rail="season"
>
    <p class="vacation-hub__season-eyebrow">{{ __('vacations.hub_season_eyebrow') }}</p>
    <h2 class="vacation-hub__season-title">{{ __('vacations.hub_season_title') }}</h2>

    <div class="vacation-hub__season-pills" role="tablist">
        @foreach($monthLabels as $index => $label)
            @php $month = $index + 1; @endphp
            <button
                type="button"
                class="vacation-hub__season-pill {{ $month === $currentMonth ? 'is-active' : '' }}"
                data-season-month="{{ $month }}"
                role="tab"
                aria-selected="{{ $month === $currentMonth ? 'true' : 'false' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <p class="vacation-hub__season-sentence" data-season-sentence>
        {{ $sentences[$currentMonth - 1] }}
    </p>
</section>

@once
    @push('js_push')
        <script>
            (function () {
                function initVacationSeasonPickers() {
                    document.querySelectorAll('[data-vacation-season-picker]').forEach(function (root) {
                        if (root.dataset.seasonInit === '1') {
                            return;
                        }
                        root.dataset.seasonInit = '1';

                        var sentences = [];
                        try {
                            sentences = JSON.parse(root.dataset.sentences || '[]');
                        } catch (e) {
                            return;
                        }

                        var pills = root.querySelectorAll('[data-season-month]');
                        var sentenceEl = root.querySelector('[data-season-sentence]');

                        pills.forEach(function (pill) {
                            pill.addEventListener('click', function () {
                                var month = parseInt(pill.dataset.seasonMonth, 10);
                                if (!month || !sentences[month - 1]) {
                                    return;
                                }

                                pills.forEach(function (p) {
                                    p.classList.remove('is-active');
                                    p.setAttribute('aria-selected', 'false');
                                });
                                pill.classList.add('is-active');
                                pill.setAttribute('aria-selected', 'true');

                                if (sentenceEl) {
                                    sentenceEl.textContent = sentences[month - 1];
                                }
                            });
                        });
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initVacationSeasonPickers);
                } else {
                    initVacationSeasonPickers();
                }
            })();
        </script>
    @endpush
@endonce
