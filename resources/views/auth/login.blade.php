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

    </style>
    <div style="background-image:url({{asset('assets/images/mackerel_fishing.JPG')}}); width: auto; height: auto; background-repeat: no-repeat; background-position: center; background-size: cover" class="pt-4 pb-4">

        <div class="container mb-auto" style="background-color: rgba(0, 0, 0, 0.5); border-radius: 30px; border-color:white;border-style: solid; border-width: 4px;">

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
                            <h3 style="color: #FFFFFF">@lang('forms.login')</h3>
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
                                <a class="btnForgetPwd mt-3" style="color: #FFFFFF" href="#}">@lang('forms.forgotPass')</a>
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
                                    <input style="margin: 5px;" type="checkbox" class="form-check-inline" id="agb" required>
                                    <label for="agb" class="text-white">
                                        {{ translate('Ich akzeptiere die') }}
                                        <a href="law/{{ route('law.agb') }}">{{ translate('AGB') }}</a>
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

