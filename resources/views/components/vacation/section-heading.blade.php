@props([
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'linkUrl' => null,
    'linkLabel' => null,
])

<div class="vacation-section-heading">
    <div>
        @if($eyebrow)
            <span class="vacation-section-heading__eyebrow">{{ $eyebrow }}</span>
        @endif
        <h2 class="vacation-section-heading__title">{{ $title }}</h2>
        @if($subtitle)
            <p class="vacation-section-heading__subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if($linkUrl && $linkLabel)
        <a href="{{ $linkUrl }}" class="vacation-section-heading__link">{{ $linkLabel }} →</a>
    @endif
</div>
