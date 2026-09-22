{{-- Shared Additional Info panel (tabs + accordion) --}}
@php
    $panelRequirements = collect($guiding->requirements);
    $panelOtherInformation = collect($guiding->other_information);
    $panelRecommendations = collect($guiding->recommendations);
    $hasDetails = !empty($guiding->style_of_fishing) || !empty($guiding->tour_type);
    $hasAnyInfo = !$panelRequirements->isEmpty()
        || !$panelOtherInformation->isEmpty()
        || !$panelRecommendations->isEmpty()
        || $hasDetails;
@endphp

<div class="tour-panel">
    @if(!$panelRequirements->isEmpty())
        <section class="tour-panel__group">
            <h4 class="tour-panel__label">
                <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                <span>@lang('guidings.Requirements')</span>
            </h4>
            <ul class="tour-panel__kv-list">
                @foreach ($panelRequirements as $requirement)
                    <li class="tour-panel__kv">
                        <strong>{{ $requirement['name'] ?? '' }}</strong>
                        <span>{{ $requirement['value'] ?? '' }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if(!$panelOtherInformation->isEmpty())
        <section class="tour-panel__group">
            <h4 class="tour-panel__label">
                <i class="fas fa-list-ul" aria-hidden="true"></i>
                <span>@lang('guidings.Other_Info')</span>
            </h4>
            <ul class="tour-panel__kv-list">
                @foreach ($panelOtherInformation as $other)
                    <li class="tour-panel__kv">
                        <strong>{{ $other['name'] ?? '' }}</strong>
                        <span>{{ $other['value'] ?? '' }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if(!$panelRecommendations->isEmpty())
        <section class="tour-panel__group">
            <h4 class="tour-panel__label">
                <i class="fas fa-suitcase" aria-hidden="true"></i>
                <span>@lang('guidings.Reco_Prep')</span>
            </h4>
            <ul class="tour-panel__kv-list">
                @foreach ($panelRecommendations as $recommendation)
                    <li class="tour-panel__kv">
                        <strong>{{ $recommendation['name'] ?? '' }}</strong>
                        <span>{{ $recommendation['value'] ?? '' }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if($hasDetails)
        <section class="tour-panel__group tour-panel__group--meta">
            <div class="tour-panel__meta-grid">
                @if(!empty($guiding->style_of_fishing))
                    <div class="tour-panel__meta">
                        <span class="tour-panel__meta-label">@lang('guidings.Style_Fishing')</span>
                        <span class="tour-panel__meta-value">{{ $guiding->style_of_fishing }}</span>
                    </div>
                @endif
                @if(!empty($guiding->tour_type))
                    <div class="tour-panel__meta">
                        <span class="tour-panel__meta-label">@lang('guidings.Tour_Type')</span>
                        <span class="tour-panel__meta-value">{{ $guiding->tour_type }}</span>
                    </div>
                @endif
            </div>
        </section>
    @endif

    @unless($hasAnyInfo)
        <p class="tour-panel__empty">@lang('guidings.No_information_specified')</p>
    @endunless
</div>
