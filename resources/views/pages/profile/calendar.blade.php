@extends('pages.profile.layouts.profile')

@section('title', __('profile.calendar'))
@section('css_after')
    <style>
        .fc .fc-button-primary {
            background-color: var(--thm-primary);
            border-color: var(--thm-primary);
        }
        .fc .fc-toolbar-title {
            color: var(--thm-primary);
        }

        a:hover {
            color: var(--thm-primary);
        }
        .fc-daygrid-event-dot {
            margin: 0 4px;
            box-sizing: content-box;
            width: 0;
            height: 0;
            border: 4px solid #3788d8;
            border: calc(var(--fc-daygrid-event-dot-width,8px)/ 2) solid  var(--thm-primary);
            border-radius: 4px;
            border-radius: calc(var(--fc-daygrid-event-dot-width,8px)/ 2);
        }

        @media screen and (max-width:767px) { .fc-toolbar.fc-header-toolbar {font-size: 60%}}
        
    </style>
@stop

@section('profile-content')

  <div class="container">
    <section class="page-header">
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span>&#183;</span></li>
                        <li><a href="{{ route('profile.index') }}">{{ translate('Profile') }}</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">
                            {{ translate('Kalender') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
  </div>
    <div class="container" style=" margin-bottom: 120px;">
        <div class="col-md-12">
            <div id="calendar" class="mt-3"></div>
        </div>
   
    </div>

    <!--add event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEventModalLabel">@lang('profile.blockade')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('profile.calendar.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="addNewBeginInput">@lang('profile.beginning')</label>
                                    <input type="date" id="addNewBeginInput" class="form-control" name="start" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="addNewEndInput">@lang('profile.ending')</label>
                                    <input type="date" id="addNewEndInput" class="form-control" name="end" required>
                                </div>
                            </div>
                            <div class="col-12 my-2 mt-3">
                                <span class="color-primary">Block by Weekday (optional)</span>
                              
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                          <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" id="blockday" value="1">
                                            @lang('message.monday')
                                          </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                          <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" id="blockday" value="2">
                                            @lang('message.tuesday')
                                          </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                          <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" id="blockday" value="3">
                                            @lang('message.wednesday')
                                          </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                          <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" id="blockday" value="4">
                                            @lang('message.thursday')
                                          </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                          <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" id="blockday" value="5">
                                            @lang('message.friday')
                                          </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                          <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" id="blockday" value="6">
                                            @lang('message.saturday')
                                          </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                          <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" id="blockday" value="7">
                                            @lang('message.sunday')
                                          </label>
                                        </div>
                                    </div>
                                </div>

                                
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('profile.interrupt')</button>
                        <button type="submit" class="btn btn-primary">@lang('profile.saveComputer')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Delete Event Modal -->
    <!-- Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEventModalLabel">@lang('profile.clearBtn')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            @lang('profile.clearMsg')
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('profile.interrupt')</button>
                    <a id="deleteroute">
                        <button type="button" class="btn btn-block btn-danger" style="background-color: #E8604C;">@lang('profile.clearBD')</button>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bookdetail" tabindex="-1" aria-labelledby="bookdetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <!-- Modal content here -->
                <div class="">
                    <div class="mb-3 py-2 text-center theme-primary">
                        <h5 class="text-white">@lang('message.booking-overview')</h5>
                    </div>
                    <div class="card border-0">
                        <div class="card-body mx-4">
                          <div class="container">
                            <div class="row">
                                <div class="col-xl-10">
                                  <p>@lang('profile.fname')</p>
                                </div>
                                <div class="col-xl-2">
                                  <p class="d-flex justify-content-md-end"><strong id="user-name"></strong>
                                  </p>
                                </div>
                                <hr>
                            </div>
                            <div class="row">
                              <div class="col-xl-10">
                                <p>@lang('message.modal-email')</p>
                              </div>
                              <div class="col-xl-2">
                                <p class="d-flex justify-content-md-end"><span id="bookemail" class="text-black">
                                </p>
                              </div>
                              <hr>
                            </div>
                            <div class="row">
                              <div class="col-xl-10">
                                <p>@lang('mailing.pNumber')</p>
                              </div>
                              <div class="col-xl-2">
                                <p class="d-flex justify-content-md-end"><span id="bookcontact" class="text-black"></span>
                                </p>
                              </div>
                              <hr>
                            </div>
                            <div class="row">
                                <div class="col-xl-10">
                                  <p>@lang('profile.guests')</p>
                                </div>
                                <div class="col-xl-2">
                                  <p class="d-flex justify-content-md-end"><span id="bookguest" class="text-black"></span>
                                  </p>
                                </div>
                                <hr>
                            </div>
                            <div class="row">
                                <div class="col-xl-10">
                                  <p>@lang('profile.meetingPoint')</p>
                                </div>
                                <div class="col-xl-2">
                                  <p class="d-flex justify-content-md-end"><span id="bookmeetingpoint" class="text-black"></span>
                                  </p>
                                </div>
                                <hr>
                            </div>
                            <div class="row">
                              <div class="col-xl-10">
                                <p>Total Extra</p>
                              </div>
                              <div class="col-xl-2">
                                <p class="d-flex justify-content-md-end"><span id="booktotalextra" class="text-black"></span>€
                                </p>
                              </div>
                              <hr style="border: 2px solid black;">
                            </div>
                            <div class="row text-black">
                      
                              <div class="col-xl-12">
                                <p class="d-flex justify-content-md-end fw-bold">@lang('profile.total-price'):<span id="total-price" class="ms-1"></span>€
                                </p>
                              </div>
                            </div>
                          </div>
                          <div class="text-center">
                            <button type="button" class="btn theme-primary"  data-bs-dismiss="modal">Close
                            </button>
                          </div>
                         
                        </div>
                      </div>
                
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js_after')
    <script>
        document.querySelector('style').textContent += "@media screen and (max-width:767px) { .fc-toolbar.fc-header-toolbar {flex-direction:column;} .fc-toolbar-chunk { display: table-row; text-align:center; padding:5px 0; } }";
        document.addEventListener('DOMContentLoaded', function() {
            @if(app()->getLocale() == 'de')
                var local = 'de';
            @elseif(app()->getLocale() == 'en')
                var local = 'en';
            @endif
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                windowResize: true,
                initialView: 'dayGridMonth',
                height: 650,
                locale: local,
                events: {
                    url: '/events',
                    method: 'GET',
                },
                eventClick: function (info) {
             
                    if(info.event.title === 'blockiert' || info.event.title === 'Blocked') {
                        url = "{{URL::to('/')}}/profile/calendar/delete/"+info.event.id;
                        $("#deleteroute").attr("href", url);    
                        $('#deleteEventModal').modal('show');
              
                    }
                    if(info.event.title === 'Booked') {
            
                        $('#bookdetail').modal('show');
                        // document.getElementById('guiding-title').innerHTML = info.event.extendedProps.guiding.data.title;
                        document.getElementById('total-price').innerHTML = info.event.extendedProps.booking.data.price;
                        document.getElementById('user-name').innerHTML = info.event.extendedProps.user.data.firstname;
                        document.getElementById('bookcontact').innerHTML = info.event.extendedProps.booking.data.phone;
                        document.getElementById('bookemail').innerHTML = info.event.extendedProps.user.data.email;
                        document.getElementById('bookguest').innerHTML = info.event.extendedProps.booking.data.count_of_users;
                        document.getElementById('bookmeetingpoint').innerHTML = info.event.extendedProps.guiding.data.meeting_point;
                        document.getElementById('booktotalextra').innerHTML = info.event.extendedProps.booking.data.total_extra_price;
                        
                    }
                },
                eventDisplay : 'auto',
                displayEventTime: false,
                customButtons: {
                    addEvent: {
                        text: '@lang('profile.blockade')',
                        click: function () {
                            $('#addEventModal').modal('show')
                        },
                    }, 
                },
                headerToolbar: {
                    @if(!$agent->ismobile())
                        center: 'title',
                        left: 'dayGridMonth,timeGridWeek,timeGridDay',
                        right: 'addEvent prev,next',
                    @else
                        right: 'prev,next',
                    @endif
                },
                @if($agent->ismobile())
                footerToolbar: {
                    center: 'addEvent',
                }
                @endif
            });
            calendar.render();
        });
    </script>
@endsection
