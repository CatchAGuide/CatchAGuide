@extends('layouts.app')


@if($booking->status == 'accepted')
    @if(app()->getLocale() == 'en')
       @section('title', 'Booking Already Accepted')
    @elseif(app()->getLocale() == 'de')
        @section('title', 'Booking Already Accepted')
    @endif
@endif
@if($booking->status == 'rejected')
    @if(app()->getLocale() == 'en')
        @section('title', 'Booking Already Rejected')
    @elseif(app()->getLocale() == 'de')
        @section('title', 'Booking Already Rejected')
    @endif
@endif
@if($booking->status == 'cancelled')
    @if(app()->getLocale() == 'en')
        @section('title', ' Booking Canceled')
    @elseif(app()->getLocale() == 'de')
        @section('title', ' Booking Canceled') 
    @endif
@endif

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <h2>
                        @if($booking->status == 'accepted')
                            @if(app()->getLocale() == 'en')
                                Booking Already Accepted
                            @elseif(app()->getLocale() == 'de')
                                Booking Already Accepted
                            @endif
                        @endif
                        @if($booking->status == 'rejected')
                            @if(app()->getLocale() == 'en')
                                Booking Already Rejected
                            @elseif(app()->getLocale() == 'de')
                                Booking Already Rejected
                            @endif
                        @endif
                        @if($booking->status == 'cancelled')
                            @if(app()->getLocale() == 'en')
                                Booking Canceled
                            @elseif(app()->getLocale() == 'de')
                                Booking Canceled
                            @endif
                        @endif
                    </h2>
                    <p>
                        @if($booking->status == 'accepted')
                            @if(app()->getLocale() == 'en')
                                We apologize, but the booking you are trying to {{$action}} has already been accepted. 
                            @elseif(app()->getLocale() == 'de')
                                We apologize, but the booking you are trying to {{$action}} has already been accepted. 
                            @endif
                        @endif
                        @if($booking->status == 'rejected')
                            @if(app()->getLocale() == 'en')
                                We apologize, but the booking you are trying to {{$action}} has already been rejected. 
                            @elseif(app()->getLocale() == 'de')
                                We apologize, but the booking you are trying to {{$action}} has already been rejected. 
                            @endif
                        @endif
                        @if($booking->status == 'cancelled')
                            @if(app()->getLocale() == 'en')
                                We apologize, but the booking you are trying to {{$action}} has already been cancelled. 
                            @elseif(app()->getLocale() == 'de')
                                We apologize, but the booking you are trying to {{$action}} has already been cancelled. 
                            @endif
                        @endif
                    </p>
                    <p class="text-muted">
                        @if(app()->getLocale() == 'en')
                            Contact us at any time if you have any questions about your booking or tour.
                        @elseif(app()->getLocale() == 'de')
                            Kontaktiere uns jederzeit, sollten Fragen zu Deiner Buchung oder Deiner Tour aufkommen.
                        @endif
                    </p>
                    <a href="{{route('welcome')}}" class="btn btn-orange">
                        @lang('message.back')
                    </a>
                    <div class="mt-3">
                        <a href="{{route('additional.contact')}}" style="color:var(--thm-primary)">@lang('message.cont')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
