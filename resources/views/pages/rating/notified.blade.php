@extends('pages.profile.layouts.profile')

@section('profile-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h2>{{ session('message') ?: __('Thank you for your rating!') }}</h2>
                    <p>{{ __('Your feedback helps other anglers make better decisions.') }}</p>
                    <a href="{{ route('welcome') }}" class="btn btn-primary">
                        {{ __('Return to Homepage') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 