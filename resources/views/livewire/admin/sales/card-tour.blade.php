{{-- Tour card (spec §4.3). Shares $i, $card, $group, $t, $money with the builder view. --}}
@use('App\Enums\TourExtraUnit')
@php
    $product = $card['product'];
    $lines = collect($group['lines'] ?? [])->keyBy('key');
    $base = $lines->get($card['key']);
@endphp
<x-admin.sales-combo :label="$t('type_tour')" options-key="tour" :value="$card['listing_id']" method="selectProduct" :args="[$i]" wire:key="combo-{{ $card['key'] }}-{{ $card['listing_id'] }}" />

@if($product)
    <div class="sb-fixed">
        @if($product['duration'])<b>{{ $product['duration'] }}</b>@endif
        <b>{{ $t('max_persons', ['count' => $product['max']]) }}</b>
        @if($product['partner'])<b>{{ $t('guide') }}: {{ $product['partner']['name'] }}</b>@endif
    </div>

    <div class="sb-row">
        <label class="sb-field"><span class="sb-label">{{ $t('date') }}</span><input type="date" class="form-control form-control-sm" wire:model.live="cards.{{ $i }}.date"></label>
        <div class="sb-field">
            <span class="sb-label">{{ $t('persons') }}</span>
            <div class="sb-stepper">
                <button type="button" wire:click="stepPersons({{ $i }}, -1)" aria-label="{{ $t('one_less') }}">−</button>
                <input type="number" min="1" class="form-control form-control-sm" wire:model.live.debounce.300ms="cards.{{ $i }}.persons" aria-label="{{ $t('persons') }}">
                <button type="button" wire:click="stepPersons({{ $i }}, 1)" aria-label="{{ $t('one_more') }}">+</button>
            </div>
        </div>
    </div>

    @if($base)
        <div class="sb-calc"><span class="sb-formula">{{ $base['adjusted'] ? $t('adjusted_from', ['amount' => $money($base['calculated'])]) : $base['formula'] }}</span><span class="sb-amt">{{ $money($base['total']) }}</span></div>
    @endif

    <div class="sb-subsec">
        <div class="sb-subsec__head"><span>{{ $t('extras') }}</span>@if($product['extras'] !== [])<span class="sb-hint">{{ $t('extras_available', ['count' => count($product['extras'])]) }}</span>@endif</div>
        @forelse($product['extras'] as $extra)
            @php
                $unit = TourExtraUnit::fromListing($extra['unit']);
                $on = isset($card['extras'][$extra['key']]);
                $line = $lines->get($card['key'].'-x'.$extra['key']);
            @endphp
            <div @class(['sb-extra', 'is-on' => $on]) wire:key="extra-{{ $card['key'] }}-{{ $extra['key'] }}">
                <label class="sb-extra__label">
                    <input type="checkbox" @checked($on) wire:click="toggleExtra({{ $i }}, '{{ $extra['key'] }}')">
                    <span>{{ $extra['name'] }}<small>{{ $money($extra['price']) }} {{ $unit->label() }}</small></span>
                </label>
                @if($unit === TourExtraUnit::PerBooking)
                    <span class="sb-hint">× 1</span>
                @elseif($on)
                    <label class="sb-extra__qty">
                        <input type="number" min="0" class="form-control form-control-sm" wire:model.live.debounce.300ms="cards.{{ $i }}.extras.{{ $extra['key'] }}.qty" aria-label="{{ $unit === TourExtraUnit::PerPerson ? $t('persons') : $t('quantity') }}">
                        <small>{{ $unit === TourExtraUnit::PerPerson ? $t('pers') : $t('pcs') }}</small>
                    </label>
                @else
                    <span></span>
                @endif
                <span class="sb-amt">{{ $line ? $money($line['total']) : '' }}</span>
            </div>
        @empty
            <div class="sb-hint">{{ $t('no_extras') }}</div>
        @endforelse
    </div>

    <details class="sb-adjust" @if(filled($card['override'] ?? '')) open @endif>
        <summary>{{ $t('adjust_price') }}</summary>
        <div class="sb-adjust__row">
            <span class="sb-hint">{{ $t('tour_price') }} €</span>
            <input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.500ms="cards.{{ $i }}.override" placeholder="{{ $t('from_listing') }}">
        </div>
    </details>
@endif
