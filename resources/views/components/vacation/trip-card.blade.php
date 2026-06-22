@props(['card', 'variant' => 'grid'])

@if($variant === 'slider')
    @include('components.vacation.partials.slider-card', ['card' => $card, 'pillar' => 'trip'])
@else
    <x-vacation.product-card :card="$card" layout="grid" />
@endif
