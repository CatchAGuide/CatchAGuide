@extends('layouts.app')
@section('title', 'Passwort zurücksetzen')
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

    </style>
    <div style="background-image:url({{asset('assets/images/login_backgroundnew.jpg')}}); width: auto; height: auto; background-repeat: no-repeat; background-position: center; background-size: cover" class="pt-4 pb-4">

        <div class="container mb-auto" style="background-color: rgba(0, 0, 0, 0.5); border-radius: 30px; border-color:white;border-style: solid; border-width: 4px;">
            <div class="row justify-content-center">
                <div class="col-lg-12">

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="col-lg-8 offset-2 login-form-2 text-center">
                            <h3 style="color: #FFFFFF">{{ translate('Passwort vergessen') }}</h3>
                            <div class="form-group" style="margin-bottom: 25px;">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                       name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                                       placeholder="{{ translate('Deine Email') }}*" />
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btnSubmit">
                                    {{ translate('Passwort zurücksetzen') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
