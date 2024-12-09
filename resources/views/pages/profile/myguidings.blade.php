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

</style>
@endsection

@section('profile-content')
<div class="row">
  <div class="col-xxl-12 col-lg-12">
    <div class="tours-list__right">
      <div class="tours-list__inner">
        @if(count($guidings))
          @foreach($guidings as $guiding)
            <div class="row m-0 mb-2">
              <div class="col-sm-6 col-md-12">
                <div class="row border shadow-sm bg-white xszzs">
                  <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-1">
                    <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                      <div class="carousel-inner">
                        @if(count(get_galleries_image_link($guiding)))
                          @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                            <div class="carousel-item @if($index == 0) active @endif">
                              <img class="d-block w-100" src="{{asset($gallery_image_link)}}">
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
                  <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-1 p-1">
                    {{-- @if ($guiding->is_newguiding == 0)
                      <h5 class="fw-bolder text-truncate"><a class="text-dark" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">{{$guiding->title}}</a></h5>
                    @else --}}
                      <h5 class="fw-bolder text-truncate"><a class="text-dark" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">{{$guiding->title}}</a></h5>
                    {{-- @endif --}}
                    <div class="ratings mr-2 color-primary my-1" style="font-size:0.80rem">
                      @if(count($guiding->user->received_ratings) > 0)
                        @switch(two($guiding->user->average_rating()))
                          @case(two($guiding->user->average_rating()) >= 5)
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            @break
                          @case(two($guiding->user->average_rating()) >= 4.5)
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-half"></i>
                            @break
                          @case(two($guiding->user->average_rating()) >= 4)
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            @break
                          @case(two($guiding->user->average_rating()) >= 3.5)
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-half"></i>
                            @break
                          @case(two($guiding->user->average_rating()) >= 3)
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            @break
                          @case(two($guiding->user->average_rating()) >= 2.5)
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-half"></i>
                            @break
                          @case(two($guiding->user->average_rating()) >= 2)
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star"></i>
                            @break
                          @case(two($guiding->user->average_rating()) >= 1.5)
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                            <i class="fa fa-star-half"></i>
                            @break
                          @case(two($guiding->user->average_rating()) >= 1)
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                            @break
                          @default
                            - {{one($guiding->user->average_rating())}}
                            <i class="fa fa-star"></i>
                        @endswitch

                        @if(count($guiding->user->received_ratings) >= 2) 
                          ({{count($guiding->user->received_ratings)}} Bewertungen)
                        @else 
                          ({{count($guiding->user->received_ratings)}} Bewertung)
                        @endif

                      @endif     
                    </div>
                    <span class="text-center" style="font-size:1rem;color:rgb(28, 28, 28)"><i class="fas fa-map-marker-alt me-2"></i>{{ translate($guiding->location) }}</span>                                      
                    <div class="row mt-2">
                      <div class="col-6 col-sm-6 col-md-6">
                        <div class="d-flex align-items-center">
                          <div>
                            <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                          </div>
                          <div class="mx-2">
                            <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                              @php
                                $guidingTargets = collect($guiding->getTargetFishNames())->pluck('name')->toArray();
                              @endphp
                              
                              @if(!empty($guidingTargets))
                                {{ implode(', ', $guidingTargets) }}
                              @endif
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-6 col-sm-6 col-md-6">
                        <div class="d-flex align-items-center">
                          <div>
                            <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                          </div>
                          <div class="mx-2">
                            <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                              @php
                                $guidingWaters = collect($guiding->getWaterNames())->pluck('name')->toArray();
                              @endphp
                              
                              @if(!empty($guidingWaters))
                                {{ implode(', ', $guidingWaters) }}
                              @endif
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-6 col-sm-6 col-md-6">
                        <div class="d-flex align-items-center">
                          <div>
                            <img src="{{asset('assets/images/icons/fishing-tool.png')}}" height="20" width="20" alt="" />
                          </div>
                          <div class="mx-2">
                            <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                              @php
                                $fishingtype = null;
                                if($guiding->fishingTypes){
                                  if(app()->getLocale() == 'en'){
                                    $fishingtype = $guiding->fishingTypes->name_en;
                                  }else{
                                    $fishingtype =  $guiding->fishingTypes->name;
                                  }
                                }
                              @endphp
                              @if($fishingtype) {{$fishingtype}}  @else {{$guiding->fishing_type}}@endif
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-6 col-sm-6 col-md-6">
                        <div class="d-flex align-items-center">
                          <div>
                            <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                          </div>
                          <div class="mx-2">
                            <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                              @php
                                $guidingMethods = collect($guiding->getFishingMethodNames())->pluck('name')->toArray();
                              @endphp
                              
                              @if(!empty($guidingMethods))
                                {{ implode(', ', $guidingMethods) }}
                              @endif
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-6 col-sm-6 col-md-6">
                        <div class="d-flex align-items-center">
                          <div>
                            <img src="{{asset('assets/images/icons/fishing-man.png')}}" height="20" width="20" alt="" />
                          </div>
                          <div class="mx-2">
                            <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                              {{ translate('Fishing from ')}}: {{$guiding->is_boat ? $guiding->boat_type : translate('Shore')}}
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-6 col-sm-6 col-md-6">
                        <div class="d-flex align-items-center mt-2">
                          <div class="icon-small">
                            <span class="icon-user"></span>
                          </div>
                          <div class="mx-2" style="font-size:0.75rem">
                            {{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                          </div>
                        </div>
                      </div>
                      <div class="col-6 col-sm-6 col-md-6">
                        <div class="d-flex align-items-center">
                          <div>
                            <img src="{{asset('assets/images/icons/clock.svg')}}" height="20" width="20" alt="" />
                          </div>
                          <div class="mx-2" style="font-size:0.75rem">
                            {{ $guiding->duration }} @if($guiding->duration != 1) {{translate('Stunden')}} @else {{translate('Stunde')}} @endif
                          </div>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="mt-3">
                          @if($guiding->user->profil_image)
                            <img class="center-block rounded-circle" src="{{asset('images/'. $guiding->user->profil_image)}}" alt="" width="20" height="20">
                          @else
                            <img class="center-block rounded-circle" src="{{asset('images/placeholder_guide.jpg')}}" alt="" width="20" height="20">
                          @endif
                          <span class="color-primary" style="font-size:1rem">{{$guiding->user->firstname}}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-sm-12 col-md-2 col-lg-3 col-xl-2 col-xxl-2 position-relative">
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
