<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.offer_sendout_title') }}</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f5; -webkit-font-smoothing: antialiased;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f4f5;">
    <tr>
        <td align="center" style="padding: 20px 16px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden;">
                {{-- Header --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 20px 24px; text-align: center;">
                        <a href="{{ route('welcome') }}" target="_blank" style="text-decoration: none; display: inline-block; color: #ffffff;">
                            <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="{{ config('app.name') }}" style="max-width: 160px; height: auto; display: block; margin: 0 auto 12px;" onerror="this.style.display='none';">
                        </a>
                        <h1 style="margin: 12px 0 0; color: #e5e7eb; font-size: 16px; font-weight: 600; letter-spacing: 0.3px;">{{ __('emails.offer_sendout_title') }}</h1>
                    </td>
                </tr>
                {{-- Greeting --}}
                <tr>
                    <td style="padding: 16px 20px 12px;">
                        <p style="margin: 0 0 4px; font-size: 14px; color: #0f172a; font-weight: 600;">{{ __('emails.dear') }} {{ $recipient_name }},</p>
                        <p style="margin: 0; font-size: 12px; color: #475569; line-height: 1.5;">{{ __('emails.offer_sendout_intro') }} {{ __('emails.offer_sendout_intro_secondary') }}</p>
                    </td>
                </tr>
                {{-- Catalog: one block per offer --}}
                @foreach($offers ?? [] as $index => $offer)
                <tr>
                    <td style="padding: 0 20px {{ $loop->last ? '16px' : '10px' }};">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border: 1px solid #e5e7eb; border-radius: 6px; background-color: #fafafa; margin-bottom: {{ $loop->last ? 0 : 10 }}px;">
                            <tr>
                                <td style="padding: 0;">
                                    {{-- Camp as highlight (hero of this offer) --}}
                                    @if(!empty($offer['camp']))
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border-radius: 6px 6px 0 0;">
                                        <tr>
                                            <td style="padding: 12px 14px;">
                                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td style="vertical-align: top;">
                                                            <h2 style="margin: 0 0 4px; font-size: 16px; font-weight: 700; color: #ffffff; letter-spacing: 0.2px; line-height: 1.3;">
                                                                <a href="{{ route('vacations.show', $offer['camp']->slug) }}" target="_blank" style="color: #ffffff; text-decoration: none;">{{ $offer['camp']->title }}</a>
                                                            </h2>
                                                            @if(!empty($offer['camp_location']))
                                                            <p style="margin: 0; font-size: 11px; color: #cbd5e1; line-height: 1.35;">
                                                                <span style="display: inline-block; margin-right: 4px;">📍</span>{{ $offer['camp_location'] }}
                                                            </p>
                                                            @endif
                                                        </td>
                                                        @php
                                                            $displayPrice = !empty($offer['component_total']) ? $offer['component_total'] : ($offer['price'] ?? null);
                                                        @endphp
                                                        @if($displayPrice !== null && $displayPrice !== '')
                                                        <td style="vertical-align: top; text-align: right; white-space: nowrap; padding-left: 12px;">
                                                            <div style="background: rgba(232, 96, 76, 0.15); border: 1px solid rgba(232, 96, 76, 0.3); border-radius: 4px; padding: 6px 10px; display: inline-block;">
                                                                <div style="font-size: 16px; color: #ffffff; font-weight: 700; line-height: 1;">€ {{ is_numeric($displayPrice) ? number_format((float)$displayPrice, 2) : $displayPrice }}</div>
                                                            </div>
                                                        </td>
                                                        @endif
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    @else
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; border-radius: 6px 6px 0 0;">
                                        <tr>
                                            <td style="padding: 8px 14px;">
                                                <h2 style="margin: 0; font-size: 13px; font-weight: 600; color: #334155;">{{ __('emails.offer_sendout_offer') }} {{ $index + 1 }}</h2>
                                            </td>
                                        </tr>
                                    </table>
                                    @endif
                                    {{-- Offer details (compact) --}}
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding: 10px 14px 12px;">
                                        <tr>
                                            <td>
                                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px; color: #111827;">
                                                    @if(isset($offer['accommodation_items']) && count($offer['accommodation_items']) > 0)
                                                    <tr>
                                                        <td style="padding: 4px 8px; font-size: 9px; color: #475569; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; background: #f1f5f9; border-radius: 4px;">{{ __('emails.offer_sendout_accommodations') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0 0 8px;">
                                                            @foreach($offer['accommodation_items'] as $item)
                                                            @php
                                                                $acc = $item['model'] ?? null;
                                                                $campUrl = (!empty($offer['camp']) && !empty($offer['camp']->slug)) ? route('vacations.show', $offer['camp']->slug) : route('welcome');
                                                                $accThumb = !empty($acc->thumbnail_path) ? (strpos($acc->thumbnail_path, 'http') === 0 ? $acc->thumbnail_path : asset(ltrim($acc->thumbnail_path, '/'))) : null;
                                                            @endphp
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 6px;">
                                                                <tr>
                                                                    @if($accThumb)
                                                                    <td style="width: 100px; vertical-align: top; padding: 0; border-radius: 6px 0 0 6px; overflow: hidden;">
                                                                        <img src="{{ $accThumb }}" alt="{{ $item['title'] ?? $acc->title ?? '' }}" width="100" height="90" style="width: 100px; height: 90px; object-fit: cover; display: block;" />
                                                                    </td>
                                                                    @endif
                                                                    <td style="padding: 8px 10px; vertical-align: top;">
                                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td style="vertical-align: top;">
                                                                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 1px;">{{ $item['title'] ?? $acc->title ?? '' }}</div>
                                                                                    <div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">{{ translate($acc->accommodationType?->name ?? 'Apartment / Holiday Home') }}</div>
                                                                                    <div style="margin-bottom: 3px;">
                                                                                        @if(!empty($acc->living_area_sqm))<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">📐 {{ $acc->living_area_sqm }} m²</span>@endif
                                                                                    </div>
                                                                                    @php
                                                                                        $bedSummary = null;
                                                                                        if (!empty($acc->room_configurations) && is_array($acc->room_configurations)) {
                                                                                            $parts = [];
                                                                                            foreach ($acc->room_configurations as $rc) {
                                                                                                $val = is_array($rc) ? ($rc['value'] ?? $rc['name'] ?? null) : $rc;
                                                                                                if ($val) $parts[] = $val;
                                                                                            }
                                                                                            $bedSummary = !empty($parts) ? implode(', ', $parts) : null;
                                                                                        }
                                                                                    @endphp
                                                                                    @if($bedSummary)<div style="font-size: 11px; color: #334155; margin-bottom: 3px;">🛏️ {{ __('accommodations.bedrooms') }}: {{ translate($bedSummary) }}</div>@endif
                                                                                    <div style="margin-bottom: 4px;">
                                                                                        @if(!empty($acc->distance_to_water_m))<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">💧 Water: {{ is_numeric($acc->distance_to_water_m) ? $acc->distance_to_water_m . 'm' : translate($acc->distance_to_water_m) }}</span>@endif
                                                                                        @if(!empty($acc->distance_to_boat_berth_m))<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">🚤 {{ is_numeric($acc->distance_to_boat_berth_m) ? $acc->distance_to_boat_berth_m . 'm' : translate($acc->distance_to_boat_berth_m) }}</span>@endif
                                                                                        @if(!empty($acc->distance_to_parking_m))<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">🚗 Parking: {{ is_numeric($acc->distance_to_parking_m) ? $acc->distance_to_parking_m . 'm' : translate($acc->distance_to_parking_m) }}</span>@endif
                                                                                    </div>
                                                                                </td>
                                                                                <td style="vertical-align: top; text-align: right; white-space: nowrap; padding-left: 10px;">
                                                                                    <div style="font-size: 9px; color: #64748b;">Per Day</div>
                                                                                    <div style="font-size: 14px; font-weight: 700; color: #1e293b;">€{{ number_format((float)($item['price'] ?? 0), 2) }}</div>
                                                                                    <a href="{{ $campUrl }}" target="_blank" style="display: block; margin-top: 4px; font-size: 11px; color: #2563eb; font-weight: 600; text-decoration: none;">{{ __('Show More') }} ▼</a>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @elseif(isset($offer['accommodations']) && $offer['accommodations']->isNotEmpty())
                                                    <tr>
                                                        <td style="padding: 4px 8px; font-size: 9px; color: #475569; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; background: #f1f5f9; border-radius: 4px;">{{ __('emails.offer_sendout_accommodations') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0 0 8px;">
                                                            @foreach($offer['accommodations'] as $acc)
                                                            @php
                                                                $campUrl = (!empty($offer['camp']) && !empty($offer['camp']->slug)) ? route('vacations.show', $offer['camp']->slug) : route('welcome');
                                                                $accThumb = !empty($acc->thumbnail_path) ? (strpos($acc->thumbnail_path, 'http') === 0 ? $acc->thumbnail_path : asset(ltrim($acc->thumbnail_path, '/'))) : null;
                                                            @endphp
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 6px;">
                                                                <tr>
                                                                    @if($accThumb)
                                                                    <td style="width: 100px; vertical-align: top; padding: 0; border-radius: 6px 0 0 6px; overflow: hidden;">
                                                                        <img src="{{ $accThumb }}" alt="{{ $acc->title }}" width="100" height="90" style="width: 100px; height: 90px; object-fit: cover; display: block;" />
                                                                    </td>
                                                                    @endif
                                                                    <td style="padding: 8px 10px; vertical-align: top;">
                                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td style="vertical-align: top;">
                                                                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $acc->title }}</div>
                                                                                    <div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">{{ translate($acc->accommodationType?->name ?? 'Apartment / Holiday Home') }}</div>
                                                                                    <div style="margin-bottom: 4px;">@if(!empty($acc->living_area_sqm))<span style="font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">📐 {{ $acc->living_area_sqm }} m²</span>@endif @if(!empty($acc->distance_to_water_m))<span style="font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">💧 Water: {{ $acc->distance_to_water_m }}m</span>@endif @if(!empty($acc->distance_to_boat_berth_m))<span style="font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">🚤 {{ $acc->distance_to_boat_berth_m }}m</span>@endif @if(!empty($acc->distance_to_parking_m))<span style="font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px;">🚗 Parking: {{ $acc->distance_to_parking_m }}m</span>@endif</div>
                                                                                </td>
                                                                                <td style="vertical-align: top; text-align: right; white-space: nowrap; padding-left: 10px;">
                                                                                    <a href="{{ $campUrl }}" target="_blank" style="font-size: 11px; color: #2563eb; font-weight: 600; text-decoration: none;">{{ __('Show More') }} ▼</a>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @if(isset($offer['boat_items']) && count($offer['boat_items']) > 0)
                                                    <tr>
                                                        <td style="padding: 4px 8px; font-size: 9px; color: #475569; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; background: #f1f5f9; border-radius: 4px;">{{ __('emails.offer_sendout_boats') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0 0 8px;">
                                                            @foreach($offer['boat_items'] as $item)
                                                            @php
                                                                $boat = $item['model'] ?? null;
                                                                $boatCampUrl = (!empty($offer['camp']) && !empty($offer['camp']->slug)) ? route('vacations.show', $offer['camp']->slug) : route('welcome');
                                                                $boatSpecs = $boat ? (is_array($boat->boat_information) ? $boat->boat_information : []) : [];
                                                                $boatInclusives = $boat ? (is_array($boat->inclusions) ? $boat->inclusions : []) : [];
                                                                $boatThumb = $boat && !empty($boat->thumbnail_path) ? (strpos($boat->thumbnail_path, 'http') === 0 ? $boat->thumbnail_path : asset(ltrim($boat->thumbnail_path, '/'))) : null;
                                                            @endphp
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 6px;">
                                                                <tr>
                                                                    @if($boatThumb)
                                                                    <td style="width: 100px; vertical-align: top; padding: 0; border-radius: 6px 0 0 6px; overflow: hidden;">
                                                                        <img src="{{ $boatThumb }}" alt="{{ $item['title'] ?? $boat->title ?? '' }}" width="100" height="90" style="width: 100px; height: 90px; object-fit: cover; display: block;" />
                                                                    </td>
                                                                    @endif
                                                                    <td style="padding: 8px 10px; vertical-align: top;">
                                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td style="vertical-align: top;">
                                                                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 1px;">{{ $item['title'] ?? $boat->title ?? '' }}</div>
                                                                                    @if($boat->boatType?->name)<div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">{{ translate($boat->boatType->name) }}</div>@endif
                                                                                    @if(count($boatSpecs) > 0)<div style="margin-bottom: 4px;">@foreach(array_slice($boatSpecs, 0, 3) as $spec)@php $spec = is_array($spec) ? $spec : ['name' => '', 'label' => '', 'value' => $spec]; $specLabel = $spec['name'] ?? $spec['label'] ?? ''; @endphp<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">{{ translate($specLabel) }}: {{ translate($spec['value'] ?? '') }}</span>@endforeach</div>@endif
                                                                                    @if(count($boatInclusives) > 0)<div style="font-size: 9px; color: #64748b; font-weight: 600; margin-bottom: 3px; text-transform: uppercase;">{{ __('Included in the price') }}</div><div style="margin-bottom: 4px;">@foreach(array_slice($boatInclusives, 0, 3) as $inc)<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">✓ {{ translate(is_array($inc) ? ($inc['name'] ?? $inc['value'] ?? '') : $inc) }}</span>@endforeach</div>@endif
                                                                                </td>
                                                                                <td style="vertical-align: top; text-align: right; white-space: nowrap; padding-left: 10px;">
                                                                                    <div style="font-size: 9px; color: #64748b;">{{ __('per day') }}</div>
                                                                                    <div style="font-size: 14px; font-weight: 700; color: #1e293b;">€{{ number_format((float)($item['price'] ?? 0), 2) }}</div>
                                                                                    <a href="{{ $boatCampUrl }}" target="_blank" style="display: block; margin-top: 4px; font-size: 11px; color: #2563eb; font-weight: 600; text-decoration: none;">{{ __('Show More') }} ▼</a>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @elseif(isset($offer['boats']) && $offer['boats']->isNotEmpty())
                                                    <tr>
                                                        <td style="padding: 4px 8px; font-size: 9px; color: #475569; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; background: #f1f5f9; border-radius: 4px;">{{ __('emails.offer_sendout_boats') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0 0 8px;">
                                                            @foreach($offer['boats'] as $boat)
                                                            @php
                                                                $boatCampUrl = (!empty($offer['camp']) && !empty($offer['camp']->slug)) ? route('vacations.show', $offer['camp']->slug) : route('welcome');
                                                                $boatSpecs = is_array($boat->boat_information ?? null) ? $boat->boat_information : [];
                                                                $boatInclusives = is_array($boat->inclusions ?? null) ? $boat->inclusions : [];
                                                                $boatThumb = !empty($boat->thumbnail_path) ? (strpos($boat->thumbnail_path, 'http') === 0 ? $boat->thumbnail_path : asset(ltrim($boat->thumbnail_path, '/'))) : null;
                                                            @endphp
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 6px;">
                                                                <tr>
                                                                    @if($boatThumb)
                                                                    <td style="width: 100px; vertical-align: top; padding: 0; border-radius: 6px 0 0 6px; overflow: hidden;">
                                                                        <img src="{{ $boatThumb }}" alt="{{ $boat->title }}" width="100" height="90" style="width: 100px; height: 90px; object-fit: cover; display: block;" />
                                                                    </td>
                                                                    @endif
                                                                    <td style="padding: 8px 10px; vertical-align: top;">
                                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td style="vertical-align: top;">
                                                                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $boat->title }}</div>
                                                                                    @if($boat->boatType?->name)<div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">{{ translate($boat->boatType->name) }}</div>@endif
                                                                                    @if(count($boatSpecs) > 0)<div style="margin-bottom: 4px;">@foreach(array_slice($boatSpecs, 0, 3) as $spec)@php $spec = is_array($spec) ? $spec : ['name' => '', 'label' => '', 'value' => $spec]; $specLabel = $spec['name'] ?? $spec['label'] ?? ''; @endphp<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">{{ translate($specLabel) }}: {{ translate($spec['value'] ?? '') }}</span>@endforeach</div>@endif
                                                                                    @if(count($boatInclusives) > 0)<div style="font-size: 9px; color: #64748b; font-weight: 600; margin-bottom: 3px; text-transform: uppercase;">{{ __('Included in the price') }}</div><div>@foreach(array_slice($boatInclusives, 0, 3) as $inc)<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">✓ {{ translate(is_array($inc) ? ($inc['name'] ?? $inc['value'] ?? '') : $inc) }}</span>@endforeach</div>@endif
                                                                                </td>
                                                                                <td style="vertical-align: top; text-align: right; white-space: nowrap; padding-left: 10px;">
                                                                                    <a href="{{ $boatCampUrl }}" target="_blank" style="font-size: 11px; color: #2563eb; font-weight: 600; text-decoration: none;">{{ __('Show More') }} ▼</a>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @if(isset($offer['guiding_items']) && count($offer['guiding_items']) > 0)
                                                    <tr>
                                                        <td style="padding: 4px 8px; font-size: 9px; color: #475569; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; background: #f1f5f9; border-radius: 4px;">{{ __('emails.offer_sendout_guidings') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0 0 8px;">
                                                            @foreach($offer['guiding_items'] as $item)
                                                            @php
                                                                $g = $item['model'] ?? null;
                                                                $guidingUrl = $g ? route('guidings.show', [$g->id, $g->slug]) : route('guidings.index');
                                                                $gDuration = $g->duration_hours ?? $g->duration ?? null;
                                                                $gType = $g->fishing_from ?? $g->type ?? ($g->fishing_type ?? 'Shore');
                                                                $gThumb = $g && !empty($g->thumbnail_path) ? (strpos($g->thumbnail_path, 'http') === 0 ? $g->thumbnail_path : asset(ltrim($g->thumbnail_path, '/'))) : null;
                                                            @endphp
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 6px;">
                                                                <tr>
                                                                    @if($gThumb)
                                                                    <td style="width: 100px; vertical-align: top; padding: 0; border-radius: 6px 0 0 6px; overflow: hidden;">
                                                                        <img src="{{ $gThumb }}" alt="{{ $item['title'] ?? $g->title ?? '' }}" width="100" height="90" style="width: 100px; height: 90px; object-fit: cover; display: block;" />
                                                                    </td>
                                                                    @endif
                                                                    <td style="padding: 8px 10px; vertical-align: top;">
                                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td style="vertical-align: top;">
                                                                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 1px;">{{ $item['title'] ?? $g->title ?? '' }}</div>
                                                                                    @if(!empty($g->description))<div style="font-size: 11px; color: #64748b; margin-bottom: 4px; line-height: 1.35;">{{ Str::limit(strip_tags($g->description), 70) }}</div>@endif
                                                                                    <div style="margin-bottom: 4px;">
                                                                                        @if($gDuration)<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">🕐 {{ is_numeric($gDuration) ? $gDuration . ' h' : $gDuration }}</span>@endif
                                                                                        @if(!empty($g->max_guests) || !empty($g->max_persons))<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">👥 {{ $g->max_guests ?? $g->max_persons ?? '' }} Pers</span>@endif
                                                                                        <span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px;">🚤 {{ translate($gType) }}</span>
                                                                                    </div>
                                                                                </td>
                                                                                <td style="vertical-align: top; text-align: right; white-space: nowrap; padding-left: 10px;">
                                                                                    <div style="font-size: 9px; color: #64748b;">{{ $g && $g->price_type ? translate(str_replace('_', ' ', $g->price_type)) : 'per tour' }}</div>
                                                                                    <div style="font-size: 14px; font-weight: 700; color: #1e293b;">€{{ number_format((float)($item['price'] ?? $g->price ?? 0), 2) }}</div>
                                                                                    <a href="{{ $guidingUrl }}" target="_blank" style="display: block; margin-top: 4px; font-size: 11px; color: #2563eb; font-weight: 600; text-decoration: none;">{{ __('Show More') }} ▼</a>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @elseif(isset($offer['guidings']) && $offer['guidings']->isNotEmpty())
                                                    <tr>
                                                        <td style="padding: 4px 8px; font-size: 9px; color: #475569; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600; background: #f1f5f9; border-radius: 4px;">{{ __('emails.offer_sendout_guidings') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 0 0 8px;">
                                                            @foreach($offer['guidings'] as $guiding)
                                                            @php
                                                                $guidingUrl = route('guidings.show', [$guiding->id, $guiding->slug]);
                                                                $gDuration = $guiding->duration_hours ?? $guiding->duration ?? null;
                                                                $gType = $guiding->fishing_from ?? $guiding->type ?? ($guiding->fishing_type ?? 'Shore');
                                                                $gPrice = is_numeric($guiding->price ?? null) ? (float)$guiding->price : (is_array($guiding->price ?? null) ? ($guiding->price['amount'] ?? 0) : 0);
                                                                $guidingThumb = !empty($guiding->thumbnail_path) ? (strpos($guiding->thumbnail_path, 'http') === 0 ? $guiding->thumbnail_path : asset(ltrim($guiding->thumbnail_path, '/'))) : null;
                                                            @endphp
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 6px;">
                                                                <tr>
                                                                    @if($guidingThumb)
                                                                    <td style="width: 100px; vertical-align: top; padding: 0; border-radius: 6px 0 0 6px; overflow: hidden;">
                                                                        <img src="{{ $guidingThumb }}" alt="{{ $guiding->title }}" width="100" height="90" style="width: 100px; height: 90px; object-fit: cover; display: block;" />
                                                                    </td>
                                                                    @endif
                                                                    <td style="padding: 8px 10px; vertical-align: top;">
                                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                                            <tr>
                                                                                <td style="vertical-align: top;">
                                                                                    <div style="font-size: 13px; font-weight: 600; color: #1e293b;">{{ $guiding->title }}</div>
                                                                                    @if(!empty($guiding->description))<div style="font-size: 11px; color: #64748b; margin-bottom: 4px; line-height: 1.35;">{{ Str::limit(strip_tags($guiding->description), 70) }}</div>@endif
                                                                                    <div style="margin-bottom: 4px;">
                                                                                        @if($gDuration)<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">🕐 {{ is_numeric($gDuration) ? $gDuration . ' h' : $gDuration }}</span>@endif
                                                                                        @if(!empty($guiding->max_guests) || !empty($guiding->max_persons))<span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px; margin-right: 3px;">👥 {{ $guiding->max_guests ?? $guiding->max_persons ?? '' }} Pers</span>@endif
                                                                                        <span style="display: inline-block; font-size: 10px; color: #64748b; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px; padding: 2px 6px;">🚤 {{ translate($gType) }}</span>
                                                                                    </div>
                                                                                </td>
                                                                                <td style="vertical-align: top; text-align: right; white-space: nowrap; padding-left: 10px;">
                                                                                    <div style="font-size: 9px; color: #64748b;">{{ $guiding->price_type ? translate(str_replace('_', ' ', $guiding->price_type)) : 'per tour' }}</div>
                                                                                    <div style="font-size: 14px; font-weight: 700; color: #1e293b;">€{{ number_format((float)$gPrice, 2) }}</div>
                                                                                    <a href="{{ $guidingUrl }}" target="_blank" style="display: block; margin-top: 4px; font-size: 11px; color: #2563eb; font-weight: 600; text-decoration: none;">{{ __('Show More') }} ▼</a>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    @if(!empty($offer['component_total']) && $offer['component_total'] > 0)
                                                    <tr>
                                                        <td style="padding: 8px 0 4px; border-top: 1px solid #e5e7eb;">
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                                <tr>
                                                                    <td style="font-size: 12px; color: #0f172a; font-weight: 700;">{{ __('emails.offer_sendout_total_price') }}</td>
                                                                    <td style="text-align: right; font-size: 14px; color: #0f172a; font-weight: 700;">€ {{ number_format((float)$offer['component_total'], 2) }}</td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                    {{-- Booking summary --}}
                                                    @if(!empty($offer['date_from']) || !empty($offer['date_to']) || !empty($offer['number_of_persons']) || !empty($offer['price']) || !empty($offer['additional_info']))
                                                    <tr>
                                                        <td style="padding: 6px 0 0;">
                                                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 4px;">
                                                                <tr>
                                                                    <td style="padding: 8px 10px;">
                                                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                                                            @if(!empty($offer['date_from']) || !empty($offer['date_to']))
                                                                            <tr>
                                                                                <td style="padding: 1px 0; vertical-align: top;">
                                                                                    <span style="font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.offer_sendout_dates') }}</span>
                                                                                    <div style="font-size: 11px; color: #0f172a; font-weight: 500; margin-top: 1px;">{{ $offer['date_from_formatted'] ?? '—' }}@if(!empty($offer['date_to'])) – {{ $offer['date_to_formatted'] ?? $offer['date_to'] }}@endif</div>
                                                                                </td>
                                                                                @if(!empty($offer['number_of_persons']))
                                                                                <td style="padding: 1px 0; vertical-align: top; text-align: right;">
                                                                                    <span style="font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.offer_sendout_number_of_persons') }}</span>
                                                                                    <div style="font-size: 11px; color: #0f172a; font-weight: 500; margin-top: 1px;">{{ $offer['number_of_persons'] }}</div>
                                                                                </td>
                                                                                @endif
                                                                            </tr>
                                                                            @elseif(!empty($offer['number_of_persons']))
                                                                            <tr>
                                                                                <td style="padding: 1px 0;">
                                                                                    <span style="font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.offer_sendout_number_of_persons') }}</span>
                                                                                    <div style="font-size: 11px; color: #0f172a; font-weight: 500; margin-top: 1px;">{{ $offer['number_of_persons'] }}</div>
                                                                                </td>
                                                                            </tr>
                                                                            @endif
                                                                            @if(!empty($offer['additional_info']))
                                                                            <tr>
                                                                                <td colspan="2" style="padding: 6px 0 0; border-top: 1px solid #e5e7eb;">
                                                                                    <span style="font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">{{ __('emails.offer_sendout_additional_info') }}</span>
                                                                                    <div style="font-size: 11px; color: #475569; line-height: 1.4; margin-top: 2px;">{!! nl2br(e($offer['additional_info'])) !!}</div>
                                                                                </td>
                                                                            </tr>
                                                                            @endif
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                @endforeach
                @if(!empty($free_text))
                <tr>
                    <td style="padding: 0 20px 12px;">
                        <div style="border-left: 3px solid #1a1a2e; padding: 8px 12px; background-color: #f8fafc; border-radius: 0 4px 4px 0;">
                            <p style="margin: 0; font-size: 12px; color: #475569; line-height: 1.45; font-style: italic;">{!! nl2br(e($free_text)) !!}</p>
                        </div>
                    </td>
                </tr>
                @endif
                {{-- Closing message --}}
                <tr>
                    <td style="padding: 0 20px 14px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background: #f0f9ff; border-radius: 4px; border: 1px solid #bae6fd;">
                            <tr>
                                <td style="padding: 10px 12px;">
                                    <p style="margin: 0 0 4px; font-size: 12px; color: #0c4a6e; font-weight: 600;">{{ __('emails.offer_sendout_closing_title') }}</p>
                                    <p style="margin: 0; font-size: 11px; color: #075985; line-height: 1.45;">{{ __('emails.offer_sendout_closing_message') }} {{ __('emails.offer_sendout_closing_secondary') }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                {{-- CTA --}}
                <tr>
                    <td style="padding: 0 20px 18px; text-align: center;">
                        <a href="{{ route('additional.contact') }}" target="_blank" style="display: inline-block; background-color: #e8604c; color: #ffffff !important; padding: 10px 24px; font-size: 13px; font-weight: 600; text-decoration: none; border-radius: 6px;">{{ __('emails.contact_us') }}</a>
                    </td>
                </tr>
                {{-- Footer --}}
                <tr>
                    <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 20px 24px; color: #ffffff; text-align: center;">
                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto; text-align: left;">
                            <tr>
                                {{-- Logo --}}
                                <td style="vertical-align: bottom; padding-right: 32px;">
                                    <img src="{{ asset('assets/images/logo/CatchAGuide2_Logo_PNG.png') }}" alt="{{ config('app.name') }}" style="max-width: 100px; height: auto; display: block;" onerror="this.style.display='none';">
                                    <span style="color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: 0.3px;">{{ config('app.name') }}</span>
                                </td>
                                {{-- Email (top), Phone (bottom) - bottom-aligned with logo block --}}
                                <td style="vertical-align: bottom; font-size: 13px; line-height: 1.6; color: #e5e7eb;">
                                    @if(config('mail.from.address'))
                                    <p style="margin: 0 0 4px;"><a href="mailto:{{ config('mail.from.address') }}" style="color: #ffffff; text-decoration: none;">{{ config('mail.from.address') }}</a></p>
                                    @endif
                                    @if(env('CONTACT_NUM'))
                                    <p style="margin: 0;"><a href="tel:{{ env('CONTACT_NUM') }}" style="color: #ffffff; text-decoration: none;">{{ env('CONTACT_NUM') }}</a></p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                        <p style="margin: 20px 0 0; color: #9ca3af; font-size: 12px;">© {{ date('Y') }} {{ config('app.name') }}</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
