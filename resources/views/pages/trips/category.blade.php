@extends('layouts.app-v2')

@section('title', $row_data->title)
@section('description', $row_data->introduction)
@section('header_title', $row_data->title)
@section('header_sub_title', $row_data->sub_title)

@section('share_tags')
    <meta property="og:title" content="{{ $row_data->title }}" />
    <meta property="og:description" content="{{ $row_data->introduction ?? '' }}" />
    <meta name="description" content="{{ $row_data->sub_title ?? $row_data->introduction }}">
    @if(isset($row_data->thumbnail_path) && $row_data->thumbnail_path && file_exists(public_path($row_data->thumbnail_path)))
        <meta property="og:image" content="{{ asset($row_data->thumbnail_path) }}"/>
    @endif
@endsection

@section('custom_style')
<style>
    #destination { max-width: 1600px; }
    .trip-cat-card { transition: box-shadow .2s; }
    .trip-cat-card:hover { box-shadow: 0 .35rem 1rem rgba(0,0,0,.08); }
    .read-more-btn {
        background-color: #E8604C !important;
        color: #fff !important;
        border: 2px solid #E8604C !important;
    }
    @media (min-width: 400px) {
        #fish_chart_table th:first-child, #fish_chart_table td:first-child {
            position: sticky;
            left: 0;
            background-color: #fff;
            min-width: 156px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <section class="page-header">
        <div class="page-header__bottom breadcrumb-container guiding">
            <div class="page-header__bottom-inner">
                <ul class="thm-breadcrumb list-unstyled">
                    <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                    <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                    <li><a href="{{ route('trips.index') }}">{{ __('trips.catalog_breadcrumb') }}</a></li>
                    <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                    <li class="active">{{ translate($row_data->name) }}</li>
                </ul>
            </div>
        </div>
    </section>
</div>

<div class="container" id="trips-category">
    <div class="row">
        <div class="col-12">
            <div id="page-main-intro" class="mb-3">
                <div class="page-main-intro-text mb-1">{!! translate(nl2br($row_data->introduction)) !!}</div>
                <p class="see-more text-center"><a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_more')</a></p>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0">{{ __('trips.catalog_list_heading', ['place' => translate($row_data->name)]) }}</h5>
                <div class="btn-group btn-group-sm">
                    <a href="{{ url()->current() }}?sortby=newest" class="btn btn-outline-secondary @if(request('sortby')==='newest') active @endif">@lang('message.newest')</a>
                    <a href="{{ url()->current() }}?sortby=price-asc" class="btn btn-outline-secondary @if(request('sortby')==='price-asc') active @endif">@lang('message.lowprice')</a>
                    <a href="{{ url()->current() }}?sortby=price-desc" class="btn btn-outline-secondary @if(request('sortby')==='price-desc') active @endif">{{ __('trips.catalog_sort_price_desc') }}</a>
                </div>
            </div>

            <p class="text-muted small mb-3">{{ __('trips.catalog_result_count', ['count' => $trips_total]) }}</p>

            <div class="row">
                @forelse($trips as $trip)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card trip-cat-card h-100 border shadow-sm">
                            <a href="{{ route('trips.show', $trip->slug) }}" class="text-decoration-none text-dark">
                                @php
                                    $img = $trip->thumbnail_path ? asset($trip->thumbnail_path) : asset('images/placeholder_guide.jpg');
                                @endphp
                                <img src="{{ $img }}" class="card-img-top" alt="{{ $trip->title }}" style="height: 200px; object-fit: cover;">
                            </a>
                            <div class="card-body d-flex flex-column">
                                <h3 class="h5 card-title">
                                    <a href="{{ route('trips.show', $trip->slug) }}" class="stretched-link text-decoration-none text-dark">{{ \Illuminate\Support\Str::limit($trip->title, 70) }}</a>
                                </h3>
                                @if($trip->location)
                                <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt me-1"></i>{{ $trip->location }}</p>
                                @endif
                                @if($trip->price_per_person)
                                <p class="mt-auto mb-0 small">
                                    <span class="text-muted">{{ __('trips.price_per_person_short') }}:</span>
                                    <strong>€{{ number_format((float) $trip->price_per_person, 2) }}</strong>
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">@lang('trips.catalog_empty_category')</p>
                @endforelse
            </div>

            @if($trips->hasPages())
                <div class="my-4">{!! $trips->links('vendor.pagination.default') !!}</div>
            @endif

            @include('pages.destination-category.post-listing-content')
        </div>
    </div>
</div>
@endsection

@section('js_after')
<script>
    $(function(){
        var word_char_count_allowed = 520;
        var page_main_intro = $('.page-main-intro-text');
        var page_main_intro_text = page_main_intro.html();
        var page_main_intro_count = page_main_intro.text().length;
        var ellipsis = "...";
        var moreText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_more')</a>';
        var lessText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_less')</a>';
        var visible_text = page_main_intro_text.substring(0, word_char_count_allowed);
        var hidden_text  = page_main_intro_text.substring(word_char_count_allowed);
        if (page_main_intro_count >= word_char_count_allowed) {
            $('.page-main-intro-text').html(visible_text + '<span class="more-ellipsis">' + ellipsis + '</span><span class="more-text" style="display:none;">' + hidden_text + '</span>');
            $('.see-more').click(function(e) {
                e.preventDefault();
                var textContainer = $(this).prev('.page-main-intro-text');
                if ($(this).hasClass('less')) {
                    $(this).removeClass('less');
                    $(this).html(moreText);
                    textContainer.find('.more-text').hide();
                    textContainer.find('.more-ellipsis').show();
                } else {
                    $(this).addClass('less');
                    $(this).html(lessText);
                    textContainer.find('.more-text').show();
                    textContainer.find('.more-ellipsis').hide();
                }
            });
        } else {
            $('.see-more').hide();
        }
    });
</script>
@endsection
