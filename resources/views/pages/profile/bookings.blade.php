@extends('pages.profile.layouts.profile')
@section('title', translate('Von mir gebucht'))
@section('css_after')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
@endsection
@section('profile-content')
<!-- Button trigger modal -->
    @if($bookings && count($bookings)>=1)
    <table id="myTable" class="display nowrap table table-hover" style="width:100%">
        <thead>
            <tr>
                <th class="wd-15p border-bottom-0">@lang('profile.guidings')</th>
                <th class="wd-15p border-bottom-0">@lang('profile.when')</th>
                <th class="wd-15p border-bottom-0">@lang('profile.price')</th>
                <th class="wd-15p border-bottom-0">@lang('profile.chat')</th>
                <th class="wd-15p border-bottom-0">@lang('profile.evaluation')</th>
                <th class="wd-15p border-bottom-0">@lang('profile.details')</th>
                <th class="wd-15p border-bottom-0">@lang('profile.contact')</th>
                <th class="wd-15p border-bottom-0">Status</th>
            </tr>
        </thead>
        <tbody> 
            @foreach($bookings as $index => $booking)
            <div class="modal fade" id="emailmodal{{$index}}" tabindex="-1" aria-labelledby="emailmodal{{$index}}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="contact">E-mail</h5>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <p><strong>E-mail:</strong> {{$booking->guiding->user->email}}</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="phonemodal{{$index}}" tabindex="-1" aria-labelledby="phonemodal{{$index}}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="contact">@lang('profile.tele')</h5>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <p><strong>@lang('profile.tele'):</strong> {{$booking->guiding->user->phone}}</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <tr>
                <td>{{ $booking->guiding->title }}</td>
                <td>
                    @if($booking->blocked_event)
                        {{ \Carbon\Carbon::parse($booking->blocked_event->from)->format('F j, Y') }}
                    @else
                        @lang('profile.cancel')
                    @endif

                </td>
                <td>{{ two($booking->price) }} €<br>
                </td>
                <td>
                    @switch($booking->status)
                    @case('accepted')
                        <a class="badge py-2 px-2 thm-btn"  href="{{ route('chat.create', $booking->guiding->user) }}">@lang('profile.chat')</a>
                    @break
                    @case('pending')
                    <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('cancelled')
                        <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break

                    @case('storniert')
                    <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('rejected')
                    <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @default
                        <a class="badge py-2 px-2 thm-btn"  href="{{ route('chat.create', $booking->guiding->user) }}">@lang('profile.chat')</a>
                    @endswitch
                </td>
                <td>
                    @switch($booking->status)
                        @case('accepted')
                            @if($booking->isBookingOver())
                                @if(!auth()->user()->hasratet($booking->user_id))
                                    @if(auth()->user()->id != $booking->user_id )
                                        <button class="btn thm-btn btn-sm">
                                            {{translate('du kannst dich nicht selbst bewerten')}}
                                        </button>
                                    @else
                                        <a href="{{ route('ratings.show', $booking->id) }}">
                                            <button class="btn thm-btn btn-sm" style="background-color: orange;">
                                                @lang('profile.rate')
                                            </button>
                                        </a>
                                    @endif
                                @else
                                    <button class="btn thm-btn btn-sm" style="background-color: orange;">
                                        {{translate('bereits bewertet')}} 
                                    </button>
                                @endif
                            @else
                            <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                            @endif
                        @break
                        @default
                        <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @endswitch
                </td>
                <td>

                    @switch($booking->status)
                    @case('accepted')
                    <a href="{{route('profile.showbooking', $booking->id)}}">
                        <button class="btn thm-btn btn-sm" style="background-color: lightblue;">
                            Details
                        </button>
                    </a>
                    @break
                    @case('pending')
                    <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('cancelled')
                        <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('rejected')
                    <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('storniert')
                    <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @default
                    <a href="{{route('profile.showbooking', $booking->id)}}">
                        <button class="btn thm-btn btn-sm" style="background-color: lightblue;">
                            Details
                        </button>
                    </a>
                    @endswitch

                </td>
                <td>
                    @switch($booking->status)
                    @case('accepted')
                    <button type="button" class="btn thm-btn btn-sm" data-bs-toggle="modal" data-bs-target="#emailmodal{{$index}}" style="background-color:rgb(54, 109, 182)">
                        E-Mail
                      </button>
                        @if($booking->guiding->user->phone)
                                <button type="button" class="btn thm-btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#phonemodal{{$index}}">
                                    @lang('profile.tele')
                                </button>
                        @endif
                    @break
                    @case('pending')
                    <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('cancelled')
                        <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('rejected')
                    <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('storniert')
                    <span class="text-muted">@lang('profile.bwm-notavailable')</span>
                    @break
                    @default
                    <button type="button" class="btn thm-btn btn-sm" data-bs-toggle="modal" data-bs-target="#emailmodal{{$index}}" style="background-color:rgb(54, 109, 182)">
                        E-Mail
                      </button>
                    @if($booking->guiding->user->phone)
                            <button type="button" class="btn thm-btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#phonemodal{{$index}}">
                                @lang('profile.tele')
                            </button>
                    @endif
                    @endswitch
                </td>
                <td>
                    
                    @switch($booking->status)
                        @case('accepted')
                        <span class="badge badge-pill badge-success">{{translate($booking->status)}}</span>
                            @break
                        @case('pending')
                            <span class="badge badge-pill badge-warning">{{translate($booking->status)}}</span>
                            @break
                        @case('cancelled')
                        <span class="badge badge-pill badge-danger">{{translate($booking->status)}}</span>
                        @break

                        @case('rejected')
                        <span class="badge badge-pill badge-danger">{{translate($booking->status)}}</span>
                        @break

                        @case('storniert')
                        <span class="badge badge-pill badge-danger">{{translate($booking->status)}}</span>
                        @break

                        @default
                        <span class="badge badge-pill badge-success">@lang('profile.bwm-accepted')</span>
                    @endswitch
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
        <div class="text-center">
            <h4>{{translate('Noch sind keine Buchungen vorhanden ')}}&#128564;</h4>
            <b>{{translate('Lass uns das schleunigst ändern')}}</b><br/><br/>
            <a href="{{ route('guidings.index') }}" class="thm-btn">{{translate('zu den Guidings')}}</a>
        </div>
    @endif
@endsection
@section('js_after')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script>
 $('#myTable').DataTable( {
    responsive: true,
    ordering: false
} );

    </script>
@endsection