{{-- Shared Inclusions + Extras panel (tabs + accordion) --}}
@php
    $inclusionItems = collect(!empty(decode_if_json($guiding->inclusions)) ? $guiding->getInclusionNames() : [])
        ->filter(fn ($inclusion) => is_array($inclusion) && filled(trim((string) ($inclusion['name'] ?? ''))))
        ->values();

    $pricingExtras = collect(decode_if_json($guiding->pricing_extra) ?: [])
        ->filter(fn ($extra) => is_array($extra) && filled(trim((string) ($extra['name'] ?? ''))))
        ->values();
@endphp

<div class="tour-panel">
    @if($inclusionItems->isNotEmpty())
        <section class="tour-panel__group">
            <h4 class="tour-panel__label">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                <span>@lang('guidings.Inclusions')</span>
            </h4>
            <ul class="tour-panel__checklist">
                @foreach ($inclusionItems as $inclusion)
                    <li class="tour-panel__check-item">
                        <i class="fas fa-check" aria-hidden="true"></i>
                        <span>{{ $inclusion['name'] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if($inclusionItems->isNotEmpty() && $pricingExtras->isNotEmpty())
        <hr class="tour-panel__divider">
    @endif

    @if($pricingExtras->isNotEmpty())
        <section class="tour-panel__group">
            <h4 class="tour-panel__label">
                <i class="fas fa-plus-circle" aria-hidden="true"></i>
                <span>@lang('guidings.Additional_Extra')</span>
            </h4>
            <div class="tour-panel__callout">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <small>{{ __('newguidings.pricing_extra_info_text') }}</small>
            </div>
            <ul class="tour-panel__extras">
                @foreach ($pricingExtras as $pricingExtra)
                    <li class="tour-panel__extra">
                        <span class="tour-panel__extra-name">{{ $pricingExtra['name'] }}</span>
                        <span class="tour-panel__extra-price">
                            {{ $pricingExtra['price'] }}€
                            <span class="tour-panel__extra-unit">{{ __('booking.per_person') }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
