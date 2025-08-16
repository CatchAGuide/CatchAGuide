@extends('layouts.app')

@section('title','Booking Rejected')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <h2>@lang('message.reject-header')</h2>
                    <p>@lang('message.reject-message')</p>
                    <a href="{{route('welcome')}}" class="btn btn-orange">
                        @lang('message.back')
                    </a>
                    <p class="mt-3">
                        <a id="contact-footer" href="{{route('additional.contact')}}" class="text-primary">
                            @lang('message.cont')
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
