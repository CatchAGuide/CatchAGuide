@php
    $lang = $language === 'en' ? 'gb' : 'de';
@endphp

<div class="category-form__charts-lang">
    <div>
        <strong>{{ __('admin.category_pages.form.charts_language') }}</strong>
        <p class="mb-0 text-muted">{{ __('admin.category_pages.form.charts_language_note') }}</p>
    </div>
    <div class="category-form__charts-lang-control">
        <span class="fi fi-{{ $lang }}" id="language-flag"></span>
        <select class="form-select form-select-sm" name="language" id="language">
            @foreach(config('app.locales') as $locale)
                <option value="{{ $locale }}" @selected($locale == $language)>
                    @if($locale === 'de') Deutsch @else English @endif
                </option>
            @endforeach
        </select>
    </div>
</div>
