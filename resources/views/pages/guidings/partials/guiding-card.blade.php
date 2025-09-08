@foreach($guidings as $guiding)
<div class="row m-0 mb-2 guiding-list-item">
    <div class="col-md-12">
        <div class="row p-2 border shadow-sm bg-white rounded">
            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                    <div class="carousel-inner">
                        @php
                            $galleryImages = $guiding->cached_gallery_images ?? json_decode($guiding->gallery_images);
                        @endphp
                        @if(count($galleryImages))
                            @foreach($galleryImages as $index => $gallery_image_link)
                                <div class="carousel-item @if($index == 0) active @endif">
                                    <img class="d-block lazy" 
                                         data-src="{{ $gallery_image_link }}"
                                         src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                         alt="{{ translate($guiding->title) }}"
                                         loading="lazy"
                                         width="800"
                                         height="600"
                                         style="aspect-ratio: 4/3; object-fit: cover;">
                                </div>
                            @endforeach
                        @endif
                    </div>

                    @if(count($galleryImages) > 1)
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
            <div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 px-md-3 pt-md-2">
                <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug])}}">
                    <div class="guidings-item">
                        <div class="guidings-item-title">
                            <h5 class="fw-bolder text-truncate">{{ Str::limit(translate($guiding->title), 70) }}</h5>
                            <span class="truncate"><i class="fas fa-map-marker-alt me-2"></i>{{ $guiding->location }}</span>                                      
                        </div>
                        @php
                            $averageRating = $guiding->cached_average_rating ?? $guiding->user->average_rating();
                            $reviewCount = $guiding->cached_review_count ?? $guiding->user->reviews->count();
                        @endphp
                        @if ($averageRating)
                            <div class="ave-reviews-row">
                                <div class="ratings-score">
                                <span class="rating-value">{{number_format($averageRating, 1)}}</span>
                            </div>
                                <span class="mb-1">
                                    ({{$reviewCount}} reviews)
                                </span>
                            </div>
                        @else
                            <div class="no-reviews"><span>@lang('guidings.no_reviews')</span></div>
                        @endif
                    </div>
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
                            {{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                            </div>
                        </div>
                        <div class="guidings-icon-container"> 
                            <img src="{{asset('assets/images/icons/fish-new.svg')}}" height="20" width="20" alt="" />
                            <div class="">
                                <div class="tours-list__content__trait__text" >
                                    @php
                                    $guidingTargets = $guiding->cached_target_fish_names ?? $guiding->getTargetFishNames($targetsMap ?? null);
                                    $targetNames = collect($guidingTargets)->pluck('name')->toArray();
                                    @endphp
                                    
                                    @if(!empty($targetNames))
                                        {{ implode(', ', $targetNames) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="guidings-icon-container">
                            <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="20" width="20" alt="" />
                            <div class="">
                                <div class="tours-list__content__trait__text" >
                                    {{ $guiding->cached_boat_type_name ?? ($guiding->is_boat ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : __('guidings.shore')) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="inclusions-price">
                        <div class="guidings-inclusions-container">
                            @php
                                $inclussions = $guiding->cached_inclusion_names ?? $guiding->getInclusionNames();
                            @endphp
                            @if(!empty($inclussions))
                            <div class="guidings-included">
                                <strong>@lang('guidings.Whats_Included')</strong>
                                <div class="inclusions-list">
                                    @php
                                        $maxToShow = 3; // Maximum number of inclusions to display
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
            </div>
        </div>
    </div>
</div>
@endforeach