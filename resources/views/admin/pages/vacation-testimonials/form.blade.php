@extends('admin.layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.vacation-testimonials.index') }}">{{ __('admin.vacation_testimonials.breadcrumb') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-8 col-xxl-6">
                    <div class="card shadow-sm">
                        <form action="{{ $route }}" method="POST">
                            @csrf
                            @if($method !== 'POST')
                                @method($method)
                            @endif

                            <div class="card-body">
                                @if($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label for="quote" class="form-label fw-semibold">{{ __('admin.vacation_testimonials.field_quote') }}</label>
                                    <textarea name="quote" id="quote" class="form-control" rows="4" required maxlength="2000">{{ old('quote', $testimonial?->quote) }}</textarea>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="author" class="form-label fw-semibold">{{ __('admin.vacation_testimonials.field_author') }}</label>
                                        <input type="text" name="author" id="author" class="form-control"
                                               value="{{ old('author', $testimonial?->author) }}" required maxlength="255">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="rating" class="form-label fw-semibold">{{ __('admin.vacation_testimonials.field_rating') }}</label>
                                        <input type="number" name="rating" id="rating" class="form-control"
                                               step="0.1" min="0" max="10"
                                               value="{{ old('rating', $testimonial?->rating) }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="reviewed_on" class="form-label fw-semibold">{{ __('admin.vacation_testimonials.field_reviewed_on') }}</label>
                                        <input type="date" name="reviewed_on" id="reviewed_on" class="form-control"
                                               value="{{ old('reviewed_on', $testimonial?->reviewed_on?->format('Y-m-d')) }}">
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="listing_title" class="form-label fw-semibold">{{ __('admin.vacation_testimonials.field_listing_title') }}</label>
                                        <input type="text" name="listing_title" id="listing_title" class="form-control"
                                               value="{{ old('listing_title', $testimonial?->listing_title) }}" maxlength="255">
                                        <div class="form-text">{{ __('admin.vacation_testimonials.field_listing_title_hint') }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="listing_url" class="form-label fw-semibold">{{ __('admin.vacation_testimonials.field_listing_url') }}</label>
                                        <input type="text" name="listing_url" id="listing_url" class="form-control"
                                               value="{{ old('listing_url', $testimonial?->listing_url) }}" maxlength="2048"
                                               placeholder="https://catchaguide.com/vacations/camps/...">
                                    </div>
                                </div>

                                <div class="row g-3 align-items-center mb-3">
                                    <div class="col-md-4">
                                        <label for="sort_order" class="form-label fw-semibold">{{ __('admin.vacation_testimonials.field_sort_order') }}</label>
                                        <input type="number" name="sort_order" id="sort_order" class="form-control"
                                               min="0" value="{{ old('sort_order', $testimonial?->sort_order ?? 0) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="border rounded px-3 py-2 mt-4">
                                            <div class="form-check mb-0">
                                                <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1"
                                                    @checked(old('is_published', $testimonial?->is_published ?? true))>
                                                <label class="form-check-label fw-semibold" for="is_published">
                                                    {{ __('admin.vacation_testimonials.field_is_published') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.vacation-testimonials.index') }}" class="btn btn-outline-secondary">
                                    {{ __('admin.vacation_testimonials.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-success px-4">
                                    {{ __('admin.vacation_testimonials.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
