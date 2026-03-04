@extends('admin.auth.layouts.app')

@section('content')
    <div class="admin-auth">
        <div class="admin-auth__orbit"></div>
        <div class="admin-auth__grid"></div>

        <div class="admin-auth__container container">
            <div class="row align-items-center justify-content-center g-4">
                <div class="col-xl-5 col-lg-6 col-md-9">
                    <div class="admin-auth__card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="admin-auth__headline">
                                <span class="admin-auth__badge">
                                    <span class="admin-auth__badge-dot"></span>
                                    {{ __('admin.auth.badge') }}
                                </span>
                                <h1 class="admin-auth__title mb-0">
                                    {{ __('admin.auth.welcome_back') }}
                                </h1>
                                <p class="admin-auth__subtitle mb-0">
                                    {{ __('admin.auth.subtitle') }}
                                </p>
                            </div>
                            <div class="admin-auth__brand ms-3">
                                <img src="{{ asset('assets/images/logo/CatchAGuide_Logo_PNG.png') }}" class="header-brand-img" alt="{{ config('app.name') }}">
                            </div>
                        </div>

                        <div class="admin-auth__errors">
                            @if ($errors->any())
                                <div class="alert alert-danger mb-2">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger mb-0">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </div>

                        <form class="admin-auth__form" method="POST" action="{{ route('admin.auth.login') }}">
                            @csrf

                            <div>
                                <label class="admin-auth__label" for="admin-email">{{ __('admin.auth.email_label') }}</label>
                                <div class="admin-auth__input-group">
                                    <span class="admin-auth__input-icon">
                                        <i class="zmdi zmdi-email" aria-hidden="true"></i>
                                    </span>
                                    <input
                                        id="admin-email"
                                        class="admin-auth__input"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="{{ __('admin.auth.email_placeholder') }}"
                                        required
                                        autocomplete="email"
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="admin-auth__label" for="admin-password">{{ __('admin.auth.password_label') }}</label>
                                <div class="admin-auth__input-group" id="Password-toggle">
                                    <span class="admin-auth__input-icon">
                                        <i class="zmdi zmdi-lock" aria-hidden="true"></i>
                                    </span>
                                    <input
                                        id="admin-password"
                                        class="admin-auth__input"
                                        type="password"
                                        name="password"
                                        placeholder="{{ __('admin.auth.password_placeholder') }}"
                                        required
                                        autocomplete="current-password"
                                    >
                                </div>
                            </div>

                            <div class="admin-auth__meta">
                                <label class="admin-auth__remember">
                                    <input type="checkbox" name="remember">
                                    <span>{{ __('admin.auth.remember_me') }}</span>
                                </label>

                                {{--<a href="#" class="admin-auth__forgot">Passwort vergessen?</a>--}}
                            </div>

                            <div class="admin-auth__submit">
                                <button type="submit" class="admin-auth__submit-btn">
                                    {{ __('admin.auth.login_button') }}
                                </button>
                            </div>
                        </form>

                        <div class="admin-auth__footer">
                            <span>{{ __('admin.auth.session_note_label') }}</span> {{ __('admin.auth.session_note_text') }}
                        </div>

                        <div class="admin-auth__sparkline admin-auth__sparkline--inline mt-3">
                            <div class="admin-auth__sparkline-label">
                                <span>{{ __('admin.auth.active_admins_today') }}</span>
                                <span class="admin-auth__sparkline-badge">
                                    <i class="zmdi zmdi-trending-up"></i>
                                    {{ __('admin.auth.trend_positive') }}
                                </span>
                            </div>
                            <div class="admin-auth__sparkline-chart" aria-hidden="true">
                                <span class="admin-auth__sparkline-bar" style="height: 35%"></span>
                                <span class="admin-auth__sparkline-bar" style="height: 65%"></span>
                                <span class="admin-auth__sparkline-bar" style="height: 45%"></span>
                                <span class="admin-auth__sparkline-bar" style="height: 75%"></span>
                                <span class="admin-auth__sparkline-bar" style="height: 55%"></span>
                                <span class="admin-auth__sparkline-bar" style="height: 85%"></span>
                                <span class="admin-auth__sparkline-bar" style="height: 60%"></span>
                                <span class="admin-auth__sparkline-bar" style="height: 90%"></span>
                                <span class="admin-auth__sparkline-bar" style="height: 70%"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
