@extends('admin.layouts.app')

@section('title', __('sales.texts.heading'))

@section('custom_style')
<link href="{{ mix('css/admin-sales-builder.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="side-app">
    <div class="main-container container-fluid sb-list">
        <div class="page-header">
            <div>
                <h1 class="page-title mb-1">{{ __('sales.texts.heading') }}</h1>
                <p class="text-muted small mb-0">{{ __('sales.texts.subheading') }}</p>
            </div>
        </div>

        @if(session('sales_notice'))
            <div class="alert alert-success" role="status">{{ session('sales_notice') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.sales.texts.update') }}" class="card card-body">
            @csrf
            @method('PUT')
            @foreach($keys as $key)
                <div class="mb-4">
                    <h2 class="h6 mb-1">{{ __('sales.texts.key.'.$key) }}</h2>
                    <p class="small text-muted mb-2">{{ __('sales.texts.help.'.$key) }}</p>
                    <div class="row g-3">
                        @foreach($languages as $language)
                            @php($name = "texts[$key][$language]")
                            <div class="col-12 col-lg-6">
                                <label class="form-label small mb-1 d-flex justify-content-between" for="text-{{ $key }}-{{ $language }}">
                                    <span>{{ strtoupper($language) }}</span>
                                    @if(isset($overrides[$key.'.'.$language]))
                                        <span class="badge bg-info">{{ __('sales.texts.edited') }}</span>
                                    @else
                                        <span class="text-muted">{{ __('sales.texts.default') }}</span>
                                    @endif
                                </label>
                                <textarea id="text-{{ $key }}-{{ $language }}" name="{{ $name }}" class="form-control form-control-sm" rows="3"
                                          placeholder="{{ $texts->default($key, $language) }}">{{ old("texts.$key.$language", $overrides[$key.'.'.$language] ?? '') }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">{{ __('sales.texts.empty_hint') }}</small>
                <button class="btn btn-primary btn-sm">{{ __('sales.texts.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
