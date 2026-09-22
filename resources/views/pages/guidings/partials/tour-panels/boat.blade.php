{{-- Shared Boat Details panel (tabs + accordion) --}}
@php
    $boatExtras = !empty(decode_if_json($guiding->boat_extras)) ? $guiding->getBoatExtras() : [];
@endphp

<div class="tour-panel">
    @if(!empty($guiding->additional_information))
        <section class="tour-panel__group">
            <h4 class="tour-panel__label">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <span>@lang('guidings.Other_boat_information')</span>
            </h4>
            <p class="tour-panel__text">{{ $guiding->additional_information }}</p>
        </section>
    @endif

    @if(!$boatInformation->isEmpty())
        <section class="tour-panel__group">
            <h4 class="tour-panel__label">
                <i class="fas fa-ship" aria-hidden="true"></i>
                <span>@lang('guidings.Boat')</span>
            </h4>
            <dl class="tour-panel__specs">
                @foreach($boatInformation as $value)
                    <div class="tour-panel__spec">
                        <dt>{{ $value['name'] }}</dt>
                        <dd>{{ $value['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif

    @if(!empty($boatExtras))
        <section class="tour-panel__group">
            <h4 class="tour-panel__label">
                <i class="fas fa-tools" aria-hidden="true"></i>
                <span>@lang('guidings.Boat_Extras')</span>
            </h4>
            <ul class="tour-panel__chips">
                @foreach($boatExtras as $extra)
                    @if(is_array($extra) && isset($extra['name']))
                        <li class="tour-panel__chip">{{ $extra['name'] }}</li>
                    @endif
                @endforeach
            </ul>
        </section>
    @endif
</div>
