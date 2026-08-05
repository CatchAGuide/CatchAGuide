@props([
    'target' => null,
    'modalId' => null,
    'label' => null,
    'resultCount' => null,
    'tag' => 'button',
    'id' => null,
])

@php
    $label = $label ?? __('destination.show_on_map');
    $isButton = $tag === 'button';
    if ($target === null || $target === '') {
        $target = $modalId
            ? (str_starts_with((string) $modalId, '#') ? (string) $modalId : '#'.$modalId)
            : '#mapModal';
    }
@endphp

<x-maps.assets />

<div {{ $attributes->class(['cag-map-teaser']) }}>
    <div class="cag-map-teaser__canvas" aria-hidden="true">
        <span class="cag-map-teaser__grid"></span>
        <span class="cag-map-teaser__pin cag-map-teaser__pin--a"></span>
        <span class="cag-map-teaser__pin cag-map-teaser__pin--b"></span>
        <span class="cag-map-teaser__pin cag-map-teaser__pin--c"></span>
    </div>
    <div class="cag-map-teaser__shade"></div>

    @if($isButton)
        <button
            type="button"
            @if($id) id="{{ $id }}" @endif
            class="cag-map-teaser__cta"
            data-bs-target="{{ $target }}"
            data-bs-toggle="modal"
        >
            <span class="cag-map-teaser__cta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span class="cag-map-teaser__cta-text">
                <span class="cag-map-teaser__cta-label">{{ $label }}</span>
                @if($resultCount !== null && (int) $resultCount > 0)
                    <span class="cag-map-teaser__cta-meta">
                        {{ $resultCount }} {{ (int) $resultCount === 1 ? translate('result') : translate('results') }}
                    </span>
                @endif
            </span>
        </button>
    @else
        <a
            @if($id) id="{{ $id }}" @endif
            class="cag-map-teaser__cta"
            data-bs-target="{{ $target }}"
            data-bs-toggle="modal"
            href="javascript:void(0)"
        >
            <span class="cag-map-teaser__cta-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span class="cag-map-teaser__cta-text">
                <span class="cag-map-teaser__cta-label">{{ $label }}</span>
                @if($resultCount !== null && (int) $resultCount > 0)
                    <span class="cag-map-teaser__cta-meta">
                        {{ $resultCount }} {{ (int) $resultCount === 1 ? translate('result') : translate('results') }}
                    </span>
                @endif
            </span>
        </a>
    @endif
</div>
