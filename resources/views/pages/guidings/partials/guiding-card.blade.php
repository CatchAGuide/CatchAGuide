@foreach($guidings as $guiding)
<div class="row m-0 mb-3 guiding-list-item">
    <div class="col-md-12">
        <div class="row p-2 border shadow-sm bg-white rounded guiding-card-wrapper">
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                @php
                    $galleryImages = $guiding->cached_gallery_images ?? json_decode($guiding->gallery_images);
                    $averageRating = $guiding->cached_average_rating ?? $guiding->user->average_rating();
                    $reviewCount   = $guiding->cached_review_count ?? $guiding->user->reviews->count();
                    $guidingTargets = $guiding->cached_target_fish_names ?? $guiding->getTargetFishNames($targetsMap ?? null);
                    $targetNames    = collect($guidingTargets)->pluck('name')->toArray();
                    $guidingWaters  = $guiding->getWaterNames();
                    $waterNames     = collect($guidingWaters)->pluck('name')->toArray();
                    $inclussions    = $guiding->cached_inclusion_names ?? $guiding->getInclusionNames();
                    $totalImages    = count($galleryImages);
                @endphp
                <div id="carouselExampleControls-{{$guiding->id}}"
                     class="carousel slide gc-carousel"
                     data-bs-ride="carousel"
                     data-bs-interval="false"
                     data-guiding-gallery="{{ $guiding->id }}"
                     data-gallery-images='@json((array)$galleryImages)'>
                    <div class="carousel-inner">
                        @if($totalImages)
                            @foreach($galleryImages as $index => $gallery_image_link)
                                <div class="carousel-item @if($index == 0) active @endif">
                                    <img class="d-block lazy"
                                         data-src="{{ $gallery_image_link }}"
                                         src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                         alt="{{ $guiding->title }}"
                                         loading="lazy"
                                         width="800"
                                         height="600"
                                         data-guiding-open-modal
                                         style="aspect-ratio: 4/3; object-fit: cover; cursor: pointer;">
                                </div>
                            @endforeach
                        @endif
                    </div>

                    @if($totalImages > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @endif

                    {{-- Mobile-only: Rating badge overlay --}}
                    @if($averageRating)
                    <div class="gc-mob-rating-badge d-flex d-md-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span>{{ number_format($averageRating, 1) }}</span>
                        <span class="gc-mob-rating-count">({{ $reviewCount }})</span>
                    </div>
                    @endif

                    {{-- Mobile-only: Image counter --}}
                    @if($totalImages > 1)
                    <div class="gc-mob-img-counter d-flex d-md-none"
                         data-counter-for="carouselExampleControls-{{$guiding->id}}"
                         data-total="{{ $totalImages }}">
                        1/{{ $totalImages }}
                    </div>
                    @endif
                </div>

                {{-- Gallery lightbox modal --}}
                <div class="guiding-gallery-modal" data-guiding-modal="{{ $guiding->id }}">
                    <div class="guiding-gallery-modal__content">
                        <button class="guiding-gallery-modal__close">&times;</button>
                        <button class="guiding-gallery-modal__prev">&#10094;</button>
                        <button class="guiding-gallery-modal__next">&#10095;</button>
                        <img class="guiding-gallery-modal__image" src="" alt="{{ $guiding->title }}">
                        <div class="guiding-gallery-modal__counter">
                            <span class="guiding-gallery-modal__current">1</span> / <span class="guiding-gallery-modal__total">{{ $totalImages }}</span>
                        </div>
                    </div>
                </div>

            </div>
            <div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 px-md-3 pt-md-2">
                <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug])}}">
                    <div class="guidings-item">
                        <div class="guidings-item-title">
                            <h5 class="fw-bolder text-truncate">{{ Str::limit($guiding->title, 70) }}</h5>
                            <span class="truncate"><i class="fas fa-map-marker-alt me-2"></i>{{ $guiding->location }}</span>                                      
                        </div>
                        @if ($averageRating)
                            <div class="ave-reviews-row">
                                <div class="ratings-score">
                                    <span class="rating-value">{{ number_format($averageRating, 1) }}</span>
                                </div>
                                <span class="mb-1">({{ $reviewCount }} reviews)</span>
                            </div>
                        @else
                            <div class="no-reviews"><span>@lang('guidings.no_reviews')</span></div>
                        @endif
                    </div>

                    {{-- Mobile-only: Fish type tags as pill badges --}}
                    @if(!empty($targetNames))
                    <div class="gc-mob-fish-tags d-flex d-md-none">
                        @foreach(array_slice($targetNames, 0, 4) as $fishName)
                            <span class="gc-mob-fish-tag">{{ $fishName }}</span>
                        @endforeach
                    </div>
                    @endif

                    <div class="guidings-item-icon">
                        <div class="guidings-icon-container"> 
                            <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" alt="" />
                            <div class="">
                                {{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}
                            </div>
                        </div>
                        <div class="guidings-icon-container"> 
                            <img src="{{asset('assets/images/icons/user-new.svg')}}" height="20" width="20" alt="" />
                            <div class="">
                                Max {{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                            </div>
                        </div>
                        <div class="guidings-icon-container"> 
                            <img src="{{asset('assets/images/icons/pelagic.png')}}" height="20" width="20" alt="" />
                            <div class="">
                                <div class="tours-list__content__trait__text">
                                    @if(!empty($waterNames))
                                        {{ implode(', ', $waterNames) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="guidings-icon-container">
                            <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="20" width="20" alt="" />
                            <div class="">
                                <div class="tours-list__content__trait__text">
                                    {{ $guiding->cached_boat_type_name ?? ($guiding->is_boat ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : __('guidings.shore')) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="inclusions-price">
                        <div class="guidings-inclusions-container">
                            @if(!empty($inclussions))
                            <div class="guidings-included">
                                <strong>@lang('guidings.Whats_Included')</strong>
                                <div class="inclusions-list">
                                    @php
                                        $maxToShow = 2;
                                    @endphp
                                    @foreach ($inclussions as $index => $inclussion)
                                        @if ($index < $maxToShow)
                                            <span class="inclusion-item"><i class="fa fa-check"></i>{{ $inclussion['name'] }}</span>
                                        @endif
                                    @endforeach
                                    @if (count($inclussions) > $maxToShow)
                                        <span class="inclusion-item">+{{ count($inclussions) - $maxToShow }} more</span>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="guiding-item-price">
                            <h5 class="mr-1 fw-bold text-end"><span class="p-1">@lang('message.from') {{$guiding->getLowestPrice()}}€ p.P.</span></h5>
                            <div class="d-none d-flex flex-column mt-4">
                            </div>
                        </div>
                    </div>    
                </a>

                {{-- Mobile-only: CTA footer (price + Book Now button) --}}
                <div class="gc-mob-footer d-flex d-md-none">
                    <div class="gc-mob-price">
                        <span class="gc-mob-price-from">@lang('message.from')</span>
                        <span class="gc-mob-price-amount">€{{ $guiding->getLowestPrice() }} p.P.</span>
                    </div>
                    <div class="gc-mob-cta">
                        <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug]) }}"
                           class="gc-mob-info-btn"
                           title="@lang('guidings.more_info')">
                            <i class="fas fa-info"></i>
                        </a>
                        <form method="POST" action="{{ route('checkout') }}" style="margin:0;">
                            @csrf
                            <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                            <input type="hidden" name="person" value="1">
                            <input type="hidden" name="selected_date" value="">
                            <button type="submit" class="gc-mob-book-btn">@lang('vacations.book_now')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
