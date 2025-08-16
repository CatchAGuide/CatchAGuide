@extends('layouts.app')

@section('title', __('message.booking-accepted'))

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <h2>@lang('message.booking-accepted')</h2>
                    <p>
                        @lang('message.booking-success')
                    </p>
                    <p>
                        @lang('message.booking-thankyou')
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
                        <a id="contact-footer" href="{{route('additional.contact')}}" style="color:var(--thm-primary)">@lang('message.cont')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
