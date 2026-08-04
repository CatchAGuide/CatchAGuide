@extends('layouts.app-v2-1')

@section('title', __('notice-takedown.page_title'))

@section('meta_robots')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<section class="contact-pages">
    <div class="container">
        <h1 class="h2 mt-5 mb-3">{{ __('notice-takedown.heading') }}</h1>
        <p class="mb-4" style="max-width: 720px;">{{ __('notice-takedown.intro') }}</p>

        <div class="row mb-5">
            <div class="col-lg-6">
                <h3 class="h5 mb-3">{{ __('notice-takedown.how_it_works_title') }}</h3>
                <ol class="ps-3">
                    @foreach(__('notice-takedown.how_it_works') as $step)
                        <li class="mb-2">{{ $step }}</li>
                    @endforeach
                </ol>
            </div>
        </div>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-7">
                <div class="contact-page__left">
                    <div class="section-title text-left">
                        <h3>{{ __('notice-takedown.form_title') }}</h3>
                    </div>
                </div>
                <div class="comment-form">
                    <form action="{{ route('product-reports.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="comment-form__input-box">
                                    <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('notice-takedown.your_name') }}" required maxlength="255">
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="comment-form__input-box">
                                    <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('notice-takedown.email') }}" required maxlength="255">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="comment-form__input-box">
                                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="{{ __('notice-takedown.phone') }}" maxlength="40">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="comment-form__input-box">
                                    <select name="reason" class="form-control" required style="height: 58px; border-radius: 10px;">
                                        <option value="" disabled {{ old('reason') ? '' : 'selected' }}>{{ __('notice-takedown.reason_placeholder') }}</option>
                                        @foreach(\App\Models\ProductReport::reasonOptions() as $value => $label)
                                            <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="comment-form__input-box">
                                    <input type="url" name="reported_url" value="{{ old('reported_url') }}" placeholder="{{ __('notice-takedown.reported_url') }}" required maxlength="2048">
                                    <small class="text-muted d-block mt-1">{{ __('notice-takedown.reported_url_hint') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="comment-form__input-box">
                                    <textarea name="description" placeholder="{{ __('notice-takedown.description') }}" required minlength="20" maxlength="5000">{{ old('description') }}</textarea>
                                    <small class="text-muted d-block mt-1">{{ __('notice-takedown.description_hint') }}</small>
                                </div>
                                <div class="submit-container">
                                    <x-recaptcha />
                                    <button type="submit" class="thm-btn comment-form__btn">{{ __('notice-takedown.submit') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
