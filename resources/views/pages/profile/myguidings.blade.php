@extends('pages.profile.layouts.profile')
@section('title', __('profile.myGuides'))

@section('css_after')
<style>
  .fixedmap {
    position: fixed;
    right: 0px;
    bottom: 10%;
    height: 70%;
  }
  a:hover {
    color: black;
  }
  .page-header-bg-overly {
    background-color: rgba(0,0,0,0);
  }
  .pager-header-bg {
    filter: none !important;
  }

  .carousel .carousel-control-next, .carousel .carousel-control-prev {
    top: 50%;
    transform: translateY(-50%);
  }

  .carousel.slide img {
    /* max-height: 265px; */
    min-height: 160px;
    max-height:228px;
    object-fit: cover;
  }

  .carousel .carousel-control-next {
    right: 0;
  }

  .carousel .carousel-control-prev {
    left: 0;
  }

  .carousel-item {
    min-height: 50px;
  }
  .carousel .carousel-control-next, .carousel .carousel-control-prev {
    padding: 3px;
    width: 24px;
  }

  .carousel-item-next, .carousel-item-prev, .carousel-item.active {
    display: flex;
  }

  .carousel-control-prev-icon,
  .carousel-control-next-icon {
    width: 10px;
    height: 10px;
  }

  .carousel .carousel-control-next, .carousel .carousel-control-prev {
    padding: 3px;
    width: 24px;
  }
  .form-custom-input{
    /* border: solid #e8604c 1px; */
    border: 1px solid #d4d5d6;
    border-radius: 5px;
    padding: 8px 10px;
    width:100%;
  }
  .form-control:focus{
    /* border: solid #e8604c 1px !important; */
    box-shadow: none;
  }
  .form-custom-input:focus-visible{
    /* border: solid #e8604c 1px !important; */
    border:0;
    outline:solid #e8604c 1px !important;
  }
  li.select2-selection__choice{
    background-color: #E8604C !important;
    color: #fff !important;
    border: 0 !important;
    font-size:14px;
    vertical-align: middle !important;
    margin-top:0 !important;
  }
  button.select2-selection__choice__remove{
    border: 0 !important;
    color: #fff !important;
  }
  .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus{
    background:none;
  }
  span.select2-selection.select2-selection--multiple{
    border: 1px solid #d4d5d6;
    border-radius: 5px;
    padding: 7px 10px;
  }
  .select2-selection--multiple:before {
    content: "";
    position: absolute;
    right: 7px;
    top: 42%;
    border-top: 5px solid #888;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
  }

  #toggleFilterBtn{
    display:none;
  }
  .sort-row .form-select{
    width: auto;
  }

  @media only screen and (max-width: 600px) {
    #toggleFilterBtn{
      display:block;
    }
    #filterContainer{
      display:none;
    }
  }

  #radius{
    background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
    background-position: right 0.3rem center !important;
  }
  #num-guests{
    background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
    background-position: right 0.3rem center !important;
  }
  .custom-select:has(option:disabled:checked[hidden]) {
    color: gray;
  }
  .custom-select option{
    color:black;
  }

  .btn-outline-theme{
    color: #E8604C;
    border-color: #E8604C;
  }
  .btn-outline-theme:hover{
    color: #fff;
    background-color: #E8604C;
  }

  .z-1 {
    z-index: 1;
  }

  /* Updated Draft badge styling */
  .badge.bg-warning {
    background-color: #ff0707 !important;
    color: #ffffff !important;
    font-size: 1rem;
    padding: 0.5rem 0.8rem;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .draft-container {
    background-color: #eeecec;
  }

</style>
@endsection

@section('profile-content')

<div class="container">
  {{-- <section class="page-header">
      <div class="page-header__bottom">
          <div class="container">
              <div class="page-header__bottom-inner">
                  <ul class="thm-breadcrumb list-unstyled">
                      <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                      <li><span>&#183;</span></li>
                      <li><a href="{{ route('profile.index') }}">{{ translate('Profile') }}</a></li>
                      <li><span>&#183;</span></li>
                      <li class="active">
                          {{ translate('Meine Führungen') }}
                      </li>
                  </ul>
              </div>
          </div>
      </div>
  </section> --}}
</div>

<div class="row">
  <div class="col-xxl-12 col-lg-12">
    <div class="tours-list__right">
      <div class="tours-list__inner">
        @if(count($guidings))
          @foreach($guidings as $guidingIndex => $guiding)
            <div class="row m-0 mb-2">
              <div class="col-sm-6 col-md-12">
                <div class="row border shadow-sm bg-white rounded ">
                  <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                    <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                      @if($guiding->status == 2)
                        <div class="position-absolute start-0 top-0 m-2 z-1">
                          <span class="badge bg-warning">Draft</span>
                        </div>
                      @endif
                      <div class="carousel-inner">
                        @if(count(get_galleries_image_link($guiding)))
                          @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                            <div class="carousel-item @if($index == 0) active @endif">
                              <img class="d-block w-100" src="{{$gallery_image_link}}">
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
                  <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 p-2 px-md-3 pt-md-2 {{$guiding->status == 2 ? 'draft-container' : ''}}">
                    <h5 class="fw-bolder text-truncate"><a class="text-dark" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">{{$guiding->title}}</a></h5>
                    <p class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>{{ translate($guiding->location) }}</p>
                    
                    <div class="d-flex align-items-center mb-2">
                      <div class="me-4">
                        <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" class="me-2" alt="" />
                        {{ $guiding->duration }} @lang('guidings.hours')
                      </div>
                      <div>
                        <img src="{{asset('assets/images/icons/user-new.svg')}}" height="20" width="20" class="me-2" alt="" />
                        {{ $guiding->max_guests }} @lang('message.persons')
                      </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                      <div class="me-4">
                        <img src="{{asset('assets/images/icons/fish-new.svg')}}" height="20" width="20" class="me-2" alt="" />
                        @php
                          $guidingTargets = collect($guiding->getTargetFishNames())->pluck('name')->toArray();
                        @endphp
                        {{ !empty($guidingTargets) ? implode(', ', $guidingTargets) : '' }}
                      </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-2">
                      <div>
                        <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="20" width="20" class="me-2" alt="" />
                        {{$guiding->is_boat ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : __('guidings.shore')}}
                      </div>
                    </div>
                    
                    <div class="mt-3">
                      <strong>@lang('guidings.Whats_Included')</strong>
                      <div class="d-flex flex-wrap">
                        @if(!empty($guiding->getInclusionNames()))
                          @php
                            $inclussions = $guiding->getInclusionNames();
                            $maxToShow = 3;
                          @endphp
                          
                          @foreach ($inclussions as $index => $inclussion)
                            @if ($index < $maxToShow)
                              <span class="me-3"><i class="fa fa-check me-1"></i>{{ $inclussion['name'] }}</span>
                            @endif
                          @endforeach
                          
                          @if (count($inclussions) > $maxToShow)
                            <span>+{{ count($inclussions) - $maxToShow }} more</span>
                          @endif
                        @endif
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-sm-12 col-md-2 col-lg-2 col-xl-2 col-xxl-2 position-relative {{$guiding->status == 2 ? 'draft-container' : ''}}">
                    <div class="d-flex flex-column my-5 py-2">
                      <a class="btn btn-outline-theme btn-sm my-1" href="{{route('guidings.show', [$guiding->id,$guiding->slug])}}">View</a>
                      <a class="btn btn-outline-theme btn-sm my-1" href="{{route('guidings.edit_newguiding', $guiding->id)}}">@lang('profile.edit')</a>
                      @if($guiding->status == 1)
                        <a class="btn btn-outline-theme btn-sm my-1" href="{{route('profile.guiding.deactivate', $guiding)}}">@lang('profile.deactivateGuide')</a>
                      @else
                        <a class="btn btn-outline-theme btn-sm my-1" href="{{route('profile.guiding.activate', $guiding)}}">@lang('profile.activateGuide')</a>
                      @endif
                    </div>
                    <div class="theme-primary p-2 shadow-sm rounded-start" style="position: absolute;top:0;right:0">
                      <h6 class="mr-1 text-white fw-bold text-center">@lang('message.from') {{$guiding->getLowestPrice()}}€</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
          {!! $guidings->links('vendor.pagination.default') !!}
        @else
          <div class="text-center">
            <h4>@lang('profile.notcreated') 🐟</h4>
            <b>@lang('profile.lets-change')</b><br/><br/>
            <a href="{{ route('profile.newguiding') }}" class="thm-btn">@lang('profile.creategiud')</a>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
