@extends('layouts.app')
@section('title', 'Registrieren')
@section('content')
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

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
            color: #fff;
        }

        .login-form-2 {
            padding: 9%;
            background: #f05837;
            box-shadow: 0 5px 8px 0 rgba(0, 0, 0, 0.2), 0 9px 26px 0 rgba(0, 0, 0, 0.19);
        }

        .login-form-2 h3 {
            text-align: center;
            margin-bottom: 12%;
            color: #fff;
        }

        .btnSubmit {
            font-weight: 600;
            width: 50%;
            color: #282726;
            background-color: #fff;
            border: none;
            border-radius: 1.5rem;
            padding: 2%;
        }


        .btnForgetPwd {
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }

        .btnForgetPwd:hover {
            text-decoration: none;
            color: #fff;
        }

    </style>
    <div class="container mb-4">
        <div class="row justify-content-center ">
            <div class="col-lg-12">
                <div class="card">

                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="col-12 login-form-2">
                            @if ($errors->any())
                                @foreach ($errors->all() as $error)
                                    <div>{{$error}}</div>
                                @endforeach
                            @endif

                            <h3>Registrieren</h3>
                            <div class="d-flex justify-content-center align-items-center flex-column">
                                <div class="form-group w-50">
                                    <input id="firstname" type="text"
                                           class="form-control @error('firstname') is-invalid @enderror"
                                           name="firstname" value="{{ old('firstname') }}" required autocomplete="firstname"
                                           autofocus
                                           placeholder="Dein Vorname*" value="{{ old('firstname') }}"/>
                                    @error('firstname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group w-50">
                                    <input id="lastname" type="text"
                                           class="form-control @error('lastname') is-invalid @enderror"
                                           name="lastname" value="{{ old('lastname') }}" required autocomplete="lastname"
                                           autofocus
                                           placeholder="Dein Nachname*" value="{{ old('lastname') }}"/>
                                    @error('lastname')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group w-50">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                           name="email" value="{{ old('email') }}" required autocomplete="email"
                                           placeholder="Deine Email *" value="{{ old('email') }}"/>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group w-50">
                                    <input type="password" id="password" class="form-control"
                                           placeholder="Dein Passwort * @error('password') is-invalid @enderror"
                                           name="password" required autocomplete="new-password" value=""/>
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group w-50">
                                    <input id="password-confirm" type="password" class="form-control"
                                           name="password_confirmation" required autocomplete="new-password"
                                           placeholder="Passwort wiederholen *" value=""/>
                                </div>
                                <button type="submit" class="btnSubmit">
                                    {{ __('Registrieren') }}
                                </button>
                                <br/><span class="text-white">- oder -</span><br/>
                                <a class="btnForgetPwd mt-3" href="{{ route('login') }}">Anmelden</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
