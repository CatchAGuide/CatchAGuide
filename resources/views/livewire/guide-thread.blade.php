<div>
    {{-- Be like water. --}}
    <style>
        .carousel.slide img {
            height: 250px;
            object-fit: cover;
            width: 100%;
            background: black;
        }

        .carousel-item {
            aspect-ratio: 4/3;
            background-color: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
        }

        .carousel-item-next, .carousel-item-prev, .carousel-item.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-item img {
            border-radius: 8px;
        }

        /* Remove blue hover glow and fix link styling */
        a:hover {
            color: black !important;
        }

        a:focus, a:active {
            outline: none !important;
            box-shadow: none !important;
        }

        .text-decoration-none:hover {
            text-decoration: none !important;
        }

        /* Price styling - primarily using Bootstrap d-flex flex-column align-items-end */
        .guiding-item-price h5 {
            font-size: clamp(16px, 2vw, 20px);
        }

        /* Button styling */
        .theme-primary, .btn-theme-new {
            background-color: #E8604C !important;
            color: #fff !important;
            border: 2px solid #E8604C !important;
        }

        .btn-outline-theme {
            color: #E8604C !important;
            border-color: #E8604C !important;
            background-color: transparent !important;
        }

        .btn-outline-theme:hover {
            background-color: #E8604C !important;
            color: #fff !important;
        }

        /* Price color styling */
        .color-primary {
            color: #E8604C !important;
        }

        /* Minimal custom styling - mostly using Bootstrap utilities */
        .guiding-list-item .row {
            min-height: 200px;
        }

        /* Theme gray color for icons */
        .thm-gray-icon {
            color: var(--thm-gray, #6c757d);
            font-size: 14px;
            width: 16px;
        }

        /* Responsive adjustments for mobile */
        @media (max-width: 767px) {
            .thm-gray-icon {
                font-size: 12px;
                width: 14px;
            }
        }
    </style>

        @foreach($guidings as $guiding)
        <div class="row m-0 mb-2">
            <div class="col-md-12">
                <div class="row p-2 border shadow-sm bg-white rounded">
                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                        <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                            <div class="carousel-inner">
                                @if(count(get_galleries_image_link($guiding)))
                                    @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                                        <div class="carousel-item @if($index == 0) active @endif">
                                            <img  class="d-block" src="{{asset($gallery_image_link)}}">
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            @if(count(get_galleries_image_link($guiding)) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            @endif
                        </div>

                    </div>
                    <div class="guiding-item-desc col-12 col-sm-12 col-md-5 col-lg-5 col-xl-5 col-xxl-5 p-3">
                        <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug])}}" class="text-decoration-none">
                            <!-- Title and Location -->
                            <div class="mb-3">
                                <h5 class="fw-bold text-dark mb-1">{{ translate($guiding->title) }}</h5>
                                <div class="text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ translate($guiding->location) }}
                                </div>
                                @if ($guiding->user->average_rating())
                                    <div class="text-muted small mt-1">
                                        {{number_format($guiding->user->average_rating(), 1)}} ⭐ ({{$guiding->user->reviews->count()}} reviews)
                                    </div>
                                @else
                                    <div class="text-muted small mt-1">@lang('guidings.no_reviews')</div>
                                @endif
                            </div>

                            <!-- Two Column Details -->
                            <div class="row g-0">
                                <!-- Left Column -->
                                <div class="col-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-fish me-2 thm-gray-icon"></i>
                                        <span class="small text-dark">
                                            @php
                                            $guidingTargets = collect($guiding->getTargetFishNames())->pluck('name')->toArray();
                                            @endphp
                                            @if(!empty($guidingTargets))
                                                {{ implode(', ', array_slice($guidingTargets, 0, 2)) }}
                                            @else
                                                Fishing
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-ship me-2 thm-gray-icon"></i>
                                        <span class="small text-dark">{{$guiding->is_boat ? __('guidings.boat') : __('guidings.shore')}}</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-clock me-2 thm-gray-icon"></i>
                                        <span class="small text-dark">{{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}</span>
                                    </div>
                                </div>
                                
                                <!-- Right Column -->
                                <div class="col-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-water me-2 thm-gray-icon"></i>
                                        <span class="small text-dark">
                                            @php
                                            $guidingWaters = $guiding->guidingWaters->pluck('name')->toArray();
                                            if(app()->getLocale() == 'en'){
                                                $guidingWaters = $guiding->guidingWaters->pluck('name_en')->toArray();
                                            }
                                            @endphp
                                            @if(!empty($guidingWaters))
                                                {{ implode(', ', array_slice($guidingWaters, 0, 1)) }}
                                            @else
                                                Water
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-user me-2 thm-gray-icon"></i>
                                        <span class="small text-dark">{{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-check me-2 text-success"></i>
                                        <span class="small text-success">
                                            @if(!empty($guiding->getInclusionNames()))
                                                {{ $guiding->getInclusionNames()[0]['name'] ?? 'Catch' }}
                                            @else
                                                Catch
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-12 col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3 mt-1 d-flex flex-column justify-content-between align-items-end">
                        <div class="guiding-item-price text-end mb-3">
                            <h5 class="m-0 color-primary fw-bold text-nowrap">@lang('message.from') {{$guiding->getLowestPrice()}}€ p.P.</h5>
                        </div>
                        <div class="d-flex flex-column w-100 gap-2">
                            <a class="btn theme-primary btn-theme-new btn-sm" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">Details</a>
                            <a class="btn btn-sm {{ (auth()->check() ? (auth()->user()->isWishItem($guiding->id) ? 'btn-danger' : 'btn-outline-theme ') : 'btn-outline-theme') }}" href="{{ route('wishlist.add-or-remove', $guiding->id) }}">
                                {{ (auth()->check() ? (auth()->user()->isWishItem($guiding->id) ? 'Added to Favorites' : 'Add to Favorites') : 'Add to Favorites') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        @if($guidings->hasMorePages())
            <div class="d-flex justify-content-center align-items-center">
                <button wire:loading.remove class="btn theme-primary" wire:click="loadMore()" wire:loading.class="hidden" wire:loading.attr="disabled">
                    View more
                </button>
                <div wire:loading.delay>
                    <div class="d-flex align-items-center">
                        <div>
                            Please wait...
                        </div>
                        <span class="mx-2" id="cover-spin"></span>
                    </div>
                </div>


            </div>
        @endif

</div>
