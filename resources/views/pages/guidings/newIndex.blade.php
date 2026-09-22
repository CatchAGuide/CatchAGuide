@extends('layouts.app-v2')

@section('title',$guiding->title)
@section('description',$guiding->desc_course_of_action ?? $guiding->title)

@section('canonical')
    <link rel="canonical" href="{{ $guiding->publicShowUrl() }}" />
@endsection

@section('share_tags')
    <meta property="og:title" content="{{$guiding->title}}" />
    <meta property="og:description" content="{{$guiding->desc_course_of_action ?? ""}}" />
    @if(media_path_usable($guiding->thumbnail_path))
        <meta property="og:image" content="{{ media_url($guiding->thumbnail_path) }}"/>
    @endif
@endsection

@section('meta_robots')
    @php
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $guiding->title,
            'description' => $guiding->desc_tour_unique ?? $guiding->desc_course_of_action ?? $guiding->description,
            'url' => $guiding->publicShowUrl(),
            'image' => $guiding->thumbnail_path ? media_url($guiding->thumbnail_path) : null,
            'touristType' => 'Fishing trip',
            'areaServed' => array_filter([
                $guiding->city,
                $guiding->region,
                $guiding->country,
            ]),
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'EUR',
                'price' => $guiding->getLowestPrice() ?? null,
                'availability' => 'https://schema.org/InStock'
            ],
        ];
    @endphp
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('css_after')
    <style>
        .carousel .carousel-control-next, .carousel .carousel-control-prev {
            top: 50%;
            transform: translateY(-50%);
        }
        .carousel .carousel-control-next {
            right: 20px;
        }

        .carousel .carousel-control-prev {
            left: 20px;
        }

        .carousel-item-next, .carousel-item-prev, .carousel-item.active {
            display: flex;
        }
        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 10px;
            height: 10px;
        }
        
        .card-header {
            background-color: transparent;
            color: #262e35;
            font-weight:bold;
        }
        .similar-guides-section {
            margin-bottom: 2rem; /* Adjust as needed */
        }

        @media screen and (max-width: 767px) {
            .price-details{
                display:none;
            }
            .sticky-booking {
                position: sticky;
                top: 20px;
            }
        }

        .guidings-gallery {
            min-height: 400px;
            gap: 16px;
            margin-bottom: 16px !important;
        }

        .guidings-gallery .left-image {
            width: 600px;
            padding: 0 !important;
            height: 400px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .guidings-gallery .right-images img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            aspect-ratio: 3/2;
        }

        /* Update modal gallery images */
        div#masonry-grid img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            aspect-ratio: 3/2;
        }

        /* Force the ratings slider to display */
        .ratings-slider {
            display: block !important;
            overflow: hidden;
        }
        
        .ratings-item {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        
        .ratings-comment-top {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        /* Horizontal scrolling reviews container with hidden scrollbar */
        .ratings-container {
            display: flex;
            overflow-x: auto;
            scroll-behavior: smooth;
            gap: 20px;
            padding: 10px 0 20px;
            margin: 20px 0;
            -webkit-overflow-scrolling: touch; /* For smooth scrolling on iOS */
            cursor: grab; /* Show grab cursor to indicate draggable */
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        /* Hide scrollbar for Chrome, Safari and Opera */
        .ratings-container::-webkit-scrollbar {
            display: none;
        }
        
        .ratings-container:active {
            cursor: grabbing; /* Change cursor when actively dragging */
        }
        
        .ratings-item {
            flex: 0 0 300px; /* Fixed width, no growing or shrinking */
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            user-select: none; /* Prevent text selection during drag */
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        /* Review card (comment section layout) */
        :root {
            --review-card-accent: #8b2332;
        }
        .review-card__header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
        }
        .review-card__avatar {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #f0f0f0;
            color: var(--review-card-accent);
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .review-card__meta {
            flex: 1;
            min-width: 0;
        }
        .review-card__name {
            font-weight: 700;
            margin: 0 0 2px;
            color: #111;
            font-size: 1rem;
        }
        .review-card__date {
            margin: 0;
            font-size: 0.72rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #9a9a9a;
        }
        .review-card__score {
            flex-shrink: 0;
            text-align: right;
            line-height: 1.1;
        }
        .review-card__score-val {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--review-card-accent);
        }
        .review-card__score-den {
            font-size: 0.8rem;
            color: #9a9a9a;
            font-weight: 500;
        }
        .review-card__divider {
            border: 0;
            border-top: 1px solid #e8e8e8;
            margin: 0 0 12px;
            opacity: 1;
        }
        .review-card__quote {
            font-style: italic;
            color: #111;
            margin: 0 0 12px;
            font-size: 0.95rem;
            line-height: 1.45;
        }
        .review-card__quote .description {
            margin: 0;
        }
        .review-card__badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #f3f3f3;
            color: #6f6f6f;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* Hide navigation buttons */
        .ratings-nav {
            display: none;
        }
        
        /* Highlight effect for reviews section */
        .highlight-reviews {
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.3);
            border-radius: 8px;
        }
        
        /* Ratings overview layout/alignment */
        .rating-overview .ratings-wrapper {
            display: flex;
            align-items: stretch;
            gap: 24px;
        }
        .rating-overview .rating-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 140px;
        }
        .rating-overview .rating-categories { flex: 1; min-width: 0; }
        .rating-overview .score-wrapper { margin: 0 auto; position: relative; display: flex; align-items: center; justify-content: center; }
        .rating-overview .score-wrapper .score { line-height: 1; }
        .rating-overview .score-wrapper .score-label { position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); margin: 0; line-height: 1; }
        .rating-overview .category .progress { height: 8px; }

        /* Mobile alignment fixes for ratings */
        @media (max-width: 767px) {
            .rating-overview .ratings-wrapper { flex-direction: column; gap: 16px; align-items: center; }
            .rating-overview .rating-categories { width: 100%; }
            .rating-overview .category { align-items: center; }
            .rating-overview .category .category-label { width: 130px; min-width: 130px; }
            .rating-overview .rating-left { text-align: center; align-items: center; display: flex; justify-content: center; width: 100%; }
            .rating-overview .score-wrapper { text-align: center; margin: 0 auto !important; }
            .rating-overview .rating-info { text-align: center; }
            .rating-overview .rating-left .score { margin: 0 auto; display: inline-block; }
        }
        
        /* Make reviews link look clickable */
        #reviews-link:hover {
            color: var(--primary-color, #007bff) !important;
            text-decoration: underline !important;
        }
        
        /* Make rating scores clickable */
        .rating-clickable {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .rating-clickable:hover {
            color: var(--primary-color, #007bff) !important;
            transform: scale(1.05);
        }
        
        /* Hide week numbers to ensure 7 days display */
        .litepicker .week-number,
        .litepicker .container__days .week-number,
        .litepicker .container__days > .week-number,
        #lite-datepicker .week-number,
        #lite-datepicker .litepicker .week-number,
        #guidings-page #lite-datepicker .litepicker .week-number,
        #guidings-page #lite-datepicker .litepicker .container__days .week-number,
        #guidings-page #lite-datepicker .litepicker .container__months .week-number,
        #guidings-page #lite-datepicker .litepicker .month-item-weekdays-row .week-number {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            visibility: hidden !important;
            flex: 0 0 0 !important;
            grid-column: span 0 !important;
            position: absolute !important;
            left: -9999px !important;
            opacity: 0 !important;
        }
        
        /* BASE LAYOUT - All 7 days using CSS Grid (works for both mobile and desktop) */
        #lite-datepicker .litepicker .container__days,
        #lite-datepicker .litepicker .month-item-weekdays-row {
            display: grid !important;
            grid-template-columns: repeat(7, 1fr) !important;
            width: 100% !important;
            gap: 1px !important;
        }
        
        /* Ensure all children fit within grid cells */
        #lite-datepicker .litepicker .container__days > *,
        #lite-datepicker .litepicker .month-item-weekdays-row > * {
            width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box !important;
        }
        
        /* Mobile: single column view (up to 767px) */
        @media (max-width: 767px) {
            #lite-datepicker .litepicker .container__months:not(.columns-2) .month-item .container__days,
            #lite-datepicker .litepicker .container__months:not(.columns-2) .month-item .month-item-weekdays-row {
                display: grid !important;
                grid-template-columns: repeat(7, 1fr) !important;
                width: 100% !important;
                gap: 2px !important;
            }
            
            #lite-datepicker .litepicker .container__months:not(.columns-2) .month-item .container__days .day-item,
            #lite-datepicker .litepicker .container__months:not(.columns-2) .month-item .month-item-weekdays-row > div {
                min-height: 40px !important;
                font-size: 0.85rem !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
        }
        
        /* Desktop: two column view (768px and up) — keep days inside the calendar card border */
        @media (min-width: 768px) {
            #guidings-page #lite-datepicker .litepicker,
            #guidings-page #lite-datepicker .litepicker .container__months.columns-2,
            #guidings-page #lite-datepicker .litepicker .container__months.columns-2 .month-item {
                box-sizing: border-box !important;
            }

            #guidings-page #lite-datepicker .litepicker .container__months.columns-2 {
                width: 100% !important;
                max-width: 100% !important;
                padding: 8px 12px 12px;
            }

            #guidings-page #lite-datepicker .litepicker .container__months.columns-2 .month-item {
                width: calc(50% - 5px) !important;
                min-width: 0 !important;
                max-width: calc(50% - 5px) !important;
                flex: 1 1 calc(50% - 5px) !important;
            }
            
            #guidings-page #lite-datepicker .litepicker .container__months.columns-2 .month-item .container__days,
            #guidings-page #lite-datepicker .litepicker .container__months.columns-2 .month-item .month-item-weekdays-row {
                display: grid !important;
                grid-template-columns: repeat(7, 1fr) !important;
                width: 100% !important;
                max-width: 100% !important;
                gap: 1px !important;
                box-sizing: border-box !important;
            }
            
            #guidings-page #lite-datepicker .litepicker .container__months.columns-2 .month-item .container__days > *,
            #guidings-page #lite-datepicker .litepicker .container__months.columns-2 .month-item .month-item-weekdays-row > * {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }
        }
        
        /* Calendar availability indicators */
        .litepicker .day-item {
            transition: all 0.2s ease !important;
        }
        
        .litepicker .day-item:not(.is-locked):not(.is-start-date):not(.is-end-date):not(.is-selected) {
            background-color: #d4edda !important; /* Light green for available dates */
            border: 1px solid #28a745 !important;
            color: #155724 !important;
        }
        
        .litepicker .day-item.is-locked {
            background-color: #ffeaea !important; /* More subtle light red for blocked dates */
            border: 1px solid #ffb3b3 !important;
            color: #b85450 !important;
            cursor: not-allowed !important;
            opacity: 0.7;
        }
        
        .litepicker .day-item:not(.is-locked):not(.is-selected):hover {
            background-color: #c3e6cb !important; /* Slightly darker green on hover */
            border-color: #28a745 !important;
            color: #155724 !important;
            transform: scale(1.02) !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2) !important;
        }
        
        .litepicker .day-item.is-locked:hover {
            /* Keep blocked dates unchanged on hover to maintain disabled state */
            cursor: not-allowed !important;
        }
        
        /* Selected date styling - works for both desktop and mobile */
        .litepicker .day-item.is-selected,
        .litepicker .day-item.is-start-date,
        .litepicker .day-item.is-end-date {
            background-color: #313041 !important;
            color: white !important;
            border: 1px solid #2a2938 !important;
        }
        
        .litepicker .day-item.is-selected:hover,
        .litepicker .day-item.is-start-date:hover,
        .litepicker .day-item.is-end-date:hover {
            background-color: #2a2938 !important;
            color: white !important;
            transform: scale(1.02) !important;
            box-shadow: 0 2px 4px rgba(49, 48, 65, 0.3) !important;
        }
        
        /* Calendar legend */
        .calendar-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
            border: 1px solid;
        }
        
        .legend-available {
            background-color: #d4edda;
            border-color: #28a745;
        }
        
        .legend-blocked {
            background-color: #ffeaea;
            border-color: #ffb3b3;
            opacity: 0.7;
        }
        
        @media (max-width: 767px) {
            .calendar-legend {
                gap: 15px;
                font-size: 13px;
            }
            
            .legend-color {
                width: 14px;
                height: 14px;
            }
        }
        
        @media (max-width: 767px) {
            .ratings-item {
                flex: 0 0 85vw; /* Take up most of the viewport width on mobile */
            }
        }
        
        /* Desktop/Mobile view toggling */
        @media (min-width: 768px) {
            .tours-list__inner.mobile-view {
                display: none !important;
            }
            .tours-list__inner.desktop-view {
                display: block !important;
            }
            .mobile-view {
                display: none !important;
            }
            .desktop-view {
                display: block !important;
            }
        }
        
        @media (max-width: 767px) {
            .tours-list__inner.desktop-view {
                display: none !important;
            }
            .tours-list__inner.mobile-view {
                display: flex !important;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                gap: 12px;
                padding: 0 0 12px;
                scrollbar-width: none;
                margin-left: -12px;
                margin-right: -12px;
                padding-left: 12px;
                padding-right: 12px;
            }
            .tours-list__inner.mobile-view::-webkit-scrollbar {
                display: none;
            }
            .desktop-view {
                display: none !important;
            }
            .mobile-view {
                display: block !important;
            }

            .popular-tours__single {
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                border-radius: 8px;
                overflow: hidden;
                background: #fff;
            }
            
            .popular-tours__img__wrapper {
                height: 180px;
                overflow: hidden;
            }
            
            .popular-tours__img__wrapper img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .popular-tours__content {
                padding: 15px;
            }
            
            .crop-text-2 {
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .crop-text-1 {
                display: -webkit-box;
                -webkit-line-clamp: 1;
                -webkit-box-orient: vertical;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            /* Compact Languages Section */
            .languages-compact-section {
                background: #f8f9fa;
                padding: 8px 12px;
                border-radius: 6px;
                border-left: 3px solid #007bff;
            }
            
            .language-flag-compact {
                display: inline-block;
                transition: transform 0.2s ease;
            }
            
            .language-flag-compact:hover {
                transform: scale(1.1);
            }
            
            .language-flag-compact img {
                border-radius: 3px;
                border: 1px solid #dee2e6;
            }
            
            .language-text-compact {
                background: #E85B40;
                color: white;
                padding: 2px 6px;
                border-radius: 10px;
                font-size: 11px;
                font-weight: 500;
            }
        }
        
        .payment-icon {
            color: #E85B40;
            font-size: 1.1rem;
        }

        /* Languages Section - Upper Right */
        .languages-section-upper-right {
            background: #f8f9fa;
            padding: 8px 16px;
            border-radius: 6px;
            border-right: 3px solid #007bff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Languages Section - Corner Position */
        .languages-section-corner {
            position: absolute;
            top: 0;
            right: 0;
            padding: 8px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 0 6px 0 6px;
            border-left: 2px solid rgba(0,0,0,.125);
            border-bottom: 2px solid rgba(0,0,0,.125);
        }
        
        .language-flag-compact {
            display: inline-block;
            transition: transform 0.2s ease;
        }
        
        .language-flag-compact:hover {
            transform: scale(1.1);
        }
        
        .language-flag-compact img {
            border-radius: 3px;
            border: 1px solid #dee2e6;
        }
        
        .language-text-compact {
            background: #E85B40;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 500;
        }

        /* Same-guide block: catalog list rows; show every card on mobile (desktop uses .is-visible + See more) */
        @media (max-width: 767px) {
            #same-guide-guidings-list .guiding-product-list-item {
                display: block !important;
            }
        }
    </style>
@endsection

@section('content')
<div class="category-hero-page" data-category-hero-page>
    @include('pages.category.partials.product-hero-header', [
        'hubTitle' => __('homepage.filter-fishing-near-me'),
        'listingTitle' => $guiding->title,
        'searchAction' => listing_search_action(),
        'breadcrumbItems' => [
            ['label' => __('homepage.filter-fishing-near-me'), 'url' => route('guidings.index')],
            ['label' => $guiding->title, 'url' => null],
        ],
        'locationLabel' => implode(', ', array_unique(array_filter([
            $guiding->city ?: $guiding->location,
            $guiding->region,
        ]))),
        'mapHref' => '#map',
        'ratingScore' => $average_grandtotal_score ?? null,
        'reviewsCount' => $reviews_count ?? 0,
    ])

 <div id="guidings-page" class="container category-hero-page__body offers-page-header__anim" style="--offers-anim-i: 5">
    <div class="title-container">
        <div class="title-wrapper">
            <div class="title-left-container">
                <div class="col-24 col mb-1 guiding-title">
                    <h1>{{ $guiding->title }}</h1>
                </div>
                <div class="col-12">
                    <div class="location-row">
                        <div class="location">
                            <span class="fs-6 text-muted">
                                @lang('guidings.Fishing_Trip') <strong>{{ $guiding->location }}</strong>
                            </span>
                        </div>
                        <div class="location-map">
                            <a href="#map" class="fs-6 text-decoration-none">
                                <span class="text-primary">@lang('guidings.show_on_map')</span>
                            </a>
                        </div>
                    </div>
                </div>
                @if ($average_grandtotal_score)
                    <div class="ave-reviews-row">
                        <div class="ratings-score">
                            <span class="rating-value rating-clickable" id="rating-score-link-desktop">{{ one($average_grandtotal_score, 1) }}</span>
                        </div>
                        <span class="mb-1">
                            (<a href="#ratings-container" class="text-decoration-none text-muted">{{ trans_choice('offers.reviews_count', $reviews_count ?? 0, ['count' => $reviews_count ?? 0]) }}</a>)
                        </span>
                    </div>
                @else
                    <span>@lang('guidings.no_reviews')</span>
                @endif
            </div>
            <div class="title-right-container">
                <div class="title-right-buttons">
                    <a class="btn" href="#" role="button"><i data-lucide="share-2"></i></a>
                    <a href="#book-now" class="btn btn-orange">@lang('message.reservation')</a>
                </div>
                <span>@lang('guidings.Best_price_guarantee')</span>
            </div>
        </div>
    </div>
        
        <!-- Image Gallery -->
        @php
            $tourGalleryId = 'tour-detail-'.$guiding->id;
            $galleryImagesRaw = decode_if_json($guiding->gallery_images, true);
            $thumbnailPath = $guiding->thumbnail_path;
            $overallImages = [];
            $desktopThumbs = [];

            if (media_path_usable($thumbnailPath)) {
                $overallImages[] = media_url($thumbnailPath);
            }

            if ($galleryImagesRaw) {
                foreach ($galleryImagesRaw as $image) {
                    if (media_path_usable($image) && $image !== $thumbnailPath) {
                        $overallImages[] = media_url($image);
                    }
                }
            }

            $overallImages = array_values(array_unique($overallImages));
            $galleryCount = count($overallImages);
            $mobileCarouselImages = array_slice($overallImages, 1);
            $galleryOnly = array_values(array_filter(
                $overallImages,
                fn ($url) => ! media_path_usable($thumbnailPath) || $url !== media_url($thumbnailPath)
            ));
            $desktopHiddenCount = max(0, count($galleryOnly) - 4);
            $desktopThumbs = array_slice($galleryOnly, 0, 4);
            if (count($desktopThumbs) < 4 && media_path_usable($thumbnailPath)) {
                $padUrl = media_url($thumbnailPath);
                while (count($desktopThumbs) < 4) {
                    $desktopThumbs[] = $padUrl;
                }
            }

            $tourGalleryIndex = function (string $url) use ($overallImages): int {
                $idx = array_search($url, $overallImages, true);
                return $idx === false ? 0 : (int) $idx;
            };

            $tourModalSpecs = array_values(array_filter([
                $guiding->duration.' '.($guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours')),
                __('guidings.Number_of_guests').' '.$guiding->max_guests,
                $guiding->is_boat
                    ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat'))
                    : __('guidings.shore'),
            ]));
            $tourPriceDisplay = $guiding->getLowestPrice() !== null
                ? number_format((float) $guiding->getLowestPrice(), 0, ',', '.').'€'
                : null;
        @endphp
        <div
            class="guidings-gallery row mx-0 mb-3"
            data-vacation-gallery="{{ $tourGalleryId }}"
            data-gallery-images='@json($overallImages)'
        >
            <div class="left-image" @if(!empty($overallImages)) data-gallery-index="0" style="cursor: pointer;" @endif>
                @if(!empty($overallImages))
                    <img src="{{ $overallImages[0] }}" class="img-fluid" alt="{{ __('guidings.gallery_image_alt', ['title' => $guiding->title, 'num' => 1]) }}">
                    @if($galleryCount > 1)
                        <span class="camp-gallery__counter">1/{{ $galleryCount }}</span>
                    @endif
                @else
                    <div class="text-center p-4">
                        <p>@lang('guidings.No_image_found')</p>
                    </div>
                @endif
            </div>
            <div class="right-images">
                <div class="gallery">
                    @foreach ($desktopThumbs as $index => $image)
                        @php $thumbIndex = $tourGalleryIndex($image); @endphp
                        @if ($index < 3)
                            <div class="gallery-item" data-gallery-index="{{ $thumbIndex }}" style="cursor: pointer;">
                                <img src="{{ $image }}" class="img-fluid" alt="{{ __('guidings.gallery_image_alt', ['title' => $guiding->title, 'num' => $index + 2]) }}">
                            </div>
                        @elseif ($index == 3 && $desktopHiddenCount > 0)
                            <div class="gallery-item" data-gallery-index="{{ $thumbIndex }}" style="cursor: pointer;">
                                <img src="{{ $image }}" class="img-fluid" alt="{{ __('guidings.gallery_image_alt', ['title' => $guiding->title, 'num' => $index + 2]) }}">
                                <span class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;">+{{ $desktopHiddenCount }} more</span>
                            </div>
                        @elseif ($index == 3)
                            <div class="gallery-item" data-gallery-index="{{ $thumbIndex }}" style="cursor: pointer;">
                                <img src="{{ $image }}" class="img-fluid" alt="{{ __('guidings.gallery_image_alt', ['title' => $guiding->title, 'num' => $index + 2]) }}">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @if(count($mobileCarouselImages) > 0)
            <div class="camp-gallery__mobile-carousel">
                <div class="camp-gallery__mobile-carousel-scroll">
                    @foreach($mobileCarouselImages as $index => $image)
                        <div class="camp-gallery__mobile-carousel-item" data-gallery-index="{{ $index + 1 }}">
                            <img src="{{ $image }}" alt="{{ __('guidings.gallery_image_alt', ['title' => $guiding->title, 'num' => $index + 2]) }}" loading="lazy" decoding="async">
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <x-gallery.modal
            :id="$tourGalleryId"
            :images="$overallImages"
            :title="$guiding->title"
            type="tour"
            :badge="__('offers.badge_tour')"
            :location="$guiding->location"
            :rating="$average_grandtotal_score ?: null"
            :review-count="(int) ($reviews_count ?? 0)"
            :specs="$tourModalSpecs"
            :price-prefix="__('message.from')"
            :price-display="$tourPriceDisplay"
            :price-suffix="$tourPriceDisplay ? __('vacations.per_person_short') : null"
            cta-url="#book-now"
            :cta-label="__('message.reservation')"
        />
<section class="guidings-description-container mb-5">
        <!-- Left Column -->
    <div class="guidings-descriptions">
    <!-- Title, Rating, and Location -->
    
    <!-- Important Information -->
    <div class="important-info">
        <div class="info-item">
            <i class="fas fa-ship"></i>
            <strong>
                <p class="mb-0">{{$guiding->is_boat ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : __('guidings.shore')}}</p>
            </strong>
        </div>
        <div class="info-item">
            <i>
                <svg class="time-icon" width="18px" height="18px" viewBox="0 0 347.442 347.442" xmlns="http://www.w3.org/2000/svg">
                    <g>
                        <g>
                            <path d="M173.721 347.442c95.919 0 173.721-77.802 173.721-173.721S269.64 0 173.721 0 0 77.802 0 173.721s77.802 173.721 173.721 173.721zm-12.409-272.99c0-6.825 5.584-12.409 12.409-12.409s12.409 5.584 12.409 12.409v93.313l57.39 45.912c5.336 4.281 6.204 12.098 1.923 17.434-2.42 3.04-6.018 4.653-9.679 4.653-2.73 0-5.46-.869-7.755-2.73l-62.043-49.634c-2.916-2.358-4.653-5.894-4.653-9.679v-99.269z"/>
                        </g>
                    </g>
                </svg>
            </i>

            <p class="mb-0">{{ __('guidings.'.$guiding->duration_type) }} : <strong>{{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}</strong></p>                    </div>
        <div class="info-item">
            <i class="fas fa-users"></i>
            <p class="mb-0">@lang('guidings.Number_of_guests') <strong>{{$guiding->max_guests}}</strong></p>
        </div>
    </div>
    
    <!-- Description Section -->
    <div class="description-container card p-3 mb-3 position-relative">
        <div class="description-list tour-overview">
            @if ($guiding->desc_course_of_action)
                <div class="description-item tour-overview__block">
                    <div class="header-container tour-overview__header">
                        <i class="fas fa-book-open" aria-hidden="true"></i>
                        <span>@lang('guidings.Course_Action')</span>
                    </div>
                    <div class="text-wrapper tour-overview__body">
                        {!! clean_html($guiding->desc_course_of_action) !!}
                    </div>
                </div>
            @endif

            @if ($guiding->desc_tour_unique)
                <div class="description-item tour-overview__block">
                    <div class="header-container tour-overview__header">
                        <i class="fas fa-star" aria-hidden="true"></i>
                        <span>@lang('guidings.Tour_Highlights')</span>
                    </div>
                    <div class="text-wrapper tour-overview__body">
                        {!! clean_html($guiding->desc_tour_unique) !!}
                    </div>
                </div>
            @endif

            @if ($guiding->desc_starting_time || $guiding->desc_departure_time || $guiding->desc_meeting_point)
                <div class="tour-overview__facts description-item-row">
                    @if ($guiding->desc_starting_time || $guiding->desc_departure_time)
                        <div class="description-item tour-overview__fact">
                            <div class="header-container tour-overview__header">
                                <i class="fas fa-clock" aria-hidden="true"></i>
                                <span>@lang('guidings.Starting_Time')</span>
                            </div>

                            @if($guiding->desc_departure_time)
                                <div class="tour-overview__pills time-boxes">
                                    @foreach(decode_if_json($guiding->desc_departure_time) as $time)
                                        <span class="tour-overview__pill">{{ __('guidings.'.$time) }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if($guiding->desc_starting_time)
                                <div class="tour-overview__body">
                                    {!! clean_html($guiding->desc_starting_time) !!}
                                </div>
                            @endif
                        </div>
                    @endif

                    @if ($guiding->desc_meeting_point)
                        <div class="description-item tour-overview__fact">
                            <div class="header-container tour-overview__header">
                                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                <span>@lang('guidings.Meeting_Point')</span>
                            </div>
                            <div class="tour-overview__body">
                                {!! clean_html($guiding->desc_meeting_point) !!}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if ($guiding->user->information['languages'])
                <div class="description-item tour-overview__block tour-overview__block--languages">
                    <div class="header-container tour-overview__header">
                        <i class="fas fa-language" aria-hidden="true"></i>
                        <span>@lang('guidings.Languages')</span>
                    </div>
                    <div class="tour-overview__languages">
                        @php
                            $languages = getLanguagesWithFlags($guiding->user->information['languages']);
                        @endphp
                        @foreach($languages as $language)
                            @if($language['has_flag'])
                                <div class="tour-overview__language language-flag-compact" title="{{ $language['name'] }}">
                                    <img src="{{ media_url('flags/' . $language['flag_code'] . '.svg') }}"
                                         alt="{{ $language['name'] }}"
                                         width="22" height="22">
                                    <span class="tour-overview__language-name">{{ $language['name'] }}</span>
                                </div>
                            @else
                                <span class="tour-overview__language tour-overview__language--text language-text-compact">{{ $language['name'] }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
            
    @if($agent->ismobile())
        <div class="contact-card card p-3 mb-4">
            <h5 class="contact-card__title">@lang('guidings.Contact_us')</h5>
            <div class="contact-card__content">
                <p class="">@lang('guidings.Do_you_have_questions_about_this_fishing_tour?')</p>
                <div class="">
                    <div class="contact-info">
                        <i class="fas fa-phone-alt me-2"></i>
                        <a href="tel:+49{{config('cag.contact_num')}}" class="text-decoration-none">+49 (0) {{config('cag.contact_num')}}</a>
                    </div>
                    <a href="#" id="contact-product" class="btn btn-outline-orange" data-bs-toggle="modal" data-bs-target="#contactModal">
                        @lang('guidings.Contact_Form')
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                    @include('partials.product-report.cta', [
                        'reportSourceType' => 'guiding',
                        'reportSourceId' => $guiding->id,
                        'reportedUrl' => url()->current(),
                    ])
                </div>
            </div>
        </div>
    @endif

    @php
        $boatInformation = $guiding->getBoatInformationAttribute();
        $hasNamedPricingExtras = collect(decode_if_json($guiding->pricing_extra) ?: [])
            ->contains(fn ($extra) => is_array($extra) && filled(trim((string) ($extra['name'] ?? ''))));
        $hasInclusionsPanel = !empty(decode_if_json($guiding->inclusions)) || $hasNamedPricingExtras;
        $hasTourInfoPanel = !empty($guiding->target_fish) || !empty($guiding->fishing_methods) || !empty($guiding->water_types);
        $hasBoatPanel = $guiding->is_boat && (
            !$boatInformation->isEmpty()
            || !empty(decode_if_json($guiding->boat_extras))
            || (!empty($guiding->additional_information) && $guiding->additional_information !== null && $guiding->additional_information !== '')
        );
        $hasAdditionalPanel = !empty(decode_if_json($guiding->requirements))
            || !empty($guiding->other_information)
            || !empty($guiding->recommendations)
            || !empty($guiding->style_of_fishing)
            || !empty($guiding->tour_type);
        $accordionOpenInclude = $hasInclusionsPanel;
        $accordionOpenFishing = !$hasInclusionsPanel && $hasTourInfoPanel;
        $accordionOpenBoat = !$hasInclusionsPanel && !$hasTourInfoPanel && $hasBoatPanel;
        $accordionOpenInfo = !$hasInclusionsPanel && !$hasTourInfoPanel && !$hasBoatPanel && $hasAdditionalPanel;
    @endphp

    <div class="tour-panels mb-3">
        {{-- Desktop tabs --}}
        <div class="tabs-container tour-panels__tabs">
            <div class="nav nav-tabs" id="guiding-tab" role="tablist">
                <button class="nav-link active" id="nav-fishing-tab" data-bs-toggle="tab" data-bs-target="#fishing" type="button" role="tab" aria-controls="fishing" aria-selected="true">
                    <i class="fas fa-fish" aria-hidden="true"></i>
                    <span>@lang('guidings.Tour_Info')</span>
                </button>
                @if($hasInclusionsPanel)
                    <button class="nav-link" id="nav-include-tab" data-bs-toggle="tab" data-bs-target="#include" type="button" role="tab" aria-controls="include" aria-selected="false">
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        <span>@lang('guidings.Inclusions')</span>
                    </button>
                @endif
                @if($hasBoatPanel)
                    <button class="nav-link" id="nav-boat-tab" data-bs-toggle="tab" data-bs-target="#boat" type="button" role="tab" aria-controls="boat" aria-selected="false">
                        <i class="fas fa-ship" aria-hidden="true"></i>
                        <span>@lang('guidings.Boat_Details')</span>
                    </button>
                @endif
                <button class="nav-link" id="nav-info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="false">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>@lang('guidings.Additional_Info')</span>
                </button>
            </div>

            <div class="tab-content tour-panels__content" id="guidings-tabs">
                <div class="tab-pane fade show active" id="fishing" role="tabpanel" aria-labelledby="nav-fishing-tab">
                    <div class="tour-panels__pane">
                        @include('pages.guidings.partials.tour-panels.fishing')
                    </div>
                </div>

                @if($hasInclusionsPanel)
                    <div class="tab-pane fade" id="include" role="tabpanel" aria-labelledby="nav-include-tab">
                        <div class="tour-panels__pane">
                            @include('pages.guidings.partials.tour-panels.inclusions')
                        </div>
                    </div>
                @endif

                @if($hasBoatPanel)
                    <div class="tab-pane fade" id="boat" role="tabpanel" aria-labelledby="nav-boat-tab">
                        <div class="tour-panels__pane">
                            @include('pages.guidings.partials.tour-panels.boat', ['boatInformation' => $boatInformation])
                        </div>
                    </div>
                @endif

                <div class="tab-pane fade" id="info" role="tabpanel" aria-labelledby="nav-info-tab">
                    <div class="tour-panels__pane">
                        @include('pages.guidings.partials.tour-panels.additional')
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile accordion --}}
        <div class="accordion tour-panels__accordion" id="guidings-accordion">
            @if($hasInclusionsPanel)
                <div class="accordion-item tour-panels__item">
                    <h2 class="accordion-header" id="headingInclude">
                        <button class="accordion-button {{ $accordionOpenInclude ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInclude" aria-expanded="{{ $accordionOpenInclude ? 'true' : 'false' }}" aria-controls="collapseInclude">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            <span>@lang('guidings.Inclusions')</span>
                        </button>
                    </h2>
                    <div id="collapseInclude" class="accordion-collapse collapse {{ $accordionOpenInclude ? 'show' : '' }}" aria-labelledby="headingInclude" data-bs-parent="#guidings-accordion">
                        <div class="accordion-body">
                            @include('pages.guidings.partials.tour-panels.inclusions')
                        </div>
                    </div>
                </div>
            @endif

            @if($hasTourInfoPanel)
                <div class="accordion-item tour-panels__item">
                    <h2 class="accordion-header" id="headingFishing">
                        <button class="accordion-button {{ $accordionOpenFishing ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFishing" aria-expanded="{{ $accordionOpenFishing ? 'true' : 'false' }}" aria-controls="collapseFishing">
                            <i class="fas fa-fish" aria-hidden="true"></i>
                            <span>@lang('guidings.Tour_Info')</span>
                        </button>
                    </h2>
                    <div id="collapseFishing" class="accordion-collapse collapse {{ $accordionOpenFishing ? 'show' : '' }}" aria-labelledby="headingFishing" data-bs-parent="#guidings-accordion">
                        <div class="accordion-body">
                            @include('pages.guidings.partials.tour-panels.fishing')
                        </div>
                    </div>
                </div>
            @endif

            @if($hasBoatPanel)
                <div class="accordion-item tour-panels__item">
                    <h2 class="accordion-header" id="headingBoat">
                        <button class="accordion-button {{ $accordionOpenBoat ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBoat" aria-expanded="{{ $accordionOpenBoat ? 'true' : 'false' }}" aria-controls="collapseBoat">
                            <i class="fas fa-ship" aria-hidden="true"></i>
                            <span>@lang('guidings.Boat_Details')</span>
                        </button>
                    </h2>
                    <div id="collapseBoat" class="accordion-collapse collapse {{ $accordionOpenBoat ? 'show' : '' }}" aria-labelledby="headingBoat" data-bs-parent="#guidings-accordion">
                        <div class="accordion-body">
                            @include('pages.guidings.partials.tour-panels.boat', ['boatInformation' => $boatInformation])
                        </div>
                    </div>
                </div>
            @endif

            @if($hasAdditionalPanel)
                <div class="accordion-item tour-panels__item">
                    <h2 class="accordion-header" id="headingInfo">
                        <button class="accordion-button {{ $accordionOpenInfo ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfo" aria-expanded="{{ $accordionOpenInfo ? 'true' : 'false' }}" aria-controls="collapseInfo">
                            <i class="fas fa-info-circle" aria-hidden="true"></i>
                            <span>@lang('guidings.Additional_Info')</span>
                        </button>
                    </h2>
                    <div id="collapseInfo" class="accordion-collapse collapse {{ $accordionOpenInfo ? 'show' : '' }}" aria-labelledby="headingInfo" data-bs-parent="#guidings-accordion">
                        <div class="accordion-body">
                            @include('pages.guidings.partials.tour-panels.additional')
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Information Section -->
    @php
        $paymentMethods = collect([
            $guiding->user->bar_allowed ? ['icon' => 'fas fa-money-bill-wave', 'label' => __('booking.cash')] : null,
            $guiding->user->banktransfer_allowed ? ['icon' => 'fas fa-university', 'label' => __('booking.bank_transfer')] : null,
            $guiding->user->paypal_allowed ? ['icon' => 'fab fa-paypal', 'label' => __('booking.paypal')] : null,
        ])->filter()->values();
    @endphp
    @if($paymentMethods->isNotEmpty())
        <div class="tour-payment mb-3">
            <div class="tour-payment__card">
                <h3 class="tour-payment__title">
                    <i class="fas fa-wallet" aria-hidden="true"></i>
                    <span>@lang('booking.how_you_can_pay')</span>
                </h3>

                <div class="tour-payment__highlight">
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <p>@lang('booking.no_payment_now')</p>
                </div>

                <p class="tour-payment__text">@lang('booking.payment_description')</p>

                <ul class="tour-payment__methods">
                    @foreach($paymentMethods as $method)
                        <li class="tour-payment__method">
                            <span class="tour-payment__method-icon" aria-hidden="true">
                                <i class="{{ $method['icon'] }}"></i>
                            </span>
                            <span class="tour-payment__method-label">{{ $method['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Availability Section -->
    <div class="tour-availability">
        <h2 class="mb-3">@lang('guidings.Availability')</h2>
        
        <!-- Calendar Legend -->
        <div class="calendar-legend">
            <div class="legend-item">
                <div class="legend-color legend-available"></div>
                <span>{{ __('checkout.available_for_request') }}</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-blocked"></div>
                <span>{{ __('checkout.blocked') }}</span>
            </div>
        </div>
        
        <div id="lite-datepicker" wire:ignore></div>
    </div>
</div>
    
        <!-- Right Column -->
        <div id="book-now" class="guidings-book">
            @if(!$agent->ismobile())
                @include('pages.guidings.content.bookguiding')
            @endif
        </div>
    </section>

    <!-- Map Section -->
    @if(!empty($guiding->lat) && !empty($guiding->lng))
        <div class="mb-5">
            <x-maps.product
                id="map"
                :lat="$guiding->lat"
                :lng="$guiding->lng"
                :title="translate($guiding->title)"
                height="400px"
                :zoom="10"
                :lazy="true"
                on-marker-click="popup"
            />
        </div>
    @endif

    <div class="mb-5">
        <div class="tour-details-two__about">
            <div class="row">
                <div class="col-md-3 wow fadeInLeft" data-wow-duration="1500ms">
                    <div class="about-one__left">
                        <div class="about-one__img-box">
                            <div class="tour-details__review-comment-top-img">
                                <img class="center-block rounded-circle"
                                     src="{{ guide_profile_photo_url($guiding->user, $guiding) }}"
                                     alt="{{ $guiding->user->firstname }}"
                                     width="180"
                                     height="180"
                                     loading="lazy"
                                     decoding="async">
                            </div>
                            <h4 class="mt-3"
                                style="text-align: center">{{$guiding->user->firstname}}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="text-left">
                        <ul class="list-unstyled mb-3 tour-details-two__overview-bottom-list">
                            <li>
                                <div class="icon-small">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="text">
                                    <p><b>{{ __('guidings.Favorite_fish') }}:</b> {{ translate($guiding->user->information['favorite_fish']) }}
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="icon-small">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="text">
                                    <p>
                                        <b>{{ __('guidings.Languages') }}:</b> {{ translate($guiding->user->information['languages']) }}
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="icon-small">
                                    <i class="fa fa-check"></i>
                                </div>
                                <div class="text">
                                    <p>
                                        <b>{{ __('guidings.Fishing_since') }}:</b> {{ $guiding->user->information['fishing_start_year'] }}
                                    </p>
                                </div>
                            </li>
                        </ul>

                        <p class="js-trigger-more-text"><b>{{ __('guidings.About_me') }}:</b>
                            {!! translate($guiding->aboutme()[0]) !!}
                            {!! translate($guiding->aboutme()[1]) !!}
                        </p>
                        <button class="thm-btn js-btn-more-text" onclick="moreOrLessFunction(this)">{{ __('guidings.More') }} </button>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <div class="guidings-rating mb-3">
        @if($reviews_count > 0)
            {{-- Mobile-only reviews (≤767px). The desktop overview + card rail below are hidden there. --}}
            @include('pages.guidings.partials.reviews-mobile')

            <div class="ratings-head">
                <div class="rating-overview text-center shadow-sm">
                    <div class="ratings-wrapper">
                        <!-- Left side - Score and ratings -->
                        <div class="rating-left">
                            <div class="score-wrapper">
                                <div class="score rating-clickable" id="rating-overview-link">{{ one($average_grandtotal_score, 1) }}</div>
                                <div class="score-label">@lang('guidings.over_10')</div>
                            </div>
                            <div class="rating-info text-center">
                                <div class="rating-badge">{{ getRatingLabel($average_grandtotal_score) }}</div>
                                <div class="rating-count">{{ $reviews_count }} @lang('guidings.Reviews')</div>
                            </div>
                        </div>

                        <!-- Right side - Rating categories -->
                        <div class="rating-categories">
                            <div class="category d-flex align-items-center mb-3">
                                <span class="category-label me-4">@lang('guidings.Overall')</span>
                                <div class="d-flex align-items-center flex-grow-1 gap-2">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar" style="width: {{ ($average_overall_score/10)*100 }}%"></div>
                                    </div>
                                    <span class="rating-value">{{ one($average_overall_score, 1) }}</span>
                                </div>
                            </div>
                            <div class="category d-flex align-items-center mb-3">
                                <span class="category-label me-4">@lang('guidings.Guide')</span>
                                <div class="d-flex align-items-center flex-grow-1 gap-2">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar" style="width: {{ ($average_guide_score/10)*100 }}%"></div>
                                    </div>
                                    <span class="rating-value">{{ one($average_guide_score, 1) }}</span>
                                </div>
                            </div>
                            <div class="category d-flex align-items-center">
                                <span class="category-label me-4">@lang('guidings.Region_Water')</span>
                                <div class="d-flex align-items-center flex-grow-1 gap-2">
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar" style="width: {{ ($average_region_water_score/10)*100 }}%"></div>
                                    </div>
                                    <span class="rating-value">{{ number_format($average_region_water_score, 1) }}</span>
                                </div>
                            </div>
                             <!-- Bottom info section -->
                    <div class="rating-info mt-4 text-center">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong class="mb-0">@lang('guidings.Real_experiences')</strong>
                        </div>
                        <p class="text-muted mb-2 review-disclaimer">
                            @lang('guidings.Real_experiences_description')
                        </p>
                    </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Horizontal scrolling reviews container -->
            <div class="ratings-container" id="ratings-container">
                @foreach($reviews as $review)
                @php
                    $bookingForDate = $review->booking;
                    $reviewDateStr = $bookingForDate
                        ? $bookingForDate->getFormattedBookingDate('M j, Y')
                        : (($review->created_at != null) ? \Carbon\Carbon::parse($review->created_at)->format('M j, Y') : null);
                    $initial = $review->user && $review->user->firstname
                        ? mb_strtoupper(mb_substr($review->user->firstname, 0, 1, 'UTF-8'), 'UTF-8')
                        : '?';
                @endphp
                <div class="ratings-item">
                    <div class="review-card__header">
                        <div class="review-card__avatar" aria-hidden="true">{{ $initial }}</div>
                        <div class="review-card__meta">
                            <p class="review-card__name">{{ $review->user->firstname ?? '—' }}</p>
                            <p class="review-card__date">{{ $reviewDateStr ? strtoupper($reviewDateStr) : '—' }}</p>
                        </div>
                        <div class="review-card__score">
                            <span class="review-card__score-val">{{ number_format($review->grandtotal_score, 1) }}</span><span class="review-card__score-den">/10</span>
                        </div>
                    </div>
                    <hr class="review-card__divider">
                    <div class="review-card__quote comment-content">
                        <p class="description">&ldquo;{{ translate($review->comment) }}&rdquo;</p>
                        <small class="see-more text-orange">{{ __('guidings.See_More') }}</small>
                        <small class="show-less text-orange">{{ __('guidings.Show_Less') }}</small>
                    </div>
                    @if($review->is_automatic)
                        <span class="review-card__badge">@lang('guidings.automatic_review_badge')</span>
                    @endif
                </div>
                @endforeach
            </div>
            
            <!-- Navigation buttons for scrolling through reviews -->
            <div class="ratings-nav">
                <button id="scroll-left" aria-label="Scroll left"><i class="fas fa-chevron-left"></i></button>
                <button id="scroll-right" aria-label="Scroll right"><i class="fas fa-chevron-right"></i></button>
            </div>
        @endif
    </div>

    @if($same_guiding && count($same_guiding ) > 0)
    <section class="tour-details-two mb-5 p-0">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-sm-12">
                    <h3 class="tour-details-two__title">@lang('guidings.More_Fishing') {{$guiding->user->firstname}}</h3>
                    <div class="tours-list__right">
                        <div class="tours-list__inner" id="same-guide-guidings-list">
                            @include('pages.guidings.partials.tour-list-rows', [
                                'guidings' => $same_guiding,
                                'collapseShowMore' => $same_guiding->count() > 2,
                                'numGuests' => $preselectedGuests ?? null,
                                'query' => $productPageQuery ?? [],
                            ])
                        </div>
                        @if($same_guiding->count() > 2)
                        <div class="text-center desktop-view">
                            <button id="showMoreBtn" type="button" class="btn btn-orange mt-3 text-center">{{ __('guidings.See_More') }}</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
    @include('pages.guidings.partials.similar-guidings-rail', [
        'guidings' => $other_guidings ?? collect(),
        'seeAllUrl' => $similar_guidings_see_all_url ?? route('guidings.index'),
        'numGuests' => $preselectedGuests ?? null,
        'query' => $productPageQuery ?? [],
    ])
</div>
<div class="guidings-book-mobile">
    @if($agent->ismobile())
        @include('pages.guidings.content.bookguidingmobile')
    @endif
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">{{ __('contact.shareYourQuestion') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- reCAPTCHA script is rendered by the component --}}
                <div id="contactFormContainer">
                    <form id="contactModalForm">
                        @csrf
                        <input type="hidden" name="source_type" value="guiding">
                        <input type="hidden" name="source_id" value="{{ $guiding->id }}">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="@lang('contact.yourName')" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <input type="email" class="form-control" placeholder="@lang('contact.email')" name="email" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            @include('includes.forms.phone-input', [
                                'placeholder' => 'contact.phone'
                            ])
                        </div>
                        <div class="form-group mb-3">
                            <textarea name="description" class="form-control" rows="4" placeholder="@lang('contact.feedback')" required></textarea>
                        </div>
                        <div class="contact-modal-submit-row d-flex flex-column flex-sm-row flex-wrap justify-content-between align-items-center gap-3">
                            <div class="contact-modal-captcha-wrap w-100 w-sm-auto d-flex justify-content-center justify-content-sm-start">
                                <x-recaptcha />
                            </div>
                            <div class="contact-modal-submit-wrap w-100 w-sm-auto d-flex justify-content-center justify-content-sm-end">
                                <button type="button" id="contactSubmitBtn" class="btn btn-orange">@lang('contact.btnSend')</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Loading Overlay -->
                <div id="contactLoadingOverlay" style="display: none;">
                    <div class="d-flex justify-content-center align-items-center flex-column p-4">
                        <x-loading.inline class="mb-3" label="Loading..." />
                        <p class="text-center">@lang('contact.submitting')...</p>
                    </div>
                </div>
                <div class="alert alert-success mt-3" id="contactSuccessMessage" style="display: none;">
                    @lang('contact.successMessage')
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@section('js_after')
@include('layouts.partials.category-hero-header-script')

<script>
    
let currentCount = 3; // Initial count of displayed items
const totalItems = {{ $same_guiding->count() }};

$(document).ready(function(){
    
    // Function to scroll to reviews with highlight effect
    function scrollToReviews() {
        // Desktop rail is display:none on mobile, where the mobile reviews block takes over.
        const desktopRatings = document.getElementById('ratings-container');
        const ratingsContainer = (desktopRatings && desktopRatings.offsetParent !== null)
            ? desktopRatings
            : (document.getElementById('ratings-container-mobile') || desktopRatings);
        if (ratingsContainer) {
            ratingsContainer.scrollIntoView({ 
                behavior: 'smooth',
                block: 'center'
            });
            
            // Add a subtle highlight effect
            $(ratingsContainer).addClass('highlight-reviews');
            setTimeout(() => {
                $(ratingsContainer).removeClass('highlight-reviews');
            }, 2000);
        }
    }
    
    // Auto-scroll to reviews when reviews link is clicked
    $('#reviews-link').on('click', function(e) {
        e.preventDefault();
        scrollToReviews();
    });
    
    // Auto-scroll to reviews when rating scores are clicked
    $('#rating-score-link, #rating-overview-link').on('click', function() {
        scrollToReviews();
    });

    // "(N reviews)" anchor in the title row: only take over when the desktop rail is hidden (mobile)
    $('a[href="#ratings-container"]').not('#reviews-link').on('click', function(e) {
        const desktopRatings = document.getElementById('ratings-container');
        if (desktopRatings && desktopRatings.offsetParent === null) {
            e.preventDefault();
            scrollToReviews();
        }
    });
    
    // Add this code to check if the button exists
    console.log('Contact button exists:', $('#contactSubmitBtn').length > 0);
    
    // Try a direct event binding approach
    $('#contactSubmitBtn').on('click', function() {
        console.log('Contact button clicked');
        handleContactFormSubmission();
    });
    
    // Also try with the modal shown event to ensure the button exists
    $('#contactModal').on('shown.bs.modal', function() {
        console.log('Modal shown, rebinding button');
        $('#contactSubmitBtn').off('click').on('click', function() {
            console.log('Contact button clicked (from modal shown)');
            handleContactFormSubmission();
        });
    });
    
    function handleContactFormSubmission() {
        const contactForm = document.getElementById('contactModalForm');
        const contactFormContainer = document.getElementById('contactFormContainer');
        const loadingOverlay = document.getElementById('contactLoadingOverlay');
        const successMessage = document.getElementById('contactSuccessMessage');
        
        // Validate form
        if (!contactForm.checkValidity()) {
            contactForm.reportValidity();
            return;
        }
        
        // Get form data
        const formData = new FormData(contactForm);
        
        // Show loading overlay
        contactFormContainer.style.display = 'none';
        loadingOverlay.style.display = 'block';
        
        // Submit form via AJAX
        fetch('{{route('sendcontactmail')}}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading overlay
            loadingOverlay.style.display = 'none';
            
            if (data.success) {
                // Reset form
                contactForm.reset();
                
                // Hide contact modal
                const contactModal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
                contactModal.hide();
                
                // Show thank you modal
                const thankYouModal = new bootstrap.Modal(document.getElementById('contactThankYouModal'));
                thankYouModal.show();
                
                // Reset form display after modal is closed
                setTimeout(() => {
                    contactFormContainer.style.display = 'block';
                }, 500);
            } else {
                contactError.style.display = 'block';
                contactError.innerHTML = data.message;
                contactFormContainer.style.display = 'block';
            }
        })
        .catch(error => {
            // Hide loading overlay and show form again on error
            loadingOverlay.style.display = 'none';
            contactFormContainer.style.display = 'block';
            
            contactError.style.display = 'block';
            contactError.innerHTML = error.message;
        });
    }
    
    // Simple drag scrolling for the ratings container
    const ratingsContainer = document.getElementById('ratings-container');
    
    if (ratingsContainer) {
        let isDragging = false;
        let startPosition = 0;
        let scrollLeftPosition = 0;
        
        // Desktop mouse events
        $(ratingsContainer).on('mousedown', function(e) {
            isDragging = true;
            startPosition = e.pageX;
            scrollLeftPosition = ratingsContainer.scrollLeft;
            $(ratingsContainer).css('cursor', 'grabbing');
            e.preventDefault(); // Prevent text selection
        });
        
        $(document).on('mouseup', function() {
            isDragging = false;
            $(ratingsContainer).css('cursor', 'grab');
        });
        
        $(document).on('mousemove', function(e) {
            if (!isDragging) return;
            const dx = e.pageX - startPosition;
            ratingsContainer.scrollLeft = scrollLeftPosition - dx;
            e.preventDefault(); // Prevent text selection during drag
        });
        
        // Prevent click events from firing when dragging
        $(ratingsContainer).find('a, button').on('click', function(e) {
            if (isDragging) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
        
        // Mobile touch events are already handled by the browser
    }
    
    // "See more" for same-guide listing (catalog list rows; first two visible until expanded)
    const showMoreBtn = document.getElementById("showMoreBtn");
    const sameGuideList = document.getElementById("same-guide-guidings-list");
    const items = sameGuideList ? sameGuideList.querySelectorAll(".guiding-product-list-item") : [];
    let isExpanded = false;

    if (showMoreBtn && sameGuideList) {
        showMoreBtn.addEventListener("click", function () {
            isExpanded = !isExpanded;
            items.forEach((item, index) => {
                item.classList.toggle("is-visible", isExpanded || index < 2);
            });
            showMoreBtn.textContent = isExpanded
                ? @json(__('guidings.Show_Less'))
                : @json(__('guidings.See_More'));
        });
    }

    // Horizontal scrolling for reviews
    const scrollLeftBtn = document.getElementById('scroll-left');
    const scrollRightBtn = document.getElementById('scroll-right');
    
    if (ratingsContainer && scrollLeftBtn && scrollRightBtn) {
        // Scroll amount (width of one review card + gap)
        const scrollAmount = 320; // 300px card width + 20px gap
        
        scrollLeftBtn.addEventListener('click', () => {
            ratingsContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });
        
        scrollRightBtn.addEventListener('click', () => {
            ratingsContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });
    }
    
});


const moreText = document.querySelector(".js-trigger-more-text");
let expanded = false;

function moreOrLessFunction(e) {
    if (!expanded) {
        expanded = true;
        moreText.classList.add('expand-text');
        e.innerHTML = '{{__("guidings.Less")}}';
    } else {
        expanded = false;
        moreText.classList.remove('expand-text');
        e.innerHTML = '{{__("guidings.More")}}';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const seeMoreLabel = @json(__('guidings.See_More'));
    const seeLessLabel = @json(__('guidings.Show_Less'));
    const descriptionItems = document.querySelectorAll(".description-item .text-wrapper");
    if (descriptionItems) {
        descriptionItems.forEach((item) => {
            const originalText = item.innerHTML.trim();
            const words = originalText.split(/\s+/);
            if (words.length > 30) {
                const truncatedText = words.slice(0, 30).join(" ") + "... ";
                item.innerHTML = truncatedText;
                const toggle = document.createElement("small");
                toggle.textContent = seeMoreLabel;
                toggle.style.cursor = "pointer";
                toggle.classList.add("text-orange", "tour-overview__toggle");
                toggle.onclick = () => {
                    const isExpanded = toggle.textContent === seeLessLabel;
                    item.innerHTML = isExpanded ? truncatedText : originalText + " ";
                    toggle.textContent = isExpanded ? seeMoreLabel : seeLessLabel;
                    item.appendChild(toggle);
                };
                item.appendChild(toggle);
            }
        });
    }

    // Reviews disclaimer See More / Show Less
    const disclaimer = document.querySelector('.review-disclaimer');
    if (disclaimer) {
        // Only shorten on mobile
        if (window.innerWidth < 768) {
            const fullText = disclaimer.innerText.trim();
            const words = fullText.split(/\s+/);
            const limit = 3.85;
            if (words.length > limit) {
                const truncated = words.slice(0, limit).join(' ') + '... ';
                disclaimer.innerText = truncated;
                const toggle = document.createElement('small');
                toggle.textContent = 'See More';
                toggle.style.cursor = 'pointer';
                toggle.classList.add('text-orange');
                let isExpanded = false;
                toggle.addEventListener('click', function() {
                    isExpanded = !isExpanded;
                    disclaimer.innerText = isExpanded ? fullText + ' ' : truncated;
                    toggle.textContent = isExpanded ? 'Show Less' : 'See More';
                    disclaimer.appendChild(toggle);
                });
                disclaimer.appendChild(toggle);
            }
        }
    }

    // Add null check for comment content elements
    const commentContents = document.querySelectorAll('.comment-content');
    if (commentContents) {
        commentContents.forEach(content => {
            const descriptionElement = content.querySelector('.description');
            const seeMore = content.querySelector('.see-more');
            const showLess = content.querySelector('.show-less');

            if (descriptionElement && seeMore && showLess) {
                const fullText = descriptionElement.innerText;
                const words = fullText.split(" ");

                if (words.length > 20) {
                    descriptionElement.innerText = words.slice(0, 20).join(" ") + "...";
                    seeMore.style.display = 'inline';
                    showLess.style.display = 'none';

                    const toggleText = (isExpanded) => {
                        descriptionElement.innerText = isExpanded ? fullText : words.slice(0, 20).join(" ") + "...";
                        seeMore.style.display = isExpanded ? 'none' : 'inline';
                        showLess.style.display = isExpanded ? 'inline' : 'none';
                    };

                    seeMore.addEventListener('click', () => toggleText(true));
                    showLess.addEventListener('click', () => toggleText(false));
                } else {
                    seeMore.style.display = 'none';
                    showLess.style.display = 'none';
                }
            }
        });
    }
    
    const showMoreBtn = document.getElementById('show-more');
    if (showMoreBtn) {
        showMoreBtn.addEventListener('click', function() {
            const container = document.getElementById('same-guidings-container');
            
        // Fetch the next set of items
        for (let i = currentCount; i < currentCount + 3 && i < totalItems; i++) {
            const guiding = @json($same_guiding); // Convert PHP variable to JavaScript
            const newGuiding = guiding[i];

            const colDiv = document.createElement('div');
            colDiv.className = 'col-md-6 mb-3';
            colDiv.innerHTML = `
                <div class="card">
                    <img src="${newGuiding.thumbnail_path}" class="card-img-top" alt="${newGuiding.title}">
                    <div class="card-body">
                        <h5 class="card-title">${newGuiding.title}</h5>
                        <p class="card-text">${newGuiding.location}</p>
                        <a href="/guidings/offer/${newGuiding.slug}" class="btn btn-primary">Details</a>
                    </div>
                </div>
            `;
            container.appendChild(colDiv);
        }

        currentCount += 3; // Update the count of displayed items

        // Hide the button if all items are displayed
        if (currentCount >= totalItems) {
            this.style.display = 'none';
            }
        });
    }
    
    const blockedEvents = JSON.parse('{!! json_encode($blocked_events) !!}');

    let lockDays = [];
    if (blockedEvents && typeof blockedEvents === 'object') {
        lockDays = Object.values(blockedEvents).flatMap(event => {
            const fromDate = new Date(event.from);
            const dueDate = new Date(event.due);

            // Create an array of all dates in the range
            const dates = [];
            for (let d = new Date(fromDate); d <= dueDate; d.setDate(d.getDate() + 1)) {
                dates.push(d.toISOString().split('T')[0]); // Format as YYYY-MM-DD
            }
            return dates;
        });
    }

    const picker = new Litepicker({
        element: document.getElementById('lite-datepicker'),
        inlineMode: true,
        singleDate: true,
        numberOfColumns: initCheckNumberOfColumns(),
        numberOfMonths: initCheckNumberOfColumns(),
        minDate: new Date(new Date().getTime() + 24 * 60 * 60 * 1000),
        startDate: null, // Explicitly set to null to prevent default date selection
        lockDays: lockDays, // Use the dynamically calculated blocked days
        lang: '{{app()->getLocale()}}',
        firstDay: 1, // Start week on Monday (0 = Sunday, 1 = Monday)
        showWeekNumbers: false, // Disable week numbers to show all 7 days
        lockDaysFormat: 'YYYY-MM-DD',
        disallowLockDaysInRange: true,
        setup: (picker) => {
            window.addEventListener('resize', () => {
                picker.setOptions({
                    numberOfColumns: initCheckNumberOfColumns(),
                    numberOfMonths: initCheckNumberOfColumns()
                });
            });
        },
        onSelect: (date1, date2) => {
            // Dispatch custom event when a date is selected
            if (date1) {
                let selectedDate;
                
                try {
                    // Handle different possible date formats from Litepicker
                    if (typeof date1 === 'string') {
                        selectedDate = date1;
                    } else if (date1.toISOString) {
                        selectedDate = date1.toISOString().split('T')[0];
                    } else if (date1.format) {
                        selectedDate = date1.format('YYYY-MM-DD');
                    } else if (date1.dateInstance) {
                        selectedDate = date1.dateInstance.toISOString().split('T')[0];
                    } else {
                        // Try to create a proper date
                        const newDate = new Date(date1);
                        selectedDate = newDate.toISOString().split('T')[0];
                    }
                    
                    console.log('Date selected:', selectedDate); // Debug log
                    window.dispatchEvent(new CustomEvent('dateSelected', {
                        detail: { date: selectedDate }
                    }));
                } catch (error) {
                    console.error('Error formatting selected date:', error, date1);
                }
            }
        },
        onClear: () => {
            // Dispatch custom event when date is cleared/deselected
            console.log('Date cleared'); // Debug log
            window.dispatchEvent(new CustomEvent('dateDeselected'));
        }
    });
    
    // Fallback: Listen for clicks on calendar days
    setTimeout(() => {
        const calendarContainer = document.getElementById('lite-datepicker');
        if (calendarContainer) {
            calendarContainer.addEventListener('click', function(event) {
                // Check if clicked element is a day item
                if (event.target.classList.contains('day-item') && !event.target.classList.contains('is-locked')) {
                    setTimeout(() => {
                        // Try to get the selected date from the picker
                        try {
                            const pickerDate = picker.getDate();
                            if (pickerDate) {
                                let selectedDate;
                                
                                // Handle different possible return types
                                if (typeof pickerDate === 'string') {
                                    selectedDate = pickerDate;
                                } else if (pickerDate.toISOString) {
                                    selectedDate = pickerDate.toISOString().split('T')[0];
                                } else if (pickerDate.format) {
                                    selectedDate = pickerDate.format('YYYY-MM-DD');
                                } else {
                                    // Try to create a date from the clicked element
                                    const dayElement = event.target;
                                    const dayNumber = dayElement.textContent;
                                    const monthContainer = dayElement.closest('.month-item');
                                    if (monthContainer) {
                                        const monthElement = monthContainer.querySelector('.month-item-name');
                                        if (monthElement) {
                                            const monthText = monthElement.textContent;
                                            // This is a fallback - might need adjustment based on your calendar structure
                                            console.log('Could not get date from picker, manual extraction needed');
                                            return;
                                        }
                                    }
                                }
                                
                                if (selectedDate) {
                                    console.log('Fallback date selected:', selectedDate); // Debug log
                                    window.dispatchEvent(new CustomEvent('dateSelected', {
                                        detail: { date: selectedDate }
                                    }));
                                }
                            }
                                                 } catch (error) {
                            console.error('Error getting date from picker:', error);
                        }
                    }, 100); // Small delay to ensure picker state is updated
                }
            });
        }
    }, 1000); // Wait for calendar to be fully rendered
    
    // Alternative approach: Watch for changes in the calendar using MutationObserver
    setTimeout(() => {
        const calendarContainer = document.getElementById('lite-datepicker');
        if (calendarContainer) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' || mutation.type === 'childList') {
                        // Look for selected day items
                        const selectedDay = calendarContainer.querySelector('.day-item.is-selected');
                        if (selectedDay && !selectedDay.classList.contains('is-locked')) {
                            // Extract date from the selected day
                            const dayNumber = selectedDay.textContent.trim();
                            const monthContainer = selectedDay.closest('.month-item');
                            
                            if (monthContainer && dayNumber) {
                                // Get year and month from the calendar structure
                                const monthNameElement = monthContainer.querySelector('.month-item-name');
                                const yearElement = monthContainer.querySelector('.month-item-year');
                                
                                if (monthNameElement && yearElement) {
                                    const monthName = monthNameElement.textContent.trim();
                                    const year = yearElement.textContent.trim();
                                    
                                    // Convert month name to number (this might need adjustment based on your locale)
                                    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                                                   'July', 'August', 'September', 'October', 'November', 'December'];
                                    const monthNumber = months.indexOf(monthName) + 1;
                                    
                                    if (monthNumber > 0) {
                                        const selectedDate = `${year}-${monthNumber.toString().padStart(2, '0')}-${dayNumber.padStart(2, '0')}`;
                                        console.log('Observer detected selected date:', selectedDate);
                                        
                                        window.dispatchEvent(new CustomEvent('dateSelected', {
                                            detail: { date: selectedDate }
                                        }));
                                    }
                                }
                            }
                        }
                    }
                });
            });
            
            observer.observe(calendarContainer, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class']
            });
        }
    }, 1500); // Wait a bit longer for calendar to be fully rendered
});

function initCheckNumberOfColumns() {
    return window.innerWidth < 768 ? 1 : 2;
}
</script>

{{-- Guiding listing cards: mobile 1/N counter (lightbox handled by listingGalleryModal.js) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-counter-for]').forEach(function (counter) {
        var carouselId = counter.getAttribute('data-counter-for');
        var total      = parseInt(counter.getAttribute('data-total'), 10);
        var carousel   = document.getElementById(carouselId);

        if (!carousel) return;

        carousel.addEventListener('slide.bs.carousel', function (e) {
            counter.textContent = (e.to + 1) + '/' + total;
        });
    });
});
</script>
@include('partials.product-report.modal', [
    'reportSourceType' => 'guiding',
    'reportSourceId' => $guiding->id,
    'reportedUrl' => url()->current(),
])
@endsection

