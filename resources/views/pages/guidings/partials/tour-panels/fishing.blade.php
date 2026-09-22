{{-- Shared Tour Infos panel (tabs + accordion) --}}
@php
    $targetFishItems = !empty($guiding->target_fish) ? $guiding->getTargetFishNames() : [];
    $methodItems = !empty($guiding->fishing_methods) ? $guiding->getFishingMethodNames() : [];
    $waterItems = !empty($guiding->water_types) ? $guiding->getWaterNames() : [];
    $hasAnyTourInfo = !empty($targetFishItems) || !empty($methodItems) || !empty($waterItems);
@endphp

<div class="tour-panel">
    @if($hasAnyTourInfo)
        <div class="tour-panel__grid">
            @if(!empty($targetFishItems))
                <section class="tour-panel__group">
                    <h4 class="tour-panel__label">
                        <i class="fas fa-fish" aria-hidden="true"></i>
                        <span>@lang('guidings.Target_Fish')</span>
                    </h4>
                    <ul class="tour-panel__chips">
                        @foreach ($targetFishItems as $fish)
                            @if(is_array($fish) && isset($fish['name']))
                                <li class="tour-panel__chip">{{ $fish['name'] }}</li>
                            @endif
                        @endforeach
                    </ul>
                </section>
            @endif

            @if(!empty($methodItems))
                <section class="tour-panel__group">
                    <h4 class="tour-panel__label">
                        <i class="fas fa-bullseye" aria-hidden="true"></i>
                        <span>@lang('guidings.Fishing_Method')</span>
                    </h4>
                    <ul class="tour-panel__chips">
                        @foreach ($methodItems as $fishing_method)
                            @if(is_array($fishing_method) && isset($fishing_method['name']))
                                <li class="tour-panel__chip">{{ $fishing_method['name'] }}</li>
                            @endif
                        @endforeach
                    </ul>
                </section>
            @endif

            @if(!empty($waterItems))
                <section class="tour-panel__group">
                    <h4 class="tour-panel__label">
                        <i class="fas fa-water" aria-hidden="true"></i>
                        <span>@lang('guidings.Water_Type')</span>
                    </h4>
                    <ul class="tour-panel__chips">
                        @foreach ($waterItems as $water)
                            @if(is_array($water) && isset($water['name']))
                                <li class="tour-panel__chip">{{ $water['name'] }}</li>
                            @endif
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    @else
        <p class="tour-panel__empty">@lang('guidings.No_information_specified')</p>
    @endif
</div>
