@extends('layouts.app')

@section('content')
    <div style="background-image:url({{asset('assets/images/login_background1.jpg')}}); width: auto; height: auto; background-repeat: no-repeat; background-position: center; background-size: cover">
    <div class="container ">
    <div class="row justify-content-center p-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Überprüfung erfolgreich') }}</div>

                <div class="card-body">
                    {{ __('Ihre E-Mail wurde bestätigt. Sie können sich jetzt mit Ihrem neuen Konto anmelden.') }}<br>
                    <a class="link-primary" href="{{ route('login') }}">@lang('registration-verification.loginText')</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
