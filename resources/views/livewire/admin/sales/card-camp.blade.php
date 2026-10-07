{{-- Camp card (spec §4.4): persons, then accommodations, rental boats and guidings. --}}
@php
    $product = $card['product'];
    $lines = collect($group['lines'] ?? [])->keyBy('key');
    $optionList = fn (array $options) => array_values(array_map(fn (array $option) => ['id' => (int) $option['id'], 'label' => '#'.$option['id'].' · '.$option['name']], $options));
    $sections = [
        'accommodation' => ['label' => $t('accommodation'), 'options' => $product['accommodations'] ?? []],
        'boat' => ['label' => $t('rental_boats'), 'options' => $product['boats'] ?? []],
        'guiding' => ['label' => $t('guidings'), 'options' => $product['guidings'] ?? []],
    ];
@endphp
<x-admin.sales-combo :label="$t('type_camp')" options-key="camp" :value="$card['listing_id']" method="selectProduct" :args="[$i]" wire:key="combo-{{ $card['key'] }}-{{ $card['listing_id'] }}" />

@if($product)
    @if($product['partner'])
        <div class="sb-fixed">{{ $t('host') }}: <b>{{ $product['partner']['name'] }}</b></div>
    @endif

    <div class="sb-field sb-field--narrow">
        <span class="sb-label">{{ $t('persons') }}</span>
        <div class="sb-stepper">
            <button type="button" wire:click="stepPersons({{ $i }}, -1)" aria-label="{{ $t('one_less') }}">−</button>
            <input type="number" min="1" class="form-control form-control-sm" wire:model.live.debounce.300ms="cards.{{ $i }}.persons" aria-label="{{ $t('persons') }}">
            <button type="button" wire:click="stepPersons({{ $i }}, 1)" aria-label="{{ $t('one_more') }}">+</button>
        </div>
    </div>

    @foreach($sections as $kind => $section)
        <div class="sb-subsec">
            <div class="sb-subsec__head">
                <span>{{ $section['label'] }}</span>
                @if($section['options'] !== [])
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="addSub({{ $i }}, '{{ $kind }}')">+ {{ $t('add') }}</button>
                @else
                    <span class="sb-hint">{{ $t('none_in_listing') }}</span>
                @endif
            </div>
            @foreach($card['subs'] as $s => $sub)
                @continue($sub['kind'] !== $kind)
                @php($line = $lines->get($sub['key']))
                <div class="sb-sub" wire:key="sub-{{ $sub['key'] }}">
                    @if($kind === 'accommodation')
                        <div class="sb-row">
                            <x-admin.sales-combo class="sb-field" :label="$t('accommodation')" :options="$optionList($section['options'])" :value="(int) $sub['option_id']" method="selectOption" :args="[$i, $s]" wire:key="combo-{{ $sub['key'] }}-{{ $sub['option_id'] }}" />
                            <label class="sb-field sb-field--qty"><span class="sb-label">{{ $t('quantity') }}</span><input type="number" min="1" class="form-control form-control-sm" wire:model.live.debounce.300ms="cards.{{ $i }}.subs.{{ $s }}.qty"></label>
                        </div>
                        <div class="sb-row">
                            <label class="sb-field"><span class="sb-label">{{ $t('check_in') }}</span><input type="date" class="form-control form-control-sm" wire:model.live="cards.{{ $i }}.subs.{{ $s }}.from"></label>
                            <label class="sb-field"><span class="sb-label">{{ $t('check_out') }}</span><input type="date" class="form-control form-control-sm" wire:model.live="cards.{{ $i }}.subs.{{ $s }}.to"></label>
                        </div>
                    @elseif($kind === 'boat')
                        <div class="sb-row">
                            <x-admin.sales-combo class="sb-field" :label="$t('boat')" :options="$optionList($section['options'])" :value="(int) $sub['option_id']" method="selectOption" :args="[$i, $s]" wire:key="combo-{{ $sub['key'] }}-{{ $sub['option_id'] }}" />
                            <label class="sb-field sb-field--qty"><span class="sb-label">{{ $t('number_of_days') }}</span><input type="number" min="1" class="form-control form-control-sm" wire:model.live.debounce.300ms="cards.{{ $i }}.subs.{{ $s }}.days"></label>
                            <label class="sb-field sb-field--qty"><span class="sb-label">{{ $t('number_of_boats') }}</span><input type="number" min="1" class="form-control form-control-sm" wire:model.live.debounce.300ms="cards.{{ $i }}.subs.{{ $s }}.qty"></label>
                        </div>
                    @else
                        <div class="sb-row">
                            <x-admin.sales-combo class="sb-field" :label="$t('guiding')" :options="$optionList($section['options'])" :value="(int) $sub['option_id']" method="selectOption" :args="[$i, $s]" wire:key="combo-{{ $sub['key'] }}-{{ $sub['option_id'] }}" />
                            <label class="sb-field"><span class="sb-label">{{ $t('date') }}</span><input type="date" class="form-control form-control-sm" wire:model.live="cards.{{ $i }}.subs.{{ $s }}.date"></label>
                            <label class="sb-field sb-field--qty"><span class="sb-label">{{ $t('number_of_guidings') }}</span><input type="number" min="1" class="form-control form-control-sm" wire:model.live.debounce.300ms="cards.{{ $i }}.subs.{{ $s }}.qty"></label>
                        </div>
                    @endif
                    <div class="sb-calc">
                        <span class="sb-formula">{{ $line ? ($line['adjusted'] ? $t('adjusted_from', ['amount' => $money($line['calculated'])]) : $line['formula']) : '' }}</span>
                        <span class="sb-override">
                            <span class="sb-hint">{{ $t('price') }}</span>
                            <input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.500ms="cards.{{ $i }}.subs.{{ $s }}.override" placeholder="{{ $t('auto') }}" aria-label="{{ $t('adjust_price') }}">
                            <span class="sb-amt">{{ $line ? $money($line['total']) : '' }}</span>
                        </span>
                    </div>
                    <button type="button" class="sb-x sb-sub__remove" wire:click="removeSub({{ $i }}, {{ $s }})">{{ $t('remove') }}</button>
                </div>
            @endforeach
        </div>
    @endforeach
@endif
