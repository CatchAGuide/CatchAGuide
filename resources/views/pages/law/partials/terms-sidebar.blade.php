{{-- Shared legal-pages sidebar. Expects: $navItems (array) --}}
<aside class="col-lg-4 col-xl-3">
    <div class="terms-sidebar-wrap">
        <div class="terms-toc-card" id="termsTocCard">
            <div class="terms-toc-card__header">
                <div class="terms-toc-card__title">
                    <span>@lang('terms.contents')</span>
                    <button type="button" class="terms-toc-toggle" id="termsTocToggle" aria-label="@lang('terms.contents')">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="terms-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="termsSearch" placeholder="@lang('terms.search_placeholder')" autocomplete="off">
                    <button type="button" class="terms-search__clear" id="termsSearchClear" aria-label="@lang('terms.clear')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="terms-search-count" id="termsSearchCount"></div>
            <ul class="terms-toc" id="termsToc" data-mode="links">
                @foreach($navItems as $index => $item)
                    <li class="{{ $item['active'] ? 'active' : '' }}">
                        <a href="{{ $item['url'] }}">
                            <span class="terms-toc__num">{{ $index + 1 }}</span>
                            <span>{{ $item['title'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</aside>
