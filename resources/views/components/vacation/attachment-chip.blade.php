@props([
    'type' => 'generic',
    'value' => '',
    'label' => null,
    'tooltip' => null,
    'showIcon' => true,
])

@php
    use App\Presenters\Vacation\CampAttachmentChipPresenter;

    $type = (string) $type;
    $value = is_scalar($value) ? (string) $value : '';
    $label = is_scalar($label) ? (string) $label : null;
    if ($label === '') {
        $label = null;
    }

    $tooltip = is_scalar($tooltip) ? (string) $tooltip : null;
    if (! filled($tooltip)) {
        $tooltip = CampAttachmentChipPresenter::tooltipFor($type, $label);
    }

    $assetIcons = [
        'persons' => asset('assets/images/icons/user-new.svg'),
        'duration' => asset('assets/images/icons/clock-new.svg'),
        'tour' => asset('assets/images/icons/fishing-tool-new.svg'),
        'boat' => asset('assets/images/icons/fishing-tool-new.svg'),
        'water' => asset('assets/images/icons/water-waves.png'),
        'water-type' => asset('assets/images/icons/water-waves.png'),
    ];
    $assetSrc = $assetIcons[$type] ?? null;
@endphp

@if($value !== '')
<span
    {{ $attributes->class(['attachment-chip', 'attachment-chip--'.$type]) }}
    tabindex="0"
    aria-label="{{ $tooltip }} — {{ $value }}"
>
    <span class="attachment-chip__tooltip" role="tooltip">{{ $tooltip }}</span>
    @if($showIcon)
    <span class="attachment-chip__icon" aria-hidden="true">
        @if($assetSrc)
            <img src="{{ $assetSrc }}" width="14" height="14" alt="">
        @else
            <svg class="attachment-chip__svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                @switch($type)
                    @case('area')
                        <rect x="4" y="4" width="16" height="16" rx="1"/>
                        <path d="M4 10h16M10 4v16"/>
                        @break
                    @case('bath')
                        <path d="M4 12h16v4a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-4z"/>
                        <path d="M6 12V7a2 2 0 0 1 2-2h1"/>
                        <path d="M7 19v1M17 19v1"/>
                        @break
                    @case('bedrooms')
                    @case('bed')
                        <path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/>
                        <path d="M2 14h20"/>
                        <path d="M4 10V7a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v3"/>
                        @break
                    @case('parking')
                        <rect x="4" y="3" width="16" height="18" rx="2"/>
                        <path d="M9 17V7h4.5a3.5 3.5 0 0 1 0 7H9"/>
                        @break
                    @case('jetty')
                        <circle cx="12" cy="8" r="3"/>
                        <path d="M12 11v5"/>
                        <path d="M8 21c.5-2 2-3 4-3s3.5 1 4 3"/>
                        <path d="M5 21h14"/>
                        @break
                    @case('engine')
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M12 2v3M12 19v3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M2 12h3M19 12h3M4.9 19.1L7 17M17 7l2.1-2.1"/>
                        @break
                    @case('license')
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <circle cx="9" cy="12" r="2"/>
                        <path d="M14 10h5M14 14h5"/>
                        @break
                    @case('length')
                        <path d="M4 12h16"/>
                        <path d="M4 9v6M20 9v6"/>
                        @break
                    @default
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 8v4M12 16h.01"/>
                @endswitch
            </svg>
        @endif
    </span>
    @endif
    <span class="attachment-chip__text">
        @if($label)
            <span class="attachment-chip__label">{{ $label }}</span>
        @endif
        <span class="attachment-chip__value">{{ $value }}</span>
    </span>
</span>
@endif
