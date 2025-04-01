@extends('pages.profile.layouts.profile')

@section('profile-content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center p-5">
                    <h2>{{ session('message') ?: __('Thank you for your feedback!') }}</h2>
                    <p>{{ __('Your review has been submitted successfully. Reviews can only be submitted once.') }}</p>
                    <a href="{{ route('welcome') }}" class="btn btn-orange">
                        {{ __('Return to Homepage') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 