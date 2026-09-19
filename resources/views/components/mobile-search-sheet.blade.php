@props([
    'triggerLabel' => null,
    'summary' => null,
    'sheetTitle' => null,
    'sheetId' => null,
    'enabled' => true,
])
@if(! $enabled)
{{ $slot }}
@else
<button
    type="button"
    class="mobile-search-sheet__trigger"
    data-mobile-search-sheet-open="{{ $sheetId }}"
    aria-haspopup="dialog"
    aria-controls="{{ $sheetId }}"
    aria-expanded="false"
>
    <span class="mobile-search-sheet__trigger-copy">
        <span class="mobile-search-sheet__trigger-label">{{ $triggerLabel }}</span>
        <span class="mobile-search-sheet__trigger-summary">{{ $summary }}</span>
    </span>
    <span class="mobile-search-sheet__trigger-cta">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
            <circle cx="11" cy="11" r="7"></circle>
            <line x1="16.5" y1="16.5" x2="21" y2="21"></line>
        </svg>
        {{ __('offers.search_field') }}
    </span>
</button>

<div
    class="mobile-search-sheet__backdrop"
    data-mobile-search-sheet-backdrop="{{ $sheetId }}"
    data-mobile-search-sheet-close
    aria-hidden="true"
></div>

<div
    id="{{ $sheetId }}"
    class="mobile-search-sheet__panel"
    data-mobile-search-sheet
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    aria-labelledby="{{ $sheetId }}Title"
>
    <div class="mobile-search-sheet__head">
        <span id="{{ $sheetId }}Title" class="mobile-search-sheet__head-title">{{ $sheetTitle }}</span>
        <button
            type="button"
            class="mobile-search-sheet__close"
            data-mobile-search-sheet-close
            aria-label="{{ __('offers.search_mobile_close') }}"
        >&times;</button>
    </div>
    {{ $slot }}
</div>
@endif
