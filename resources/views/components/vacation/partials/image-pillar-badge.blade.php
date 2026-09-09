@props(['pillar' => 'camp', 'badge' => null])

@php
    $label = $badge ?? match ($pillar) {
        'trip' => __('vacations.badge_trip'),
        'tour' => __('homepage.offer_type_tour'),
        default => __('vacations.badge_camp'),
    };
@endphp

<span class="vacation-pillar-badge vacation-pillar-badge--{{ $pillar }}">{{ $label }}</span>
