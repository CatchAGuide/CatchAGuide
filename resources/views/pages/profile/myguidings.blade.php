@extends('pages.profile.layouts.profile')
@section('title', __('profile.myGuides'))

@section('profile-content')
<style>
    .guidings-container {
        padding: 0;
    }
    
    .page-header {
        background: linear-gradient(135deg, #313041 0%, #252238 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    /* Fix breadcrumb background - target the actual breadcrumb container */
    .main-content-wrapper::before,
    .main-content-wrapper .breadcrumb-container,
    .breadcrumb-nav,
    .page-title-area,
    .breadcrumb-wrapper {
        background: transparent !important;
    }
    
    /* Remove the dark header that contains breadcrumbs */
    .page-header {
        display: none !important;
    }
    
    /* Header Section - copied from bookings.blade.php */
    .guidings-header {
        background: linear-gradient(135deg, #313041, #252238);
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .guidings-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
        opacity: 0.5;
        animation: float 20s infinite linear;
    }
    
    @keyframes float {
        0% { transform: translateX(-100px) translateY(-100px); }
        100% { transform: translateX(100px) translateY(100px); }
    }
    
    .guidings-header h1 {
        color: white;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 8px;
        position: relative;
        z-index: 1;
    }
    
    .guidings-header p {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 0;
        font-size: 1.1rem;
        position: relative;
        z-index: 1;
    }
    
    /* Create a simple title instead - keeping as fallback */
    .simple-page-title {
        background: white;
        padding: 15px 0;
        margin-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
        display: none; /* Hide since we're using the new header */
    }
    
    .simple-page-title h2 {
        color: #313041;
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0;
    }
    
    .page-header h2 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .page-header p {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .guiding-row {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        margin-bottom: 12px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
    }
    
    .guiding-row:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .guiding-row.draft {
        border: 2px solid #e74c3c;
        background: #fdfbfb;
    }
    
    .carousel.slide img {
        height: 140px;
        width: 100%;
        object-fit: cover;
        object-position: center center;
        border-radius: 0;
        display: block;
    }
    
    .carousel-inner {
        border-radius: 0;
        overflow: hidden;
        height: 140px;
    }
    
    .carousel-item {
        height: 140px;
        overflow: hidden;
    }
    
    .carousel-item img {
        height: 140px;
        width: 100%;
        object-fit: cover;
        object-position: center center;
        display: block;
    }
    
    .carousel.slide {
        height: 140px;
        border-radius: 0;
    }
    
    .carousel .carousel-control-next, 
    .carousel .carousel-control-prev {
        top: 50%;
        transform: translateY(-50%);
        width: 30px;
        height: 30px;
        background: rgba(49, 48, 65, 0.8);
        border-radius: 50%;
        opacity: 0;
        transition: all 0.3s ease;
    }
    
    .guiding-row:hover .carousel-control-next,
    .guiding-row:hover .carousel-control-prev {
        opacity: 1;
    }
    
    .carousel .carousel-control-next {
        right: 8px;
    }
    
    .carousel .carousel-control-prev {
        left: 8px;
    }
    
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 12px;
        height: 12px;
    }
    
    .draft-badge {
        background: #e74c3c !important;
        color: #ffffff !important;
        font-size: 0.75rem;
        padding: 4px 8px;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(231, 76, 60, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 15px;
    }
    
    .guiding-content {
        padding: 16px;
    }
    
    .guiding-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #313041;
        margin-bottom: 8px;
        line-height: 1.3;
    }
    
    .guiding-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    
    .guiding-title a:hover {
        color: #e8604c;
    }
    
    .location-text {
        color: #666;
        font-size: 0.85rem;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .location-text i {
        color: #e8604c;
        margin-right: 6px;
    }
    
    .detail-row {
        display: flex;
        align-items: center;
        margin-bottom: 6px;
        font-size: 0.8rem;
        color: #555;
    }
    
    .detail-row img {
        margin-right: 8px;
        opacity: 0.8;
        width: 14px;
        height: 14px;
    }
    
    .inclusions-section {
        margin-top: 12px;
    }
    
    .inclusions-title {
        font-weight: 600;
        color: #313041;
        margin-bottom: 8px;
        font-size: 0.85rem;
    }
    
    .inclusions-flex {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    
    .inclusion-tag {
        background: #f8f9fa;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        color: #555;
        border: 1px solid #e9ecef;
        display: inline-flex;
        align-items: center;
    }
    
    .inclusion-tag i {
        color: #28a745;
        margin-right: 4px;
        font-size: 0.7rem;
    }
    
    .more-inclusions {
        background: #e8604c;
        color: white;
        border-color: #e8604c;
        font-weight: 600;
    }
    
    .actions-column {
        padding: 16px;
        background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f4 100%);
        border-left: 1px solid #e9ecef;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 140px;
        position: relative;
        gap: 8px;
    }
    
    .guiding-row.draft .actions-column {
        background: #fdfbfb;
    }
    
    .action-btn {
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        text-align: center;
        transition: all 0.3s ease;
        font-size: 0.75rem;
        margin-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: 32px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    
    .btn-outline-primary {
        color: white;
        border: 2px solid #313041;
        background: #313041;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    
    .btn-outline-primary:hover {
        background: #252238;
        border-color: #252238;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.12);
    }
    
    .btn-outline-success {
        color: white;
        border: 2px solid #28a745;
        background: #28a745;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    
    .btn-outline-success:hover {
        background: #218838;
        border-color: #218838;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.12);
    }
    
    .btn-outline-danger {
        color: white;
        border: 2px solid #dc3545;
        background: #dc3545;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    
    .btn-outline-danger:hover {
        background: #c82333;
        border-color: #c82333;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.12);
    }
    
    .price-badge {
        background: linear-gradient(135deg, #e8604c, #d54e37);
        color: white;
        padding: 6px 10px;
        border-radius: 6px 0 6px 0;
        font-weight: 700;
        font-size: 0.8rem;
        box-shadow: 0 2px 6px rgba(232, 96, 76, 0.3);
        position: absolute;
        top: 0;
        right: 0;
        z-index: 10;
    }
    
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
    }
    
    .empty-state i {
        font-size: 3rem;
        color: #e8604c;
        margin-bottom: 18px;
    }
    
    .empty-state h4 {
        color: #313041;
        margin-bottom: 12px;
        font-weight: 700;
        font-size: 1.3rem;
    }
    
    .empty-state b {
        color: #666;
        font-size: 1rem;
        display: block;
        margin-bottom: 20px;
    }
    
    .thm-btn {
        background: linear-gradient(135deg, #e8604c, #d54e37);
        color: white;
        padding: 10px 20px;
        border-radius: 20px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .thm-btn:hover {
        background: linear-gradient(135deg, #d54e37, #c44232);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(232, 96, 76, 0.3);
        color: white;
    }
    
    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 25px;
    }
    
    @media (max-width: 768px) {
        .guiding-content {
            padding: 14px;
        }
        
        .actions-column {
            padding: 14px;
            border-left: none;
            border-top: 1px solid #e9ecef;
            flex-direction: row;
            gap: 8px;
            min-height: auto;
        }
        
        .action-btn {
            font-size: 0.7rem;
            padding: 8px 10px;
            flex: 1;
            min-height: 30px;
        }
        
        .price-badge {
            position: absolute;
            top: 0;
            right: 0;
            border-radius: 6px 0 6px 0;
            font-size: 0.75rem;
            padding: 6px 10px;
        }
        
        .detail-row {
            margin-bottom: 5px;
        }
        
        .simple-page-title {
            padding: 12px 0;
        }
        
        .simple-page-title h2 {
            font-size: 1.4rem;
        }
        
        .carousel.slide img,
        .carousel-inner,
        .carousel.slide {
            height: 120px;
        }
        
        .carousel-item {
            height: 120px;
        }
        
        .carousel-item img {
            height: 120px;
            width: 100%;
            object-fit: cover;
            object-position: center center;
        }
        
        .actions-column {
            min-height: 120px;
        }
        
        .guidings-header {
            padding: 20px;
        }
        
        .guidings-header h1 {
            font-size: 1.6rem;
        }
        
        .guidings-header p {
            font-size: 1rem;
        }
    }
</style>

<div class="guidings-container">
    <!-- Header Section -->
    <div class="guidings-header">
        <h1 class="mb-0 text-white">
            <i class="fas fa-fish"></i>
            @lang('profile.myGuides')
        </h1>
        <p class="mb-0 mt-2 text-white">Manage your fishing guide experiences and track their performance</p>
    </div>

    <div class="row">
        <div class="col-12">
            @if(count($guidings))
                @foreach($guidings as $guidingIndex => $guiding)
                    <div class="guiding-row {{ $guiding->status == 2 ? 'draft' : '' }}">
                        <div class="row g-0">
                            <!-- Image Column -->
                            <div class="col-12 col-md-4 col-lg-4 position-relative">
                                <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                    @if($guiding->status == 2)
                                        <div class="position-absolute start-0 top-0 m-3" style="z-index: 10;">
                                            <span class="badge draft-badge">Draft</span>
                                        </div>
                                    @endif
                                    
                                    <div class="carousel-inner">
                                        @if(count(get_galleries_image_link($guiding)))
                                            @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                                                <div class="carousel-item @if($index == 0) active @endif">
                                                    <img class="d-block w-100" src="{{$gallery_image_link}}" alt="{{$guiding->title}}">
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    @if(count(get_galleries_image_link($guiding)) > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Content Column -->
                            <div class="col-12 col-md-5 col-lg-5">
                                <div class="guiding-content">
                                    <h5 class="guiding-title">
                                        <a href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">{{$guiding->title}}</a>
                                    </h5>
                                    
                                    <p class="location-text">
                                        <i class="fas fa-map-marker-alt"></i>{{ translate($guiding->location) }}
                                    </p>
                                    
                                    <div class="detail-row">
                                        <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="18" width="18" alt="Duration" />
                                        <span class="me-4">{{ $guiding->duration }} @lang('guidings.hours')</span>
                                        <img src="{{asset('assets/images/icons/user-new.svg')}}" height="18" width="18" alt="Guests" />
                                        <span>{{ $guiding->max_guests }} @lang('message.persons')</span>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <img src="{{asset('assets/images/icons/fish-new.svg')}}" height="18" width="18" alt="Target Fish" />
                                        @php
                                            $guidingTargets = collect($guiding->getTargetFishNames())->pluck('name')->toArray();
                                        @endphp
                                        <span>{{ !empty($guidingTargets) ? implode(', ', $guidingTargets) : 'Various fish' }}</span>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="18" width="18" alt="Type" />
                                        <span>{{$guiding->is_boat ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : __('guidings.shore')}}</span>
                                    </div>
                                    
                                    @if(!empty($guiding->getInclusionNames()))
                                        <div class="inclusions-section">
                                            <div class="inclusions-title">@lang('guidings.Whats_Included')</div>
                                            <div class="inclusions-flex">
                                                @php
                                                    $inclusions = $guiding->getInclusionNames();
                                                    $maxToShow = 3;
                                                @endphp
                                                
                                                @foreach ($inclusions as $index => $inclusion)
                                                    @if ($index < $maxToShow)
                                                        <span class="inclusion-tag">
                                                            <i class="fa fa-check"></i>{{ $inclusion['name'] }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                                
                                                @if (count($inclusions) > $maxToShow)
                                                    <span class="inclusion-tag more-inclusions">
                                                        +{{ count($inclusions) - $maxToShow }} more
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Actions Column -->
                            <div class="col-12 col-md-3 col-lg-3 position-relative">
                                <div class="actions-column">
                                    <a class="action-btn btn-outline-primary" href="{{route('guidings.show', [$guiding->id,$guiding->slug])}}">
                                        <i class="fas fa-eye me-1"></i>@lang('profile.view')
                                    </a>
                                    <a class="action-btn btn-outline-primary" href="{{route('guidings.edit_newguiding', $guiding->id)}}">
                                        <i class="fas fa-edit me-1"></i>{{$guiding->status == 2 ? __('profile.draft') : __('profile.edit')}}
                                    </a>
                                    @if($guiding->status !== 2)
                                        @if($guiding->status == 1)
                                            <a class="action-btn btn-outline-danger" href="{{route('profile.guiding.deactivate', $guiding)}}">
                                                <i class="fas fa-pause me-1"></i>@lang('profile.deactivateGuide')
                                            </a>
                                        @else
                                            <a class="action-btn btn-outline-success" href="{{route('profile.guiding.activate', $guiding)}}">
                                                <i class="fas fa-play me-1"></i>@lang('profile.activateGuide')
                                            </a>
                                        @endif
                                    @endif
                                </div>
                                
                                <div class="price-badge">
                                    @lang('message.from') {{$guiding->getLowestPrice()}}€
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div class="pagination-container">
                    {!! $guidings->links('vendor.pagination.default') !!}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-fish"></i>
                    <h4>@lang('profile.notcreated') 🐟</h4>
                    <b>@lang('profile.lets-change')</b>
                    <a href="{{ route('profile.newguiding') }}" class="thm-btn">@lang('profile.creategiud')</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection