@extends('admin.layouts.app')

@section('title', 'Alle Buchungen')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Verwaltung</a></li>
                        <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
                    </ol>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap border-bottom" id="booking-datatable">
                                    <thead>
                                    <tr>
                                        <th class="wd-20p border-bottom-0">ID</th>
                                        <th class="wd-20p border-bottom-0">Customer</th>
                                        <th class="wd-15p border-bottom-0">Phone #</th>
                                        <th class="wd-15p border-bottom-0">Price</th>
                                        <th class="wd-15p border-bottom-0">Guide Share</th>
                                        <th class="wd-15p border-bottom-0">CaG Share</th>
                                        <th class="wd-15p border-bottom-0">Booking Date</th>
                                        <th class="wd-15p border-bottom-0">Transaction</th>
                                        <th class="wd-15p border-bottom-0">Status</th>
                                        <th class="wd-15p border-bottom-0">Guide</th>
                                        <th class="wd-15p border-bottom-0">Guiding</th>
                                        <th class="wd-15p border-bottom-0">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bookings as $booking)
                                            @php
                                                $price = $booking->guiding->price_type == 'per_boat' ? $booking->price * $booking->count_of_users : $booking->price;
                                            @endphp
                                            <tr class="{{ $booking->is_guest ? 'bg-warning' : '' }}">
                                                <td>{{ $booking->id }}</td>
                                                <td>
                                                    @if ($booking->user)
                                                        {{ $booking->user->firstname ?? 'Guest' }} {{ $booking->user->lastname ?? '' }}
                                                    @endif
                                                </td>
                                                <td>{{ $booking->phone }}</td>
                                                <td>{{ two($price) }} €</td>
                                                <td>{{ two($price - $booking->cag_percent) }} €</td>
                                                <td>{{ two($booking->cag_percent) }} €</td>
                                                <td>
                                                    @if($booking->blocked_event)
                                                        {{ \Carbon\Carbon::parse($booking->blocked_event->due)->format('d.m.Y') }}
                                                    @else
                                                        -storniert-
                                                    @endif
                                                </td>
                                                <td>{{ $booking->transaction_id }}</td>
                                                <td>
                                                    <span class="@if($booking->status == 'rejected' || $booking->status == 'cancelled') text-danger @elseif($booking->status == 'accepted') text-success @else {{ !$booking->is_guest ? "text-warning" : "" }} glow @endif">
                                                        {{ strtoupper($booking->status) }}
                                                        @if($booking->is_guest)
                                                            <br>
                                                            <small class="text-muted">Guest Checkout</small>
                                                        @endif
                                                    </span>
                                                    @if($booking->last_employee_id)

                                                        <br>

                                                        <span class="text-info">by {{ $booking->employee->name }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{route('admin.guides.edit', $booking->guiding->user->id)}}">
                                                        {{ $booking->guiding->user->full_name }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="{{route('admin.guidings.edit', $booking->guiding->id)}}">
                                                        {{$booking->guiding->title}}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if($booking->status == 'pending')
                                                        <a href="{{ route('booking.accept', $booking->token . '|' . auth()->user()->id) }}" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                    @endif

                                                    <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-sm btn-secondary"><i class="fa fa-pen"></i></a>
                                                    <a href="javascript:deleteResource('{{ route('admin.bookings.destroy', $booking, false) }}')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-info" onclick="showEmailPreview({{ $booking->id }})"><i class="fa fa-envelope"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- CONTAINER CLOSED -->

    </div>

    <!-- Email Preview Modal -->
    <div class="modal fade" id="emailPreviewModal" tabindex="-1" role="dialog" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailPreviewModalLabel">Email Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Main tabs: Guest vs Guide -->
                    <ul class="nav nav-pills mb-3" id="emailTypesTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="guest-emails-tab" data-bs-toggle="tab" data-bs-target="#guest-emails" type="button" role="tab" aria-controls="guest-emails" aria-selected="true">Guest Emails</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="guide-emails-tab" data-bs-toggle="tab" data-bs-target="#guide-emails" type="button" role="tab" aria-controls="guide-emails" aria-selected="false">Guide Emails</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="emailTypesTabsContent">
                        <!-- Guest Emails Tab -->
                        <div class="tab-pane fade show active" id="guest-emails" role="tabpanel" aria-labelledby="guest-emails-tab">
                            <!-- Guest Email Types -->
                            <ul class="nav nav-tabs nav-fill flex-column flex-sm-row" id="guestEmailTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="guest-booking-request-tab" data-bs-toggle="tab" data-bs-target="#guest-booking-request" type="button" role="tab" aria-controls="guest-booking-request" aria-selected="true">Booking Request</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="expired-booking-tab" data-bs-toggle="tab" data-bs-target="#guest-expired-booking" type="button" role="tab" aria-controls="guest-expired-booking" aria-selected="false">Expired Booking</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="accepted-booking-tab" data-bs-toggle="tab" data-bs-target="#guest-accepted-booking" type="button" role="tab" aria-controls="guest-accepted-booking" aria-selected="false">Accepted Booking</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="rejected-booking-tab" data-bs-toggle="tab" data-bs-target="#guest-rejected-booking" type="button" role="tab" aria-controls="guest-rejected-booking" aria-selected="false">Rejected Booking</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tour-reminder-tab" data-bs-toggle="tab" data-bs-target="#guest-tour-reminder" type="button" role="tab" aria-controls="guest-tour-reminder" aria-selected="false">Tour Reminder</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="guest-review-tab" data-bs-toggle="tab" data-bs-target="#guest-review" type="button" role="tab" aria-controls="guest-review" aria-selected="false">Guest Review</button>
                                </li>
                            </ul>
                            
                            <!-- Guest Email Content -->
                            <div class="tab-content mt-3" id="guestEmailTabsContent">
                                <!-- Booking Request Email -->
                                <div class="tab-pane fade show active" id="guest-booking-request" role="tabpanel" aria-labelledby="guest-booking-request-tab">
                                    <iframe id="booking-request-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="booking-request-not-available" class="alert alert-warning d-none">
                                        Booking request email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Expired Booking Email -->
                                <div class="tab-pane fade" id="guest-expired-booking" role="tabpanel" aria-labelledby="expired-booking-tab">
                                    <iframe id="expired-booking-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="expired-booking-not-available" class="alert alert-warning d-none">
                                        Expired booking email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Accepted Booking Email -->
                                <div class="tab-pane fade" id="guest-accepted-booking" role="tabpanel" aria-labelledby="accepted-booking-tab">
                                    <iframe id="accepted-booking-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="accepted-booking-not-available" class="alert alert-warning d-none">
                                        Accepted booking email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Rejected Booking Email -->
                                <div class="tab-pane fade" id="guest-rejected-booking" role="tabpanel" aria-labelledby="rejected-booking-tab">
                                    <iframe id="rejected-booking-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="rejected-booking-not-available" class="alert alert-warning d-none">
                                        Rejected booking email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Tour Reminder Email -->
                                <div class="tab-pane fade" id="guest-tour-reminder" role="tabpanel" aria-labelledby="tour-reminder-tab">
                                    <iframe id="tour-reminder-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="tour-reminder-not-available" class="alert alert-warning d-none">
                                        Tour reminder email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Guest Review Email -->
                                <div class="tab-pane fade" id="guest-review" role="tabpanel" aria-labelledby="guest-review-tab">
                                    <iframe id="guest-review-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="guest-review-not-available" class="alert alert-warning d-none">
                                        Guest review email template is not available.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Guide Emails Tab -->
                        <div class="tab-pane fade" id="guide-emails" role="tabpanel" aria-labelledby="guide-emails-tab">
                            <!-- Guide Email Types -->
                            <ul class="nav nav-tabs nav-fill flex-column flex-sm-row" id="guideEmailTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="guide-booking-request-tab" data-bs-toggle="tab" data-bs-target="#guide-booking-request" type="button" role="tab" aria-controls="guide-booking-request" aria-selected="true">Booking Request</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="guide-expired-booking-tab" data-bs-toggle="tab" data-bs-target="#guide-expired-booking" type="button" role="tab" aria-controls="guide-expired-booking" aria-selected="false">Expired Booking</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="guide-accepted-booking-tab" data-bs-toggle="tab" data-bs-target="#guide-accepted-booking" type="button" role="tab" aria-controls="guide-accepted-booking" aria-selected="false">Accepted Booking</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="guide-reminder-tab" data-bs-toggle="tab" data-bs-target="#guide-reminder" type="button" role="tab" aria-controls="guide-reminder" aria-selected="false">24h Reminder</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="guide-reminder-12hrs-tab" data-bs-toggle="tab" data-bs-target="#guide-reminder-12hrs" type="button" role="tab" aria-controls="guide-reminder-12hrs" aria-selected="false">12h Reminder</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="guide-upcoming-tour-tab" data-bs-toggle="tab" data-bs-target="#guide-upcoming-tour" type="button" role="tab" aria-controls="guide-upcoming-tour" aria-selected="false">Upcoming Tour</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="guide-review-confirmation-tab" data-bs-toggle="tab" data-bs-target="#guide-review-confirmation" type="button" role="tab" aria-controls="guide-review-confirmation" aria-selected="false">Review Confirmation</button>
                                </li>
                            </ul>
                            
                            <!-- Guide Email Content -->
                            <div class="tab-content mt-3" id="guideEmailTabsContent">
                                <!-- Guide Booking Request Email -->
                                <div class="tab-pane fade show active" id="guide-booking-request" role="tabpanel" aria-labelledby="guide-booking-request-tab">
                                    <iframe id="guide-booking-request-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="guide-booking-request-not-available" class="alert alert-warning d-none">
                                        Guide booking request email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Guide Expired Booking Email -->
                                <div class="tab-pane fade" id="guide-expired-booking" role="tabpanel" aria-labelledby="guide-expired-booking-tab">
                                    <iframe id="guide-expired-booking-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="guide-expired-booking-not-available" class="alert alert-warning d-none">
                                        Guide expired booking email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Guide Accepted Booking Email -->
                                <div class="tab-pane fade" id="guide-accepted-booking" role="tabpanel" aria-labelledby="guide-accepted-booking-tab">
                                    <iframe id="guide-accepted-booking-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="guide-accepted-booking-not-available" class="alert alert-warning d-none">
                                        Guide accepted booking email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Guide Reminder Email -->
                                <div class="tab-pane fade" id="guide-reminder" role="tabpanel" aria-labelledby="guide-reminder-tab">
                                    <iframe id="guide-reminder-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="guide-reminder-not-available" class="alert alert-warning d-none">
                                        Guide reminder email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Guide 12hrs Reminder Email -->
                                <div class="tab-pane fade" id="guide-reminder-12hrs" role="tabpanel" aria-labelledby="guide-reminder-12hrs-tab">
                                    <iframe id="guide-reminder-12hrs-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="guide-reminder-12hrs-not-available" class="alert alert-warning d-none">
                                        Guide 12hrs reminder email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Guide Upcoming Tour Email -->
                                <div class="tab-pane fade" id="guide-upcoming-tour" role="tabpanel" aria-labelledby="guide-upcoming-tour-tab">
                                    <iframe id="guide-upcoming-tour-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="guide-upcoming-tour-not-available" class="alert alert-warning d-none">
                                        Guide upcoming tour email template is not available.
                                    </div>
                                </div>
                                
                                <!-- Guide Review Confirmation Email -->
                                <div class="tab-pane fade" id="guide-review-confirmation" role="tabpanel" aria-labelledby="guide-review-confirmation-tab">
                                    <iframe id="guide-review-confirmation-iframe" style="width: 100%; height: 500px; border: none;"></iframe>
                                    <div id="guide-review-confirmation-not-available" class="alert alert-warning d-none">
                                        Guide review confirmation email template is not available.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showEmailPreview(bookingId) {
            fetch(`/admin/bookings/${bookingId}/email-preview`)
                .then(response => response.json())
                .then(data => {
                    // Guest emails
                    if (data.bookingRequestEmail) {
                        document.getElementById('booking-request-iframe').srcdoc = data.bookingRequestEmail;
                        document.getElementById('booking-request-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('booking-request-iframe').srcdoc = '';
                        document.getElementById('booking-request-not-available').classList.remove('d-none');
                    }
                    
                    if (data.expiredBookingEmail) {
                        document.getElementById('expired-booking-iframe').srcdoc = data.expiredBookingEmail;
                        document.getElementById('expired-booking-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('expired-booking-iframe').srcdoc = '';
                        document.getElementById('expired-booking-not-available').classList.remove('d-none');
                    }
                    
                    if (data.acceptedBookingEmail) {
                        document.getElementById('accepted-booking-iframe').srcdoc = data.acceptedBookingEmail;
                        document.getElementById('accepted-booking-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('accepted-booking-iframe').srcdoc = '';
                        document.getElementById('accepted-booking-not-available').classList.remove('d-none');
                    }
                    
                    if (data.rejectedBookingEmail) {
                        document.getElementById('rejected-booking-iframe').srcdoc = data.rejectedBookingEmail;
                        document.getElementById('rejected-booking-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('rejected-booking-iframe').srcdoc = '';
                        document.getElementById('rejected-booking-not-available').classList.remove('d-none');
                    }
                    
                    if (data.tourReminderEmail) {
                        document.getElementById('tour-reminder-iframe').srcdoc = data.tourReminderEmail;
                        document.getElementById('tour-reminder-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('tour-reminder-iframe').srcdoc = '';
                        document.getElementById('tour-reminder-not-available').classList.remove('d-none');
                    }
                    
                    if (data.guestReviewEmail) {
                        document.getElementById('guest-review-iframe').srcdoc = data.guestReviewEmail;
                        document.getElementById('guest-review-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('guest-review-iframe').srcdoc = '';
                        document.getElementById('guest-review-not-available').classList.remove('d-none');
                    }
                    
                    // Guide emails
                    if (data.guideBookingRequestEmail) {
                        document.getElementById('guide-booking-request-iframe').srcdoc = data.guideBookingRequestEmail;
                        document.getElementById('guide-booking-request-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('guide-booking-request-iframe').srcdoc = '';
                        document.getElementById('guide-booking-request-not-available').classList.remove('d-none');
                    }
                    
                    if (data.guideExpiredBookingEmail) {
                        document.getElementById('guide-expired-booking-iframe').srcdoc = data.guideExpiredBookingEmail;
                        document.getElementById('guide-expired-booking-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('guide-expired-booking-iframe').srcdoc = '';
                        document.getElementById('guide-expired-booking-not-available').classList.remove('d-none');
                    }
                    
                    if (data.guideAcceptedBookingEmail) {
                        document.getElementById('guide-accepted-booking-iframe').srcdoc = data.guideAcceptedBookingEmail;
                        document.getElementById('guide-accepted-booking-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('guide-accepted-booking-iframe').srcdoc = '';
                        document.getElementById('guide-accepted-booking-not-available').classList.remove('d-none');
                    }
                    
                    if (data.guideReminderEmail) {
                        document.getElementById('guide-reminder-iframe').srcdoc = data.guideReminderEmail;
                        document.getElementById('guide-reminder-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('guide-reminder-iframe').srcdoc = '';
                        document.getElementById('guide-reminder-not-available').classList.remove('d-none');
                    }
                    
                    if (data.guideReminder12hrsEmail) {
                        document.getElementById('guide-reminder-12hrs-iframe').srcdoc = data.guideReminder12hrsEmail;
                        document.getElementById('guide-reminder-12hrs-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('guide-reminder-12hrs-iframe').srcdoc = '';
                        document.getElementById('guide-reminder-12hrs-not-available').classList.remove('d-none');
                    }
                    
                    if (data.guideUpcomingTourEmail) {
                        document.getElementById('guide-upcoming-tour-iframe').srcdoc = data.guideUpcomingTourEmail;
                        document.getElementById('guide-upcoming-tour-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('guide-upcoming-tour-iframe').srcdoc = '';
                        document.getElementById('guide-upcoming-tour-not-available').classList.remove('d-none');
                    }
                    
                    // Guide review confirmation email
                    if (data.guideReviewConfirmationEmail) {
                        document.getElementById('guide-review-confirmation-iframe').srcdoc = data.guideReviewConfirmationEmail;
                        document.getElementById('guide-review-confirmation-not-available').classList.add('d-none');
                    } else {
                        document.getElementById('guide-review-confirmation-iframe').srcdoc = '';
                        document.getElementById('guide-review-confirmation-not-available').classList.remove('d-none');
                    }
                    
                    // Show the modal
                    var modal = new bootstrap.Modal(document.getElementById('emailPreviewModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading email previews:', error);
                    alert('Failed to load email previews');
                });
        }
    </script>
@endsection
