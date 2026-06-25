@props(['pillar' => 'camp', 'badge' => null])

@php
    $label = $badge ?? ($pillar === 'trip'
        ? __('vacations.badge_trip')
        : __('vacations.badge_camp'));
@endphp

<span class="vacation-pillar-badge vacation-pillar-badge--{{ $pillar }}">{{ $label }}</span>
