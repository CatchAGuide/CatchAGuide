@extends('admin.auth.layouts.app')

@section('content')
    <div class="login-img">

        <!-- PAGE -->
        <div class="page">
            <div class="">
                <!-- Theme-Layout -->

                <!-- CONTAINER OPEN -->
                <div class="col col-login mx-auto mt-7">
                    <div class="text-center">
                        <img src="{{ asset('assets/images/logo.png') }}" class="header-brand-img" alt="">
                    </div>
                </div>

                <div class="container-login100">
                    <div class="wrap-login100 p-6">
                        <form class="login100-form validate-form" method="POST" action="{{ route('admin.auth.login') }}">
                            @csrf
                            <span class="login100-form-title pb-5">
                                Anmelden
                            </span>
                            <div class="panel panel-primary">
                                <div class="wrap-input100 validate-input input-group">
                                    <a href="#" class="input-group-text bg-white text-muted">
                                        <i class="zmdi zmdi-email text-muted" aria-hidden="true"></i>
                                    </a>
                                    <input class="input100 form-control" type="email" placeholder="E-Mail Adresse" name="email">
                                </div>
                                <div class="wrap-input100 validate-input input-group" id="Password-toggle">
                                    <a href="#" class="input-group-text bg-white text-muted">
                                        <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                                    </a>
                                    <input class="input100 form-control" type="password" placeholder="Passwort" name="password">
                                </div>
                                {{--<div class="text-end pt-4">
                                    <p class="mb-0"><a href="forgot-password.html" class="text-primary ms-1">Forgot Password?</a></p>
                                </div>--}}
                                <div class="container-login100-form-btn">
                                    <button type="submit" class="login100-form-btn btn-primary">Anmelden</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
                <!-- CONTAINER CLOSED -->
            </div>
        </div>
        <!-- End PAGE -->

    </div>
@endsection
