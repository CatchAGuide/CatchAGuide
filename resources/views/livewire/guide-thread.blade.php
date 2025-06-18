<div>
    <!--Tours List Start-->
    <section class="tours-list">
        <div class="container">
            <div class="row">
                <!-- Desktop Filter Sidebar -->
                <div class="col-lg-3 d-none d-lg-block">        
                    {{-- Filters Card --}}
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">@lang('destination.filter_by'):</h6>
                        </div>
                        <div class="card-body">
                            {{-- Target Fish --}}
                            <div class="filter-group mb-4">
                                <h6 class="fw-bold mb-3">{{translate('Target Fish')}}</h6>
                                <div class="filter-scroll">
                                    @foreach($this->filterOptions['targets'] as $target)
                                        <div class="form-check mb-2">
                                            <input wire:model="targetFish" 
                                                   type="checkbox" 
                                                   class="form-check-input" 
                                                   value="{{ $target['id'] }}" 
                                                   id="fish_{{ $target['id'] }}">
                                            <label class="form-check-label" for="fish_{{ $target['id'] }}">
                                                {{ $target['name'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Methods --}}
                            <div class="filter-group mb-4">
                                <h6 class="fw-bold mb-3">{{translate('Methods')}}</h6>
                                <div class="filter-scroll">
                                    @foreach($this->filterOptions['methods'] as $method)
                                        <div class="form-check mb-2">
                                            <input wire:model="methods" 
                                                   type="checkbox" 
                                                   class="form-check-input" 
                                                   value="{{ $method['id'] }}" 
                                                   id="method_{{ $method['id'] }}">
                                            <label class="form-check-label" for="method_{{ $method['id'] }}">
                                                {{ $method['name'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Water Types --}}
                            <div class="filter-group mb-4">
                                <h6 class="fw-bold mb-3">{{translate('Water Types')}}</h6>
                                <div class="filter-scroll">
                                    @foreach($this->filterOptions['waters'] as $water)
                                        <div class="form-check mb-2">
                                            <input wire:model="waterTypes" 
                                                   type="checkbox" 
                                                   class="form-check-input" 
                                                   value="{{ $water['id'] }}" 
                                                   id="water_{{ $water['id'] }}">
                                            <label class="form-check-label" for="water_{{ $water['id'] }}">
                                                {{ $water['name'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Duration --}}
                            <div class="filter-group mb-4">
                                <h6 class="fw-bold mb-3">{{translate('Duration')}}</h6>
                                <div class="form-check mb-2">
                                    <input wire:model="durationTypes" type="checkbox" class="form-check-input" value="half_day" id="duration_half">
                                    <label class="form-check-label" for="duration_half">
                                        @lang('guidings.half_day')
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input wire:model="durationTypes" type="checkbox" class="form-check-input" value="full_day" id="duration_full">
                                    <label class="form-check-label" for="duration_full">
                                        @lang('guidings.full_day')
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input wire:model="durationTypes" type="checkbox" class="form-check-input" value="multi_day" id="duration_multi">
                                    <label class="form-check-label" for="duration_multi">
                                        @lang('guidings.multi_day')
                                    </label>
                                </div>
                            </div>
                            
                            {{-- Number of People --}}
                            <div class="filter-group mb-4">
                                <h6 class="fw-bold mb-3">{{translate('Number of People')}}</h6>
                                @for($i = 1; $i <= 8; $i++)
                                    <div class="form-check mb-2">
                                        <input wire:model="numPersons" 
                                               type="radio" 
                                               class="form-check-input" 
                                               value="{{ $i }}" 
                                               id="persons_{{ $i }}">
                                        <label class="form-check-label" for="persons_{{ $i }}">
                                            {{ $i }} {{ translate('person'.($i > 1 ? 's' : '')) }}
                                        </label>
                                    </div>
                                @endfor
                            </div>

                            {{-- Reset Filters Button --}}
                            <button wire:click="resetFilters" class="btn btn-outline-secondary btn-sm w-100">
                                @lang('guidings.reset_filters')
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Main Content Area --}}
                <div class="col-12 col-lg-9">
                    {{-- Sort and Results Info --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="me-2">@lang('message.sortby'):</span>
                                    <select wire:model="sortBy" class="form-select form-select-sm" style="width: auto;">
                                        <option value="">@lang('message.choose')...</option>
                                        <option value="newest">@lang('message.newest')</option>
                                        <option value="price-asc">@lang('message.lowprice')</option>
                                        <option value="price-desc">@lang('message.highprice')</option>
                                        <option value="short-duration">@lang('message.shortduration')</option>
                                        <option value="long-duration">@lang('message.longduration')</option>
                                    </select>
                                </div>

                                {{-- Results Count --}}
                                @if($guidings->count() > 0)
                                    <div>
                                        <small class="text-muted">{{ $guidings->total() }} @lang('guidings.results_found')</small>
                                    </div>
                                @endif
                            </div>

                            {{-- Active Filters --}}
                            <div class="active-filters mb-3">
                                @if(!empty($targetFish))
                                    @foreach($targetFish as $fishId)
                                        @php
                                            $fishName = collect($this->filterOptions['targets'])->firstWhere('id', $fishId)['name'] ?? 'Fish';
                                        @endphp
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            {{ $fishName }}
                                            <button type="button" class="btn-close ms-2" wire:click="removeFilter('targetFish', '{{ $fishId }}')" style="font-size: 0.7em;"></button>
                                        </span>
                                    @endforeach
                                @endif

                                @if(!empty($methods))
                                    @foreach($methods as $methodId)
                                        @php
                                            $methodName = collect($this->filterOptions['methods'])->firstWhere('id', $methodId)['name'] ?? 'Method';
                                        @endphp
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            {{ $methodName }}
                                            <button type="button" class="btn-close ms-2" wire:click="removeFilter('methods', '{{ $methodId }}')" style="font-size: 0.7em;"></button>
                                        </span>
                                    @endforeach
                                @endif

                                @if(!empty($waterTypes))
                                    @foreach($waterTypes as $waterId)
                                        @php
                                            $waterName = collect($this->filterOptions['waters'])->firstWhere('id', $waterId)['name'] ?? 'Water';
                                        @endphp
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            {{ $waterName }}
                                            <button type="button" class="btn-close ms-2" wire:click="removeFilter('waterTypes', '{{ $waterId }}')" style="font-size: 0.7em;"></button>
                                        </span>
                                    @endforeach
                                @endif

                                @if(!empty($durationTypes))
                                    @foreach($durationTypes as $durationType)
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            @if($durationType == 'half_day')
                                                @lang('guidings.half_day')
                                            @elseif($durationType == 'full_day')
                                                @lang('guidings.full_day')
                                            @elseif($durationType == 'multi_day')
                                                @lang('guidings.multi_day')
                                            @endif
                                            <button type="button" class="btn-close ms-2" wire:click="removeFilter('durationTypes', '{{ $durationType }}')" style="font-size: 0.7em;"></button>
                                        </span>
                                    @endforeach
                                @endif

                                @if($numPersons)
                                    <span class="badge bg-light text-dark border me-1 mb-1">
                                        {{ $numPersons }} @lang('message.persons')
                                        <button type="button" class="btn-close ms-2" wire:click="removeFilter('numPersons', '')" style="font-size: 0.7em;"></button>
                                    </span>
                                @endif

                                @if($priceMin || $priceMax)
                                    <span class="badge bg-light text-dark border me-1 mb-1">
                                        @if($priceMin && $priceMax)
                                            €{{ $priceMin }} - €{{ $priceMax }}
                                        @elseif($priceMin)
                                            From €{{ $priceMin }}
                                        @elseif($priceMax)
                                            Up to €{{ $priceMax }}
                                        @endif
                                        <button type="button" class="btn-close ms-2" wire:click="removeFilter('price', '')" style="font-size: 0.7em;"></button>
                                    </span>
                                @endif
                            </div>

                            {{-- Guiding Cards --}}
                            @if($guidings->count() > 0)
                                @foreach($guidings as $guiding)
                                <div class="card mb-2" style="border: 1px solid #dee2e6; border-radius: 0;">
                                    <div class="row g-0" style="min-height: 180px;">
                                        {{-- Image Carousel --}}
                                        <div class="col-12 col-md-4 p-0">
                                            @php
                                                $galleryImages = json_decode($guiding->gallery_images, true) ?? [];
                                            @endphp
                                            @if(count($galleryImages) > 0)
                                                <div id="carousel-{{$guiding->id}}" class="carousel slide h-100">
                                                    <div class="carousel-inner h-100">
                                                        @foreach($galleryImages as $index => $imagePath)
                                                            <div class="carousel-item h-100 @if($index == 0) active @endif">
                                                                <img class="d-block w-100 h-100" 
                                                                     src="{{ asset($imagePath) }}"
                                                                     alt="{{ translate($guiding->title) }}"
                                                                     style="object-fit: cover; object-position: center;">
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @if(count($galleryImages) > 1)
                                                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{{$guiding->id}}" data-bs-slide="prev">
                                                            <span class="carousel-control-prev-icon"></span>
                                                        </button>
                                                        <button class="carousel-control-next" type="button" data-bs-target="#carousel-{{$guiding->id}}" data-bs-slide="next">
                                                            <span class="carousel-control-next-icon"></span>
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center justify-content-center h-100" style="background-color: #f8f9fa;">
                                                    <i class="fas fa-image fa-3x text-muted"></i>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Guiding Details --}}
                                        <div class="col-12 col-md-5">
                                            <div class="card-body py-3 position-relative">
                                                {{-- Rating Box - Top Right --}}
                                                <div class="position-absolute top-0 end-0 mt-2 me-2 d-md-none">
                                                    <div style="font-size: 0.8rem; color: #666;">
                                                        Noch keine Bewertungen
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-12 col-md-12">
                                                        <h5 class="card-title mb-1" style="font-size: 1.1rem;">{{ translate($guiding->title) }}</h5>
                                                        <p class="text-muted mb-1" style="font-size: 0.9rem;">
                                                            <i class="fas fa-map-marker-alt me-1"></i>{{ $guiding->location }}
                                                        </p>
                                                        
                                                        {{-- Rating for desktop --}}
                                                        <div class="d-none d-md-block">
                                                            @php
                                                                $averageRating = $guiding->user->average_rating();
                                                                $reviewCount = $guiding->user->reviews->count();
                                                            @endphp
                                                            <p class="text-muted mb-2" style="font-size: 0.85rem;">Noch keine Bewertungen</p>
                                                        </div>

                                                        <div class="row g-1 mb-1">
                                                            <div class="col-6">
                                                                <small class="text-dark d-flex align-items-center" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-clock me-1" style="color: #666;"></i>
                                                                    {{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}
                                                                </small>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-dark d-flex align-items-center" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-user me-1" style="color: #666;"></i>
                                                                    {{ $guiding->max_guests }} {{ translate('Personen') }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="row g-1 mb-2">
                                                            <div class="col-6">
                                                                <small class="text-dark d-flex align-items-center" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-fish me-1" style="color: #666;"></i>
                                                                    @php
                                                                        $guidingTargets = $guiding->getTargetFishNames();
                                                                        $targetNames = collect($guidingTargets)->pluck('name')->toArray();
                                                                    @endphp
                                                                    {{ implode(', ', array_slice($targetNames, 0, 1)) }}
                                                                </small>
                                                            </div>
                                                            <div class="col-6">
                                                                <small class="text-dark d-flex align-items-center" style="font-size: 0.8rem;">
                                                                    <i class="fas fa-ship me-1" style="color: #666;"></i>
                                                                    {{ $guiding->is_boat ? __('guidings.boat') : __('guidings.shore') }}
                                                                </small>
                                                            </div>
                                                        </div>

                                                        @php
                                                            $inclusions = $guiding->getInclusionNames();
                                                        @endphp
                                                        @if(!empty($inclusions))
                                                        <div class="mb-1">
                                                            <small class="text-muted fw-bold" style="font-size: 0.8rem;">Im Preis inklusive</small>
                                                            <div>
                                                                @foreach ($inclusions as $index => $inclusion)
                                                                    @if ($index < 2)
                                                                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                                            ✓ {{ $inclusion['name'] }}
                                                                        </small>
                                                                    @endif
                                                                @endforeach
                                                                @if (count($inclusions) > 2)
                                                                    <small class="text-muted" style="font-size: 0.75rem;">+{{ count($inclusions) - 2 }} more</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Price and Actions --}}
                                        <div class="col-12 col-md-3">
                                            <div class="card-body d-flex flex-column justify-content-between h-100 py-3">
                                                {{-- Desktop Layout --}}
                                                <div class="d-none d-md-block text-end d-flex flex-column justify-content-between h-100">
                                                    <div>
                                                        {{-- Rating --}}
                                                        <div class="mb-2">
                                                            <div style="font-size: 0.8rem; color: #666;">
                                                                Noch keine Bewertungen
                                                            </div>
                                                        </div>
                                                        {{-- Price --}}
                                                        <div class="mb-3">
                                                            <div style="font-size: 0.9rem; font-weight: bold; color: #E8604C;">
                                                                ab {{$guiding->getLowestPrice()}}€ p.P.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- Buttons - Aligned to bottom --}}
                                                    <div class="d-grid gap-1">
                                                        <a class="btn btn-primary btn-sm" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}" style="font-size: 0.85rem;">Details</a>
                                                        <a class="btn btn-outline-secondary btn-sm py-1" href="{{ route('wishlist.add-or-remove', $guiding->id) }}" style="font-size: 0.75rem; line-height: 1.2;">
                                                            Add to Favorites
                                                        </a>
                                                    </div>
                                                </div>

                                                {{-- Mobile Layout --}}
                                                <div class="d-md-none">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        {{-- Price --}}
                                                        <div style="font-size: 0.9rem; font-weight: bold; color: #E8604C;">
                                                            ab {{$guiding->getLowestPrice()}}€ p.P.
                                                        </div>
                                                        {{-- Buttons --}}
                                                        <div class="d-flex gap-2">
                                                            <a class="btn btn-primary btn-sm" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}" style="font-size: 0.85rem;">Details</a>
                                                            <a class="btn btn-outline-secondary btn-sm py-1" href="{{ route('wishlist.add-or-remove', $guiding->id) }}" style="font-size: 0.75rem; line-height: 1.2;">
                                                                Favorites
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                                {{-- Load More Button --}}
                                @if($guidings->hasMorePages())
                                    <div class="text-center mt-4">
                                        <button wire:loading.remove class="btn btn-primary" wire:click="loadMore()">
                                            View more
                                        </button>
                                        <div wire:loading.delay>
                                            <div class="spinner-border" role="status"></div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-fish fa-3x text-muted mb-3"></i>
                                    <h4>@lang('guidings.no_results')</h4>
                                    <p class="text-muted">@lang('guidings.try_different_filters')</p>
                                    <button wire:click="resetFilters" class="btn btn-primary">
                                        @lang('guidings.reset_filters')
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--Tours List End-->

    <style>
        .btn-primary {
            background-color: #E8604C;
            border-color: #E8604C;
        }
        
        .btn-primary:hover {
            background-color: #d4503a;
            border-color: #d4503a;
        }
        
        .text-primary {
            color: #E8604C !important;
        }
        
        .filter-group {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        
                .filter-group:last-child {
            border-bottom: none;
        }
        
        .filter-scroll {
            max-height: 150px;
            overflow-y: auto;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* Internet Explorer 10+ */
        }
        
        .filter-scroll::-webkit-scrollbar {
            display: none; /* WebKit */
        }
        
        .price-box {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 8%;
        }
    </style>
</div>
