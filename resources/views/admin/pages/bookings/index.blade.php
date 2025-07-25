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
                                            {{-- @php
                                                $price = $booking->guiding->price_type == 'per_boat' ? $booking->price * $booking->count_of_users : $booking->price;
                                            @endphp --}}
                                            <tr class="{{ $booking->is_guest ? 'bg-warning' : '' }}">
                                                <td>{{ $booking->id }}</td>
                                                <td>
                                                    @if ($booking->user)
                                                        {{ $booking->user->firstname ?? 'Guest' }} {{ $booking->user->lastname ?? '' }}
                                                    @endif
                                                </td>
                                                <td>{{ $booking->phone }}</td>
                                                <td>{{ two($booking->price) }} €</td>
                                                <td>{{ two($booking->price - $booking->cag_percent) }} €</td>
                                                <td>{{ two($booking->cag_percent) }} €</td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($booking->book_date)->format('d.m.Y') }}
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
                                                        <a href="{{ route('booking.accept', $booking->token) }}" class="btn btn-sm btn-success"><i class="fa fa-check"></i></a>
                                                        <a href="{{ route('booking.reject', $booking->token) }}" class="btn btn-sm btn-danger"><i class="fa fa-times-circle"></i></a>
                                                    @endif

                                                    <a href="javascript:void(0)" class="btn btn-sm btn-secondary" onclick="showEditBookingModal({{ $booking->id }})"><i class="fa fa-pen"></i></a>
                                                    <a href="javascript:deleteResource('{{ route('admin.bookings.destroy', $booking, false) }}')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>
                                                    <a href="javascript:void(0)" class="btn btn-sm btn-info" onclick="showEmailPreview({{ $booking->id }})"><i class="fa fa-envelope"></i></a>
                                                    <button class="btn btn-sm btn-warning" onclick="showResendModal(
                                                        {{ $booking->id }}, 
                                                        {{ json_encode($booking->user ? ($booking->user->firstname . ' ' . $booking->user->lastname) : ($booking->firstname . ' ' . $booking->lastname)) }}, 
                                                        {{ json_encode($booking->email ?: ($booking->user ? $booking->user->email : '')) }}, 
                                                        {{ json_encode($booking->guiding->user->firstname . ' ' . $booking->guiding->user->lastname) }}, 
                                                        {{ json_encode($booking->guiding->user->email) }}
                                                    )">
                                                        <i class="fa fa-paper-plane"></i> Resend Emails
                                                    </button>
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

    <!-- Resend Email Confirmation Modal -->
    <div class="modal fade" id="resendEmailModal" tabindex="-1" role="dialog" aria-labelledby="resendEmailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resendEmailModalLabel">
                        <i class="fa fa-paper-plane"></i> Resend Booking Request Emails
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <strong>Confirmation Required</strong>
                        <p class="mb-0">You are about to send booking request emails. This will send emails to both the guest and guide if they haven't been sent within the last 24 hours.</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fa fa-user"></i> Guest Information</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Name:</strong></p>
                                    <p class="text-muted" id="guest-name">-</p>
                                    <p class="mb-1"><strong>Email:</strong></p>
                                    <p class="text-muted" id="guest-email">-</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fa fa-user-tie"></i> Guide Information</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Name:</strong></p>
                                    <p class="text-muted" id="guide-name">-</p>
                                    <p class="mb-1"><strong>Email:</strong></p>
                                    <p class="text-muted" id="guide-email">-</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <p class="mb-2"><strong>Booking ID:</strong> <span id="booking-id-display" class="badge bg-secondary">-</span></p>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="confirmSend" required>
                            <label class="form-check-label" for="confirmSend">
                                I confirm that I want to send these booking request emails
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmSendBtn" onclick="confirmSendEmails()" disabled>
                        <i class="fa fa-paper-plane"></i> Send Emails
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Booking Modal -->
    <div class="modal fade" id="editBookingModal" tabindex="-1" role="dialog" aria-labelledby="editBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBookingModalLabel">Edit Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editBookingForm">
                        <input type="hidden" id="edit-booking-id" name="id">
                        <div class="mb-3">
                            <label for="edit-booking-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit-booking-email" name="email">
                        </div>
                        <div class="mb-3">
                            <label for="edit-booking-phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="edit-booking-phone" name="phone">
                        </div>
                        <div class="mb-3" id="edit-booking-status-group" style="display:none;">
                            <label for="edit-booking-status" class="form-label">Status</label>
                            <select class="form-control" id="edit-booking-status" name="status">
                                <option value="pending">Pending</option>
                                <option value="accepted">Accepted</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveBookingBtn" onclick="saveBookingEdit()">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentBookingId = null;

        function showResendModal(bookingId, guestName, guestEmail, guideName, guideEmail) {
            currentBookingId = bookingId;
            
            // Populate modal with booking details
            document.getElementById('booking-id-display').textContent = bookingId;
            document.getElementById('guest-name').textContent = guestName || 'N/A';
            document.getElementById('guest-email').textContent = guestEmail || 'N/A';
            document.getElementById('guide-name').textContent = guideName || 'N/A';
            document.getElementById('guide-email').textContent = guideEmail || 'N/A';
            
            // Reset confirmation checkbox
            document.getElementById('confirmSend').checked = false;
            document.getElementById('confirmSendBtn').disabled = true;
            
            // Show the modal
            var modal = new bootstrap.Modal(document.getElementById('resendEmailModal'));
            modal.show();
        }

        // Enable/disable send button based on checkbox
        document.getElementById('confirmSend').addEventListener('change', function() {
            document.getElementById('confirmSendBtn').disabled = !this.checked;
        });

        function confirmSendEmails() {
            if (!document.getElementById('confirmSend').checked) {
                alert('Please confirm by checking the checkbox');
                return;
            }

            // Get CSRF token with error handling
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement) {
                console.error('CSRF token meta tag not found');
                alert('Error: CSRF token not found. Please refresh the page and try again.');
                return;
            }

            const csrfToken = csrfTokenElement.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token content is empty');
                alert('Error: CSRF token is empty. Please refresh the page and try again.');
                return;
            }

            const confirmBtn = document.getElementById('confirmSendBtn');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';
            confirmBtn.disabled = true;

            fetch(`/admin/bookings/${currentBookingId}/send-booking-request-emails`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message with details
                    let message = '✅ Success!\n\n' + data.message;
                    if (data.emails_sent > 0) {
                        message += `\n\n📧 ${data.emails_sent} email(s) sent successfully`;
                    }
                    if (data.emails_skipped > 0) {
                        message += `\n⚠️ ${data.emails_skipped} email(s) were already sent (within 24 hours)`;
                    }
                    alert(message);
                    
                    // Close the modal
                    bootstrap.Modal.getInstance(document.getElementById('resendEmailModal')).hide();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error sending emails:', error);
                alert('❌ Error sending emails. Please check the console for details and try again.');
            })
            .finally(() => {
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
            });
        }

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

        function showEditBookingModal(bookingId) {
            fetch(`/admin/bookings/${bookingId}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit-booking-id').value = data.id;
                    document.getElementById('edit-booking-email').value = data.email || '';
                    document.getElementById('edit-booking-phone').value = data.phone || '';
                    document.getElementById('edit-booking-status').value = data.status;
                    if (data.allowed_status_edit) {
                        document.getElementById('edit-booking-status-group').style.display = '';
                    } else {
                        document.getElementById('edit-booking-status-group').style.display = 'none';
                    }
                    var modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
                    modal.show();
                })
                .catch(error => {
                    alert('Failed to load booking data.');
                });
        }

        function saveBookingEdit() {
            const bookingId = document.getElementById('edit-booking-id').value;
            const email = document.getElementById('edit-booking-email').value;
            const phone = document.getElementById('edit-booking-phone').value;
            const statusGroup = document.getElementById('edit-booking-status-group');
            let status = undefined;
            if (statusGroup.style.display !== 'none') {
                status = document.getElementById('edit-booking-status').value;
            }
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement) {
                alert('CSRF token not found.');
                return;
            }
            const csrfToken = csrfTokenElement.getAttribute('content');
            const payload = { email, phone };
            if (typeof status !== 'undefined') payload.status = status;
            fetch(`/admin/bookings/${bookingId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Booking updated successfully.');
                    // Optionally update the table row here
                    location.reload(); // For simplicity, reload the page
                } else {
                    alert('Failed to update booking: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error updating booking.');
            });
        }
    </script>
@endsection
