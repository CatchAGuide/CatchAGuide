{{-- Custom line (spec §4.5): transfers, licences bought for the customer, special requests. --}}
@php($base = collect($group['lines'] ?? [])->firstWhere('key', $card['key']))
<div class="sb-row">
    <label class="sb-field"><span class="sb-label">{{ $t('title') }} *</span><input class="form-control form-control-sm" wire:model.live.debounce.400ms="cards.{{ $i }}.title"></label>
    <label class="sb-field"><span class="sb-label">{{ $t('date_optional') }}</span><input type="date" class="form-control form-control-sm" wire:model.live="cards.{{ $i }}.date"></label>
</div>
<label class="sb-field"><span class="sb-label">{{ $t('description') }}</span><input class="form-control form-control-sm" wire:model.live.debounce.400ms="cards.{{ $i }}.description"></label>
<div class="sb-row">
    <label class="sb-field sb-field--qty"><span class="sb-label">{{ $t('quantity') }}</span><input type="number" min="0" class="form-control form-control-sm" wire:model.live.debounce.300ms="cards.{{ $i }}.quantity"></label>
    <label class="sb-field"><span class="sb-label">{{ $t('unit_label') }}</span><input class="form-control form-control-sm" wire:model.live.debounce.400ms="cards.{{ $i }}.unit_label" placeholder="{{ $t('unit_label_placeholder') }}"></label>
    <label class="sb-field"><span class="sb-label">{{ $t('unit_price') }} €</span><input class="form-control form-control-sm" inputmode="decimal" wire:model.live.debounce.400ms="cards.{{ $i }}.unit_price"></label>
</div>
@if($base)
    <div class="sb-calc"><span class="sb-formula">{{ $base['formula'] }}</span><span class="sb-amt">{{ $money($base['total']) }}</span></div>
@endif
