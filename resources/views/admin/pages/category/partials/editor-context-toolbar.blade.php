@php
    $scopes = $scopes ?? [];
    $activeScope = $activeScope ?? ($scopes[0] ?? 'tours');
    $language = $language ?? 'de';
    $completeness = $completeness ?? [];
    $showScopeTabs = count($scopes) > 1;
@endphp

<div class="category-editor__toolbar">
    @if($showScopeTabs)
        <div class="category-editor__toolbar-group">
            <span class="category-editor__toolbar-label">{{ __('admin.category_pages.editor.scope') }}</span>
            <div class="category-editor__scope-tabs" id="scope-tabs">
                @foreach($scopes as $scope)
                    @php $filled = ($completeness[$scope]['de'] ?? false) || ($completeness[$scope]['en'] ?? false); @endphp
                    <button type="button"
                        class="category-editor__tab scope-tab {{ $activeScope === $scope ? 'is-active' : '' }} {{ $filled ? 'is-filled' : 'is-empty' }}"
                        data-scope="{{ $scope }}">
                        {{ \App\Domain\CategoryPage\CategoryPageScope::label($scope) }}
                    </button>
                @endforeach
            </div>
        </div>
        <input type="hidden" name="content_scope" id="content_scope" value="{{ $activeScope }}">
    @else
        <input type="hidden" name="content_scope" id="content_scope" value="{{ $activeScope }}">
    @endif

    <div class="category-editor__toolbar-group">
        <span class="category-editor__toolbar-label">{{ __('admin.category_pages.editor.locale') }}</span>
        <div class="category-editor__locale-tabs" id="locale-tabs">
            @foreach(config('app.locales') as $locale)
                <button type="button"
                    class="category-editor__tab locale-tab {{ $language === $locale ? 'is-active' : '' }}"
                    data-locale="{{ $locale }}">
                    @if($locale === 'de')
                        <i class="fi fi-de"></i> DE
                    @else
                        <i class="fi fi-gb"></i> EN
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <input type="hidden" name="languageSwitch" id="languageSwitch" value="{{ $language }}">
</div>
