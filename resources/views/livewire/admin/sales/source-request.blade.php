{{-- The camp/trip request this offer was created from: what the guest asked for, for reference. --}}
@use('App\Models\TripBooking')
@use('App\Services\Sales\SalesFormat')
@php
    $isTrip = $request instanceof TripBooking;
    $locale = app()->getLocale();
    $listUrl = route($isTrip ? 'admin.trip-bookings.index' : 'admin.camp-vacation-bookings.index');
    $period = $request->preferred_date ? SalesFormat::date($request->preferred_date->toDateString(), $locale) : null;
    if ($isTrip && $request->preferred_date_to) {
        $period .= ' – '.SalesFormat::date($request->preferred_date_to->toDateString(), $locale);
    }
@endphp
<section class="sb-panel sb-source">
    <div class="sb-source__head">
        <h2>{{ __($isTrip ? 'sales.request.from_trip' : 'sales.request.from_camp', ['id' => $request->id]) }}</h2>
        <a href="{{ $listUrl }}" class="sb-hint">{{ __('sales.request.to_list') }} ↗</a>
    </div>
    <div class="sb-fixed">
        @if($period)<b>{{ $isTrip && $request->preferred_date_to ? __('sales.request.window', ['period' => $period]) : __('sales.request.arrival', ['date' => $period]) }}</b>@endif
        @if(! $isTrip && $request->nights)<b>{{ trans_choice('sales.nights', $request->nights, ['count' => $request->nights]) }}</b>@endif
        @if($request->number_of_persons)<b>{{ trans_choice('sales.persons', $request->number_of_persons, ['count' => $request->number_of_persons]) }}</b>@endif
        @if($request->estimated_total !== null)<b>{{ __('sales.request.estimate', ['amount' => SalesFormat::money($request->estimated_total, $locale)]) }}</b>@endif
        <span>{{ __('sales.request.received', ['date' => $request->created_at?->format('d.m.Y H:i')]) }}</span>
    </div>
    @if($isTrip && $request->preferred_date_to)
        <div class="sb-hint-box">{{ __('sales.request.window_hint') }}</div>
    @endif
    @if(filled($request->message))
        <details class="sb-source__message">
            <summary>{{ __('sales.request.message') }}</summary>
            <p>{{ $request->message }}</p>
        </details>
    @endif
</section>
