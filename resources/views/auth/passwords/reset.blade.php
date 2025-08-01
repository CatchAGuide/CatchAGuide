@extends('layouts.app')
@section('title', 'Login')
@section('content')


    <style>


        .login-logo img {
            position: absolute;
            width: 20%;
            margin-top: 19%;
            background: #282726;
            border-radius: 4.5rem;
            padding: 5%;
        }


        .login-form-1 h3 {
            text-align: center;
            margin-bottom: 12%;
            color: #111111;
        }

        .login-form-2 {
            padding: 9%;
        rgba(0, 0, 0, 0.19);
        }

        .login-form-2 h3 {
            text-align: center;
            margin-bottom: 12%;
            color: #111111;
        }

        .btnSubmit {
            font-weight: 600;
            width: 70% !important;
            color: #282726;
            background-color: #111111;
            border: none;
            border-radius: 1.5rem;
            padding: 2%;
        }

        .btnForgetPwd {
            color: #111111;
            font-weight: 600;
            text-decoration: none;
        }

        .btnForgetPwd:hover {
            text-decoration: none;
            color: #111111;
        }

        .login-logo img {
            position: absolute;
            width: 20%;
            margin-top: 19%;
            background: #282726;
            border-radius: 4.5rem;
            padding: 5%;
        }


        .login-form-1 h3 {
            text-align: center;
            margin-bottom: 12%;
            color: #111111;
        }

        .login-form-2 {
            padding: 9%;
        rgba(0, 0, 0, 0.19);
        }

        .login-form-2 h3 {
            text-align: center;
            margin-bottom: 12%;
            color: #111111;
        }

        .btnSubmit {
            font-weight: 600;
            width: 50%;
            color: #111111;
            background-color: #ffffff;
            border: none;
            border-radius: 1.5rem;
            padding: 2%;
        }


        .btnForgetPwd {
            color: #111111;
            font-weight: 600;
            text-decoration: none;
        }

        .btnForgetPwd:hover {
            text-decoration: none;
            color: #111111;
        }
        .w-50 {
            width: 80% !important;
        }
        label {
            color: white;
        }

    </style>
    <div style="background-image:url({{asset('assets/images/login_backgroundnew.jpg')}}); width: auto; height: auto; background-repeat: no-repeat; background-position: center; background-size: cover" class="pt-4 pb-4">

        <div class="container mb-auto" style="background-color: rgba(0, 0, 0, 0.5); border-radius: 30px; border-color:white;border-style: solid; border-width: 4px;">
            <div class="row justify-content-center">
                <div class="col-md-8 login-form-2">


                    <h1 class="h2" style="color: #FFFFFF">{{ __('global.Reset Password') }}</h1>

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="row mb-3">
                                <label for="email"
                                       class="col-md-4 col-form-label text-md-end">{{ __('global.E-Mail Address') }}</label>
                                <div class="col-md-6">
                                    <input id="email" type="email"
                                           class="form-control @error('email') is-invalid @enderror" name="email"
                                           value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="password"
                                       class="col-md-4 col-form-label text-md-end">{{ __('global.Password') }}</label>
                                <div class="col-md-6">
                                    <input id="password" type="password"
                                           class="form-control @error('password') is-invalid @enderror" name="password"
                                           required autocomplete="new-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="password-confirm"
                                       class="col-md-4 col-form-label text-md-end">{{ __('global.New Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btnSubmit">
                                        {{ __('global.Set New Password') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
@endsection
