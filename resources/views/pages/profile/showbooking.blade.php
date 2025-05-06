@extends('pages.profile.layouts.profile')
@section('title', __('profile.bookings'))
@section('profile-content')
    <div class="row">
        @if($booking->status == "storniert")
            <div class="alert alert-danger" role="alert">
                @lang('profile.cancelWarning')
            </div>
        @endif
        <div class="col-md-12">
            <h3 class="tour-details-two__title" style="margin-bottom: 0px;">{{ translate($guiding->title) }}</h3>
        </div>
        @if($guiding->additional_information)
        <div class="col-md-12 mt-2">
                <br>Sonstiges:</br>
                {!! $guiding->additional_information !!}
        </div>
        @endif
        {{-- <div class="col-md-12 mt-2">
            {!! translate($guiding->description) !!}
        </div> --}}

        <div class="col-12 my-1 mt-5">
            <h5><span class="bordered-heading">Guiding Information</span></h5>
        </div>
        <div class="col-md-8 my-1">
            <div class="row">
                <div class="col-12">
                    <div class="guide-info">
                        <div class="p-3 bg-light rounded">
                            <div class="flex-column border-bottom">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.duration'):</span>
                                </div>

                                <div class="px-2 text-dark">
                                    <p>{{$guiding->duration}} @lang('message.hours')</p>
                                </div>
                            </div>
                            <div class="flex-column border-bottom">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.targetFish'):</span>
                                </div>

                                <div class="px-2 text-dark">
                                    @php 
                                    $guidingTargets = $guiding->guidingTargets->pluck('name')->toArray();

                                    if(app()->getLocale() == 'en'){
                                        $guidingTargets = $guiding->guidingTargets->pluck('name_en')->toArray();
                                    }
                                @endphp
                                <p>
                                    {{implode(', ',$guidingTargets)}}
                                </p>
                                </div>
                            </div>
                            @if($guiding->fishingTypes)
                            <div class="flex-column border-bottom">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.angelType'):</span>
                                </div>
       
                                <div class="px-2 text-dark">
                                    @if(app()->getLocale() == 'en')
                                    {{$guiding->fishingTypes->name_en ? $guiding->fishingTypes->name_en : $guiding->fishingTypes->name }}
                                    @else
                                    <p>
                                        {{$guiding->fishingTypes ? $guiding->fishingTypes->name : '' }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <div class="flex-column border-bottom">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.techniqueMethod'):</span>
                                </div>
       
                                <div class="px-2 text-dark">
                                    @php 
                               $guidingMethods = $guiding->guidingMethods->pluck('name')->toArray();

                                    if(app()->getLocale() == 'en'){
                                        $guidingMethods = $guiding->guidingMethods->pluck('name_en')->toArray();
                                    }
                                    @endphp

                                    {{implode(', ',$guidingMethods)}}
                                </div>
                            </div>

                            @if($guiding->fishingFrom)
                            <div class="flex-column border-bottom">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.WhereFrom'):</span>
                                </div>
       
                                <div class="px-2 text-dark">
                                    @if(app()->getLocale() == 'en')
                                    {{$guiding->fishingFrom->name_en ? $guiding->fishingFrom->name_en : $guiding->fishingFrom->name}}
                                    @else
                                    {{$guiding->fishingFrom ? $guiding->fishingFrom->name : ''}}
                                    @endif
                                </div>
                            </div>
                            @endif

                            <div class="flex-column border-bottom">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.waterType'):</span>
                                </div>
       
                                <div class="px-2 text-dark">
                                    @php 
                                    $guidingWaters = $guiding->guidingWaters->pluck('name')->toArray();

                                    if(app()->getLocale() == 'en'){
                                        $guidingWaters = $guiding->guidingWaters->pluck('name_en')->toArray();
                                    }
                                    @endphp

                                    {{implode(', ',$guidingWaters)}}
                                </div>
                            </div>

                            @php
                            $guidingInclusion = $guiding->inclussions->pluck('name')->toArray();
                        
                            if (app()->getLocale() == 'en') {
                                $guidingInclusion = $guiding->inclussions->pluck('name_en')->toArray();
                        
                                // If name_en is empty, fallback to name
                                foreach ($guidingInclusion as $index => $name) {
                                    if (empty($name)) {
                                        $guidingInclusion[$index] = $guiding->inclussions[$index]->name;
                                    }
                                }
                            }
                            @endphp
                            
                            @if (!empty($guidingInclusion))
                            <div class="flex-column border-bottom">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.inclussion'):</span>
                                </div>
       
                                <div class="px-2 text-dark">


                            
                                {{ implode(', ', array_filter($guidingInclusion)) }}
                           
                                </div>
                            </div>
                            @endif

                            <div class="flex-column border-bottom">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.location'):</span>
                                </div>
                                <div class="px-2 text-dark">
                                   {{$guiding->location}}
                                </div>
                            </div>

                            <div class="flex-column border-bottom">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.meetingPoint'):</span>
                                </div>
                                <div class="px-2 text-dark">
                                    {{$guiding->meeting_point ? $guiding->meeting_point : null }}
                                </div>
                            </div>

                            @if($guiding->needed_equipment)
                            <div class="flex-column">
                                <div class="my-2">
                                    <span class="text-dark fw-bold">@lang('profile.equipment'):</span>
                                </div>
                                <div class="px-2 text-dark">
                                    {{$guiding->needed_equipment}}
                                </div>
                            </div>
                            @endif

                        </div>
                      
                    </div>
                </div>
            
            </div>
        </div>
        <div class="col-md-4 my-1">
            <div class="row m-0">
                <div class="shadow-sm bg-light p-3 rounded d-flex flex-column">
                    <div class="ml-3"><h5>@lang('message.booking-overview')</h5></div>
                    <div class="my-2">
                        <div class="px-2 py-1 d-flex">
                            <div class="col-8">@lang('message.total-guest'):</div>
                            <div class="ml-auto">
                                {{$booking->count_of_users}}
                            </div>
                        </div>
                        <div class="px-2 py-1 d-flex">
                            <div class="col-8">@lang('message.booking-date'):</div>
                            <div class="ml-auto">
                                @if($booking->blocked_event)
                                {{date('d-m-Y', strtotime($booking->blocked_event->from))}}
                                @else
                                    -storniert-
                                @endif
                            </div>
                        </div>
                        <div class="px-4 mx-3"></div>
                        <div class="px-2 py-1 d-flex">
                            <div class="col-8">@lang('message.guiding-price'):</div>
                            <div class="ml-auto">{{$guiding->getGuidingPriceByPerson($booking->count_of_users)}}€</div>
                        </div>
                        <div class="px-2 py-1 d-flex">
                            <div class="col-8">@lang('message.total-extras'):</span></div>
                            <div class="ml-auto">
                               {{$booking->total_extra_price ? $booking->total_extra_price : 0 }}€
                            </div>
                        </div>
                        <div class="border-top px-4 mx-3"></div>
                        <div class="px-2 py-1 d-flex pt-3">
                            <div class="col-8"><b>Total</b></div>
                            <div class="ml-auto"><b class="green">{{$booking->price}}€</b></div>
                        </div>
                    </div>

                </div>
    
            </div>
        </div>
        

            {{-- @if(!$authUser->is_guide)
                @if($booking->blocked_event)
                    @if(\Carbon\Carbon::now() < $booking->blocked_event->from && $booking->status != "storniert")
                        <a href="{{route('profile.stornobooking', $booking->id)}}">
                            <button class="thm-btn">JETZT STORNIEREN</button>
                        </a>
                    @endif
                @endif
            @endif --}}


    </div>
@endsection
