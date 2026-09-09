@php
    $formAction = isset($activeCategory) && $activeCategory
        ? route($blogPrefix.'.categories.show', $activeCategory)
        : route($blogPrefix.'.index');
@endphp
<div class="cag-mag-filters">
    <form class="cag-mag-filters__search" method="get" action="{{ $formAction }}" role="search" data-magazine-analytics-form="magazine_search_submit">
        <label class="sr-only" for="magazine-search">{{ __('magazine.search_button') }}</label>
        <div class="cag-mag-filters__field">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input
                id="magazine-search"
                type="search"
                name="q"
                value="{{ $search ?? '' }}"
                placeholder="{{ __('magazine.search_placeholder') }}"
                autocomplete="off"
            >
        </div>
        <button type="submit">{{ __('magazine.search_button') }}</button>
        @if(!empty($search))
            <a class="cag-mag-filters__clear" href="{{ $formAction }}" data-magazine-analytics="magazine_search_clear">{{ __('magazine.clear_search') }}</a>
        @endif
    </form>

    <div class="cag-mag-filters__row">
        @if(($categories ?? collect())->isNotEmpty())
            <nav class="cag-mag-filters__cats" aria-label="{{ __('magazine.all_categories') }}">
                <a
                    href="{{ route($blogPrefix.'.index') }}{{ !empty($search) ? '?q='.urlencode($search) : '' }}"
                    class="cag-mag-chip {{ empty($activeCategory) ? 'is-active' : '' }}"
                    data-magazine-analytics="magazine_category_click"
                    data-magazine-category="all"
                >{{ __('magazine.all_categories') }}</a>
                @foreach($categories as $category)
                    @php
                        $catUrl = route($blogPrefix.'.categories.show', $category);
                        if (!empty($search)) {
                            $catUrl .= (str_contains($catUrl, '?') ? '&' : '?').'q='.urlencode($search);
                        }
                    @endphp
                    <a
                        href="{{ $catUrl }}"
                        class="cag-mag-chip {{ isset($activeCategory) && $activeCategory && $activeCategory->id === $category->id ? 'is-active' : '' }}"
                        data-magazine-analytics="magazine_category_click"
                        data-magazine-category="{{ getLocalizedValue($category) }}"
                    >
                        {{ getLocalizedValue($category) }}
                        <span class="cag-mag-chip__count">{{ $category->threads_count }}</span>
                    </a>
                @endforeach
            </nav>
        @endif

        @isset($totalCount)
            <p class="cag-mag-filters__count">
                {{ !empty($search) || !empty($activeCategory)
                    ? __('magazine.results_count_filtered', ['count' => $totalCount])
                    : __('magazine.results_count', ['count' => $totalCount]) }}
            </p>
        @endisset
    </div>
</div>
