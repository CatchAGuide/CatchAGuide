@extends('layouts.app-v2-1')
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
            padding: 2rem;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            margin: 1rem;
        }

        .login-form-2 h1, .login-form-2 h3 {
            text-align: center;
            margin-bottom: 2rem;
            color: #FFFFFF;
            font-weight: 600;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 10px;
            padding: 12px;
            margin: 8px 0;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: #FFFFFF;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .btnSubmit {
            font-weight: 600;
            width: 60% !important;
            color: #FFFFFF;
            background: linear-gradient(45deg, #1a1a1a, #333333);
            border: none;
            border-radius: 10px;
            padding: 12px;
            margin: 15px 0;
            transition: all 0.3s ease;
        }

        .btnSubmit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .btnForgetPwd {
            color: #FFFFFF;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btnForgetPwd:hover {
            color: #FFFFFF;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        .w-50 {
            width: 80% !important;
        }

    </style>
    <div style="background-image:url({{asset('assets/images/mackerel_fishing.JPG')}}); min-height: 100vh; background-repeat: no-repeat; background-position: center; background-size: cover" class="py-5">

        <div class="container" style="background-color: rgba(0, 0, 0, 0.7); border-radius: 20px; border: 2px solid rgba(255, 255, 255, 0.1); box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);">

            <div class="row">
                <div class="col-md-8 offset-2">
                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <div class="alert alert-danger" role="alert">
                                {{$error}}
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="col-lg-6 mt-3">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="col-lg-12 login-form-2">
                            <h1 style="color: #FFFFFF">@lang('forms.login')</h1>
                            <div class="d-flex justify-content-center align-items-center flex-column">
                                <div class="form-group w-50">
                                    <input style="margin: 5px;" type="email" class="form-control" placeholder="@lang('forms.user')"
                                            name="email" value="{{ old('email') }}"/>
                                </div>
                                <div class="form-group w-50">
                                    <input style="margin: 5px;" type="password" class="form-control" placeholder="@lang('forms.pass')"
                                            name="password" value=""/>
                                </div>
                                <button style="margin: 5px;" type="submit" class="btnSubmit">
                                    {{ __('Login') }}
                                </button>
                                <a class="btnForgetPwd mt-3" style="color: #FFFFFF" href="{{ route('password.request') }}">@lang('forms.forgotPass')</a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-lg-6 mb-5 mt-3">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="col-12 login-form-2">
                            {!! ReCaptcha::htmlScriptTagJsApi() !!}
                            <h3 style="color: #FFFFFF">@lang('forms.register')</h3>
                            @if(Session::has('success-message'))
                            <div class="alert alert-success"><small>@lang('registration-verification.success_message')</small></div>
                            @endif
                            <div class="d-flex justify-content-center align-items-center flex-column">
                                <div class="form-group w-50">
                                    <input style="margin: 5px;" id="firstname" type="text"
                                            class="form-control @error('firstname') is-invalid @enderror"
                                            name="firstname" value="{{ old('firstname') }}" required
                                            autocomplete="firstname"
                                            autofocus
                                            placeholder="@lang('forms.fname')" value="{{ old('firstname') }}"/>
                                    @error('firstname')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong>
                                    @enderror
                                </div>
                                <div class="form-group w-50">
                                    <input style="margin: 5px;" id="lastname" type="text"
                                            class="form-control @error('lastname') is-invalid @enderror"
                                            name="lastname" value="{{ old('lastname') }}" required
                                            autocomplete="lastname"
                                            autofocus
                                            placeholder="@lang('forms.lname')" value="{{old('lastname') }}"/>
                                    @error('lastname')
                                    <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                    @enderror
                                </div>
                                <div class="form-group w-50">
                                    <input style="margin: 5px;" id="email" type="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            name="email" value="{{ old('email') }}" required
                                            autocomplete="email"
                                            placeholder="@lang('forms.email')" value="old('email') }}"/>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                                    @enderror
                                </div>
                                <div class="form-group w-50">
                                    <input style="margin: 5px;" type="password" id="password" class="form-control"
                                            placeholder="@lang('forms.pass') @error('password') is-invalid @enderror"
                                            name="password" required autocomplete="new-password" value=""/>
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group w-50">
                                    <input style="margin: 5px;" id="password-confirm" type="password" class="form-control"
                                            name="password_confirmation" required autocomplete="new-password"
                                            placeholder="@lang('forms.rpass')" value=""/>
                                </div>
                                <div class="form-group w-50">
                                    <input style="margin: 5px;" type="checkbox" class="form-check-inline" id="agb" name='agb' required>
                                    <label for="agb" class="text-white">
                                        {{ translate('Ich akzeptiere die') }}
                                        <a href="{{ route('law.agb') }}">{{ translate('AGB') }}</a>
                                        {{ translate('und') }}
                                        <a href="{{ route('law.data-protection') }}">{{ translate('Datenschutzbestimmungen') }} </a>
                                    </label>
                                </div>
                                 {!! htmlFormSnippet() !!}
                                <button style="margin: 5px;" type="submit" class="btn btnSubmit">
                                    @lang('forms.register')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
                    
        </div>
    </div>

@endsection

