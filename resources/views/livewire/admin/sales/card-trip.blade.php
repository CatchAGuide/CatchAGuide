{{-- Trip card (spec §4.5): start date and persons; the end date follows from the fixed duration. --}}
@use('App\Services\Sales\SalesFormat')
@php
    $product = $card['product'];
    $base = collect($group['lines'] ?? [])->firstWhere('key', $card['key']);
@endphp
<x-admin.sales-combo :label="$t('type_trip')" options-key="trip" :value="$card['listing_id']" method="selectProduct" :args="[$i]" wire:key="combo-{{ $card['key'] }}-{{ $card['listing_id'] }}" />

@if($product)
    <div class="sb-fixed">
        {{ $t('fixed') }}:
        <b>{{ trans_choice('sales.nights', $product['nights'], ['count' => $product['nights']]) }}</b>
        <b>{{ $money($product['price']) }} {{ $t('per_person') }}</b>
        <b>{{ $t('persons_range', ['min' => $product['min'], 'max' => $product['max']]) }}</b>
        @if($product['partner'])<b>{{ $t('host') }}: {{ $product['partner']['name'] }}</b>@endif
    </div>
    <div class="sb-row">
        <label class="sb-field"><span class="sb-label">{{ $t('start_date') }}</span><input type="date" class="form-control form-control-sm" wire:model.live="cards.{{ $i }}.date"></label>
        <div class="sb-field">
            <span class="sb-label">{{ $t('persons') }}</span>
            <div class="sb-stepper">
                <button type="button" wire:click="stepPersons({{ $i }}, -1)" aria-label="{{ $t('one_less') }}">−</button>
                <input type="number" min="1" class="form-control form-control-sm" wire:model.live.debounce.300ms="cards.{{ $i }}.persons" aria-label="{{ $t('persons') }}">
                <button type="button" wire:click="stepPersons({{ $i }}, 1)" aria-label="{{ $t('one_more') }}">+</button>
            </div>
        </div>
    </div>
    @if($base && $base['to'])
        <div class="sb-hint">{{ $t('end_date_derived', ['date' => SalesFormat::date($base['to'], app()->getLocale())]) }}</div>
    @endif
    @if($base)
        <div class="sb-calc">
            <span class="sb-formula">{{ $base['adjusted'] ? $t('adjusted_from', ['amount' => $money($base['calculated'])]) : $base['formula'] }}</span>
            <span class="sb-amt">{{ $money($base['total']) }}</span>
        </div>
    @endif
    <details class="sb-adjust" @if(filled($card['override'] ?? '')) open @endif>
        <summary>{{ $t('adjust_price') }}</summary>
        <div class="sb-adjust__row">
            <span class="sb-hint">{{ $t('trip_price') }} €</span>
            <input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.500ms="cards.{{ $i }}.override" placeholder="{{ $t('from_listing') }}">
        </div>
    </details>
@endif
