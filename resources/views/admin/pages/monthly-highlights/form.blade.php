@extends('admin.layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="side-app">
        <div class="main-container container-fluid">
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.monthly-highlights.index') }}">Monthly Highlights</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-10 col-xxl-8">
                    <div class="card shadow-sm">
                        <div class="card-header border-bottom">
                            <div>
                                <h3 class="card-title mb-1">@yield('title')</h3>
                                <p class="text-muted mb-0 small">
                                    Powers the homepage season module. Each card pairs a country with a target fish
                                    (up to {{ \App\Models\MonthlyHighlight::MAX_ITEMS }} cards).
                                </p>
                            </div>
                        </div>

                        <form action="{{ $route }}" method="POST" id="monthly-highlight-form">
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

                                <div class="row g-3 align-items-end mb-4">
                                    <div class="col-md-6 col-lg-4">
                                        <label for="month" class="form-label fw-semibold">Month</label>
                                        <select name="month" id="month" class="form-control form-select" required>
                                            @foreach($months as $value => $label)
                                                <option value="{{ $value }}" @selected((int) old('month', $highlight?->month ?? now()->month) === (int) $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="border rounded px-3 py-2 h-100 d-flex align-items-center">
                                            <div class="form-check mb-0">
                                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                                    @checked(old('is_active', $highlight?->is_active ?? true))>
                                                <label class="form-check-label fw-semibold" for="is_active">Active on homepage</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="text-uppercase text-muted small fw-bold mb-3">Copy</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="title_en" class="form-label fw-semibold">Title (EN)</label>
                                        <input type="text" name="title_en" id="title_en" class="form-control"
                                               value="{{ old('title_en', $highlight?->title_en) }}" required maxlength="255"
                                               placeholder="What's biting in August?">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="title_de" class="form-label fw-semibold">Title (DE)</label>
                                        <input type="text" name="title_de" id="title_de" class="form-control"
                                               value="{{ old('title_de', $highlight?->title_de) }}" required maxlength="255"
                                               placeholder="Was beißt im August?">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="subtitle_en" class="form-label fw-semibold">Subtext (EN)</label>
                                        <textarea name="subtitle_en" id="subtitle_en" class="form-control" rows="3" maxlength="1000"
                                                  placeholder="Short supporting line for anglers">{{ old('subtitle_en', $highlight?->subtitle_en) }}</textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="subtitle_de" class="form-label fw-semibold">Subtext (DE)</label>
                                        <textarea name="subtitle_de" id="subtitle_de" class="form-control" rows="3" maxlength="1000"
                                                  placeholder="Kurzer Begleittext für Angler">{{ old('subtitle_de', $highlight?->subtitle_de) }}</textarea>
                                    </div>
                                </div>

                                <h6 class="text-uppercase text-muted small fw-bold mb-2">Homepage cards</h6>
                                <p class="text-muted small mb-3">
                                    Each row is one homepage card: country badge + target fish title.
                                    Leave a row empty to skip it.
                                </p>

                                @php
                                    $oldCards = old('cards', $cards);
                                @endphp

                                @for($i = 0; $i < \App\Models\MonthlyHighlight::MAX_ITEMS; $i++)
                                    @php
                                        $card = $oldCards[$i] ?? ['country_id' => null, 'target_id' => null];
                                    @endphp
                                    <div class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-semibold">Card {{ $i + 1 }}</span>
                                            @error('cards.'.$i)
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="cards_{{ $i }}_country_id" class="form-label fw-semibold">Country</label>
                                                <select
                                                    name="cards[{{ $i }}][country_id]"
                                                    id="cards_{{ $i }}_country_id"
                                                    class="form-control mh-select2"
                                                    data-placeholder="Select country..."
                                                >
                                                    <option value=""></option>
                                                    @foreach($countryOptions as $option)
                                                        <option value="{{ $option['id'] }}"
                                                            @selected((int) ($card['country_id'] ?? 0) === (int) $option['id'])>
                                                            {{ $option['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="cards_{{ $i }}_target_id" class="form-label fw-semibold">Target fish</label>
                                                <select
                                                    name="cards[{{ $i }}][target_id]"
                                                    id="cards_{{ $i }}_target_id"
                                                    class="form-control mh-select2"
                                                    data-placeholder="Select target fish..."
                                                >
                                                    <option value=""></option>
                                                    @foreach($targetOptions as $option)
                                                        <option value="{{ $option['id'] }}"
                                                            @selected((int) ($card['target_id'] ?? 0) === (int) $option['id'])>
                                                            {{ $option['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>

                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.monthly-highlights.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-success px-4">Save highlight</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js_after')
<script>
jQuery(function ($) {
    if (typeof $.fn.select2 !== 'function') {
        return;
    }

    $('.mh-select2').each(function () {
        var $el = $(this);
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        $el.select2({
            width: '100%',
            allowClear: true,
            placeholder: $el.data('placeholder') || 'Select...'
        });
    });
});
</script>
@endpush
