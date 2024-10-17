@extends('pages.profile.layouts.profile')
@section('title', __('profile.guidebookings'))
@section('css_after')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
@endsection
@section('profile-content')
    {{-- @if(count($authUser->guidings)) 
        @foreach($authUser->guidings as $guiding)
            @if(count($guiding->bookings))
            @foreach($guiding->bookings as $booking)
            {{$booking}}
            @endforeach
            @endif
        @endforeach
    @endif --}}

    <table id="myTable" class="display nowrap table table-hover" style="width:100%">
        <thead >
            <tr>
                <th>@lang('profile.customer')</th>
                <th>@lang('profile.guiding')</th>
                <th>@lang('profile.when')</th>
                <th>@lang('profile.guest')</th>
                <th>@lang('profile.price')</th>
                <th>@lang('profile.chat')</th>
                <th>@lang('profile.contact')</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($guideBookings as $index =>  $booking)
            <div class="modal fade" id="emailmodal{{$index}}" tabindex="-1" aria-labelledby="emailmodal{{$index}}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="contact">E-mail</h5>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <p><strong>E-mail:</strong> {{$booking->user->email}}</p>
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
                                <p><strong>@lang('profile.tele'):</strong> {{$booking->phone}}</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <tr>
                <td>{{ $booking->user->full_name }}</td>
                <td>{{ $booking->guiding->title }}</td>
                <td>
                    @if($booking->blocked_event)    
                        {{ \Carbon\Carbon::parse($booking->blocked_event->from)->format('F j, Y') }}
                    @else
                    <span class="badge badge-pill badge-danger">@lang('profile.bwm-notavailable')</span>
                        {{-- @lang('profile.cancel') --}}
                    @endif
                </td>
                <td>{{ $booking->count_of_users }}</td>
                <td>{{$booking->price}}€</td>
                <td>
                    @if($booking->status == 'accepted')
                    <a  class="badge py-2 px-2 thm-btn" href="{{ route('chat.create', $booking->user_id) }}">@lang('profile.mess')</a>
                    @else
                    <span class="badge badge-pill badge-danger">@lang('profile.bwm-notavailable')</span>
                    @endif
                </td>
                <td>
                    @switch($booking->status)
                    @case('accepted')
                        <div class="d-flex">
                            <div>
                                <button type="button" class="btn thm-btn btn-sm" data-bs-toggle="modal" data-bs-target="#emailmodal{{$index}}" style="background-color:rgb(54, 109, 182)">
                                    E-Mail
                                </button>    
                                @if($booking->phone)               
                                <button type="button" class="btn thm-btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#phonemodal{{$index}}">
                                    @lang('profile.pnumber')
                                </button>
                                @endif
                            </div>
                        </div>

                        @break
                    @case('pending')
                        <span class="badge badge-pill badge-danger">@lang('profile.bwm-notavailable')</span>
                        @break
                    @case('cancelled')
                        <span class="badge badge-pill badge-danger">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('rejected')
                    <span class="badge badge-pill badge-danger">@lang('profile.bwm-notavailable')</span>
                    @break
                    @case('storniert')
                        <span class="badge badge-pill badge-danger">@lang('profile.bwm-notavailable')</span>
                    @break

                    @default
                        <button type="button" class="btn thm-btn btn-sm" data-bs-toggle="modal" data-bs-target="#emailmodal{{$index}}" style="background-color:rgb(54, 109, 182)">
                            E-Mail
                        </button>
                        @if($booking->phone)
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
                            <a class="btn btn-warning btn-sm" href="{{ route('profile.guidebookings.accept',[$booking]) }}">@lang('profile.gn-accept')</a>
                            <a class="btn btn-danger btn-sm" href="{{ route('profile.guidebookings.reject',[$booking]) }}">@lang('profile.gn-reject')</a>
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