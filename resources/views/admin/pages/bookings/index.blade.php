@extends('admin.layouts.app')

@section('title', 'Alle Buchungen')

@section('content')
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <h1 class="page-title">@yield('title')</h1>
                <div class="ms-auto d-flex">
                    <button type="button"
                            class="btn btn-primary ms-3"
                            data-bs-toggle="modal"
                            data-bs-target="#manualBookingModal">
                        <i class="fa fa-plus"></i> Create Booking
                    </button>
                </div>

            </div>
            <!-- PAGE-HEADER END -->
            <!-- Row -->
            <div class="row row-sm">
                <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive booking-table-responsive booking-table__wrapper booking-table">
                                    <table class="table table-bordered text-nowrap border-bottom" id="booking-datatable">
                                        <thead>
                                        <tr>
                                            <th class="wd-20p border-bottom-0 col-id">ID</th>
                                            <th class="wd-20p border-bottom-0 col-customer">Customer</th>
                                            <th class="wd-15p border-bottom-0">Date / Checkout</th>
                                            <th class="wd-15p border-bottom-0">Price / Shares</th>
                                            <th class="wd-15p border-bottom-0">Status</th>
                                            <th class="wd-20p border-bottom-0">Guide / Tour</th>
                                            <th class="wd-10p border-bottom-0 col-action">Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($bookings as $booking)
                                            @php
                                                $bookingDateTime = $booking->getFormattedBookingDate('d.m.Y H:i');
                                                $canSendGuideInvoice = $booking->status === 'accepted'
                                                    && $booking->isBookingOver()
                                                    && optional($booking->guiding)->user
                                                    && optional($booking->guiding->user)->email;
                                            @endphp
                                            {{-- @php
                                                $price = $booking->guiding->price_type == 'per_boat' ? $booking->price * $booking->count_of_users : $booking->price;
                                            @endphp --}}
                                            @php
                                                $rowClass = $booking->is_guest
                                                    ? 'booking-table__row booking-table__row--guest'
                                                    : 'booking-table__row booking-table__row--registered';
                                            @endphp
                                            <tr class="{{ $rowClass }}">
                                                <td class="col-id booking-table__cell booking-table__cell--id">
                                                    <div class="booking-table__id">
                                                        <span class="booking-table__id-number">{{ $booking->id }}</span>
                                                        <span class="booking-table__checkout-badge {{ $booking->is_guest ? 'booking-table__checkout-badge--guest' : 'booking-table__checkout-badge--member' }}">
                                                            {{ $booking->is_guest ? 'Guest' : 'Account' }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="col-customer booking-table__cell booking-table__cell--customer">
                                                    <div class="booking-table__customer-name">
                                                        @if ($booking->user)
                                                            {{ $booking->user->firstname ?? 'Guest' }} {{ $booking->user->lastname ?? '' }}
                                                        @else
                                                            Guest
                                                        @endif
                                                    </div>
                                                    <div class="booking-table__customer-meta">
                                                        {{ $booking->email ?? ($booking->user->email ?? '—') }}
                                                    </div>
                                                    @if($booking->phone)
                                                        <div class="booking-table__customer-meta">
                                                            {{ $booking->phone }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="booking-table__cell booking-table__cell--date">
                                                    <div class="booking-table__checkout">
                                                        <div class="booking-table__checkout-date">
                                                            <i class="fa fa-calendar-alt me-1"></i>
                                                            {{ \Carbon\Carbon::parse($booking->book_date)->format('d.m.Y') }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="booking-table__cell booking-table__cell--money">
                                                    <div class="booking-table__money-row">
                                                        <div class="booking-table__money-line">
                                                            <span class="booking-table__money-label">Total</span>
                                                            <span class="booking-table__money-value">{{ two($booking->price) }} €</span>
                                                        </div>
                                                        <div class="booking-table__money-line">
                                                            <span class="booking-table__money-label">Guide</span>
                                                            <span class="booking-table__money-value">{{ two($booking->price - $booking->cag_percent) }} €</span>
                                                        </div>
                                                        <div class="booking-table__money-line">
                                                            <span class="booking-table__money-label">CaG</span>
                                                            <span class="booking-table__money-value">{{ two($booking->cag_percent) }} €</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="booking-table__cell booking-table__cell--status">
                                                    @php
                                                        $status = strtolower($booking->status);
                                                        $statusClass = match ($status) {
                                                            'accepted' => 'booking-table__status-pill--accepted',
                                                            'rejected', 'cancelled' => 'booking-table__status-pill--danger',
                                                            default => 'booking-table__status-pill--pending',
                                                        };
                                                    @endphp
                                                    <span class="booking-table__status-pill {{ $statusClass }}">
                                                        <i class="fa
                                                            @switch($status)
                                                                @case('accepted') fa-check-circle @break
                                                                @case('rejected') fa-times-circle @break
                                                                @case('cancelled') fa-times-circle @break
                                                                @default fa-hourglass-half
                                                            @endswitch
                                                        me-1"></i>
                                                        {{ strtoupper($booking->status) }}
                                                    </span>
                                                    <div class="booking-table__status-meta">
                                                        @if($booking->last_employee_id)
                                                            <span>by {{ $booking->employee->name }}</span>
                                                        @endif
                                                        @if($booking->status === 'accepted' && $booking->isBookingOver())
                                                            @if($booking->is_guide_billed)
                                                                <span>Billed to guide</span>
                                                            @else
                                                                <span>To be billed</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="booking-table__cell booking-table__cell--guide-tour">
                                                    <div class="booking-table__guide-tour">
                                                        <div class="booking-table__guide-tour-line">
                                                            <span class="booking-table__guide-tour-label">
                                                                <i class="fa fa-user-tie me-1"></i>
                                                            </span>
                                                            <span class="booking-table__guide-tour-value">
                                                                @if($booking->guiding && $booking->guiding->user)
                                                                    <a href="{{route('admin.guides.edit', $booking->guiding->user->id)}}">
                                                                        {{ $booking->guiding->user->full_name }}
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="booking-table__guide-tour-line">
                                                            <span class="booking-table__guide-tour-label booking-table__guide-tour-label--muted">
                                                                <i class="fa fa-map-marked-alt me-1"></i>
                                                            </span>
                                                            <span class="booking-table__guide-tour-value">
                                                                @if($booking->guiding)
                                                                    <a href="{{route('admin.guidings.edit', $booking->guiding->id)}}">
                                                                        {{$booking->guiding->title}}
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="booking-table__guide-tour-line">
                                                            <span class="booking-table__guide-tour-label booking-table__guide-tour-label--muted">
                                                                <i class="fa fa-map-pin me-1"></i>
                                                            </span>
                                                            <span class="booking-table__guide-tour-value booking-table__guide-tour-value--location">
                                                                @if($booking->guiding && $booking->guiding->location)
                                                                    {{ $booking->guiding->location }}
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="col-action">
                                                    <div class="booking-table__actions">
                                                        {{-- Primary inline actions --}}
                                                        <div class="booking-table__actions-inline">
                                                            @if($booking->status == 'pending')
                                                                <a href="{{ route('booking.accept', $booking->token) }}" class="btn btn-success btn-compact-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Accept booking">
                                                                    <i class="fa fa-check"></i>
                                                                </a>
                                                                <a href="{{ route('booking.reject', $booking->token) }}" class="btn btn-danger btn-compact-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Cancel / reject booking">
                                                                    <i class="fa fa-times-circle"></i>
                                                                </a>
                                                            @endif
                                                            <a href="javascript:void(0)" class="btn btn-secondary btn-compact-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit booking" onclick="showEditBookingModal({{ $booking->id }})">
                                                                <i class="fa fa-pen"></i>
                                                            </a>
                                                            <a href="javascript:deleteResource('{{ route('admin.bookings.destroy', $booking, false) }}')" class="btn btn-danger btn-compact-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete booking">
                                                                <i class="fa fa-trash"></i>
                                                            </a>
                                                        </div>

                                                        {{-- More actions in a compact bubble menu --}}
                                                        <div class="dropdown booking-table__more">
                                                            <button class="btn btn-light btn-compact-icon booking-table__more-toggle" type="button" id="booking-more-{{ $booking->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="fa fa-ellipsis-h"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end booking-table__more-menu" aria-labelledby="booking-more-{{ $booking->id }}">
                                                                <li>
                                                                    <button class="dropdown-item" type="button" onclick="showBookingNotesModal({{ $booking->id }})">
                                                                        <i class="fa fa-sticky-note me-2"></i> Notes
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item" type="button" onclick="showEmailPreview({{ $booking->id }})">
                                                                        <i class="fa fa-envelope me-2"></i> Preview emails
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item" type="button" onclick="showResendModal(
                                                                        {{ $booking->id }},
                                                                        {{ json_encode($booking->user ? ($booking->user->firstname . ' ' . $booking->user->lastname) : ($booking->firstname . ' ' . $booking->lastname)) }},
                                                                        {{ json_encode($booking->email ?: ($booking->user ? $booking->user->email : '')) }},
                                                                        {{ json_encode($booking->guiding && $booking->guiding->user ? ($booking->guiding->user->firstname . ' ' . $booking->guiding->user->lastname) : 'N/A') }},
                                                                        {{ json_encode($booking->guiding && $booking->guiding->user ? $booking->guiding->user->email : '') }}
                                                                    )">
                                                                        <i class="fa fa-paper-plane me-2"></i> Resend booking emails
                                                                    </button>
                                                                </li>
                                                                @if($canSendGuideInvoice)
                                                                    <li><hr class="dropdown-divider"></li>
                                                                    <li>
                                                                        <button class="dropdown-item" type="button" onclick="showInvoiceConfirmModal(
                                                                            {{ $booking->id }},
                                                                            {{ json_encode($booking->guiding->user->full_name ?? 'N/A') }},
                                                                            {{ json_encode($booking->guiding->user->email ?? 'N/A') }},
                                                                            {{ json_encode($bookingDateTime ?: 'N/A') }},
                                                                            {{ json_encode(two($booking->price) . ' €') }},
                                                                            {{ json_encode(two($booking->price - $booking->cag_percent) . ' €') }},
                                                                            {{ json_encode(two($booking->cag_percent) . ' €') }}
                                                                        )">
                                                                            <i class="fa fa-receipt me-2"></i> Send guide invoice
                                                                        </button>
                                                                    </li>
                                                                    <li>
                                                                        @if($booking->is_guide_billed)
                                                                            <button class="dropdown-item" type="button" onclick="updateGuideBillingStatus({{ $booking->id }}, false)">
                                                                                <i class="fa fa-undo me-2"></i> Unmark as billed
                                                                            </button>
                                                                        @else
                                                                            <button class="dropdown-item" type="button" onclick="updateGuideBillingStatus({{ $booking->id }}, true)">
                                                                                <i class="fa fa-check-circle me-2"></i> Mark as billed
                                                                            </button>
                                                                        @endif
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>
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

    <!-- Send Guide Invoice Confirmation Modal -->
    <div class="modal fade" id="sendGuideInvoiceModal" tabindex="-1" role="dialog" aria-labelledby="sendGuideInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendGuideInvoiceModalLabel">
                        <i class="fa fa-envelope"></i> Send Guide Invoice
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-lg-5">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>Confirmation Required</strong>
                                <p class="mb-0">You are about to send an invoice email to the guide for this completed booking.</p>
                            </div>

                            <div class="card border-primary mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fa fa-receipt"></i> Booking Summary</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Booking ID:</strong> <span id="invoice-booking-id">-</span></p>
                                    <p class="mb-1"><strong>Guide:</strong> <span id="invoice-guide-name">-</span></p>
                                    <p class="mb-1"><strong>Guide Email:</strong> <span id="invoice-guide-email">-</span></p>
                                    <p class="mb-1"><strong>Tour Date/Time:</strong> <span id="invoice-tour-datetime">-</span></p>
                                    <hr>
                                    <p class="mb-1"><strong>Total Price:</strong> <span id="invoice-total-price">-</span></p>
                                    <p class="mb-1"><strong>Guide Share:</strong> <span id="invoice-guide-share">-</span></p>
                                    <p class="mb-0"><strong>CaG Share:</strong> <span id="invoice-cag-share">-</span></p>
                                </div>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="confirmGuideInvoiceSend" required>
                                <label class="form-check-label" for="confirmGuideInvoiceSend">
                                    I confirm that I want to send this invoice email
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-lg-7">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <iframe id="guide-invoice-preview-iframe" style="width: 100%; height: 520px; border: 1px solid #dee2e6; border-radius: 6px;"></iframe>
                                    <div id="guide-invoice-preview-loading" class="text-muted mt-2">
                                        Loading invoice email preview...
                                    </div>
                                    <div id="guide-invoice-preview-not-available" class="alert alert-warning mt-2 d-none mb-0">
                                        Invoice email preview is not available.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmGuideInvoiceSendBtn" onclick="confirmGuideInvoiceSend()" disabled>
                        <i class="fa fa-envelope"></i> Send Invoice Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Booking Modal -->
    <div class="modal fade" id="manualBookingModal" tabindex="-1" aria-labelledby="manualBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manualBookingModalLabel">Create Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.bookings.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="card border-0 shadow-sm mb-4 position-relative">
                                    <div class="card-header border-0">
                                        <h5 class="mb-0">Guiding / Trip</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 position-relative">
                                            <input type="text"
                                                   id="guiding-search-input"
                                                   class="form-control"
                                                   placeholder="Select guiding…"
                                                   autocomplete="off">
                                            <input type="hidden" name="guiding_id" id="guiding-id-input">

                                            <div id="guiding-dropdown"
                                                 class="card position-absolute w-100 mt-1 d-none"
                                                 style="z-index: 1055; max-height: 260px; overflow-y: auto;">
                                                <div id="guiding-dropdown-list"></div>

                                                <div id="guiding-dropdown-loading" class="text-center py-2 small text-muted d-none">
                                                    Loading guidings…
                                                </div>

                                                <div id="guiding-dropdown-empty" class="text-center py-2 small text-muted d-none">
                                                    No guidings found.
                                                </div>
                                            </div>

                                            <div class="text-danger small mt-1 d-none" id="guiding-error">
                                                Please select a guiding.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-header border-0">
                                        <h5 class="mb-0">Booking details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Date</label>
                                                <input type="date"
                                                       name="date"
                                                       class="form-control"
                                                       min="{{ now()->toDateString() }}"
                                                       required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Number of guests</label>
                                                <input type="number" name="number_of_guests" class="form-control" min="1" value="1" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Price override (€)</label>
                                                <input type="number" step="0.01" name="price_override" class="form-control" placeholder="Auto if empty">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Initial status</label>
                                                <select name="status" class="form-control">
                                                    <option value="">Pending (default)</option>
                                                    <option value="pending">Pending</option>
                                                    <option value="accepted">Accepted</option>
                                                    <option value="rejected">Rejected</option>
                                                    <option value="cancelled">Cancelled</option>
                                                </select>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-label">Internal notes / message</label>
                                                <textarea name="notes" rows="2" class="form-control"></textarea>
                                            </div>
                                        </div>

                                        <div class="form-check mt-2">
                                            <input type="hidden" name="send_emails" value="0">
                                            <input class="form-check-input" type="checkbox" id="manual-send-emails" name="send_emails" value="1" checked>
                                            <label class="form-check-label" for="manual-send-emails">
                                                Send booking request emails to guest and guide
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header border-0">
                                        <h5 class="mb-0">Guest details</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Guest name</label>
                                            <input type="text" name="guest_name" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Guest email</label>
                                            <input type="email" name="guest_email" class="form-control">
                                            <small class="text-muted">Required if you want to send emails.</small>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Country code</label>
                                                <input type="text" name="guest_phone_country_code" class="form-control" value="+49">
                                            </div>
                                            <div class="col-md-8 mb-3">
                                                <label class="form-label">Phone</label>
                                                <input type="text" name="guest_phone" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm">
                                    <div class="card-header border-0">
                                        <h5 class="mb-0">How this works</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <strong>Same flow as frontend:</strong>
                                                Uses the shared booking service to create a pending booking, blocked event, and calendar entry.
                                            </li>
                                            <li class="mb-2">
                                                <strong>Email simulation:</strong>
                                                When email sending is enabled, guest/guide/CEO emails are dispatched just like a normal checkout.
                                            </li>
                                            <li class="mb-0">
                                                <strong>Audit:</strong>
                                                Booking is marked as created by the current admin user with source <code>admin</code>.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" onclick="return validateManualBookingForm()">
                            Create booking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Ensure guiding cards render correctly inside the admin dropdown */
        #guiding-dropdown {
            background: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
        }

        #guiding-dropdown .guiding-card {
            border-radius: 0;
            border-left: none;
            border-right: none;
            box-shadow: none;
            overflow: visible;
            contain: initial;
            cursor: pointer;
            transition: background-color 120ms ease, transform 120ms ease;
        }

        /* Subtle row differentiation (striped) */
        #guiding-dropdown #guiding-dropdown-list .guiding-card:nth-child(odd) {
            background: #ffffff;
        }

        #guiding-dropdown #guiding-dropdown-list .guiding-card:nth-child(even) {
            background: #f8fafc;
        }

        #guiding-dropdown #guiding-dropdown-list .guiding-card:hover {
            background: #eef2ff;
        }

        #guiding-dropdown .guiding-card:first-child {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }

        #guiding-dropdown .guiding-card:last-child {
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            border-bottom: none;
        }

        #guiding-dropdown .guiding-card .card-body {
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
        }

        /* Thumbnail polish */
        #guiding-dropdown img[alt="Guiding thumbnail"] {
            border-radius: 10px !important;
            border: 1px solid rgba(15, 23, 42, 0.10);
            background: #f1f5f9;
        }

        /* Make checkbox checked state obvious in modal */
        #manualBookingModal .form-check-input {
            width: 1.15rem;
            height: 1.15rem;
            margin-top: 0.2rem;
            border: 1px solid rgba(15, 23, 42, 0.25);
            background-color: #ffffff;
            accent-color: #4f46e5; /* modern browsers */
        }

        #manualBookingModal .form-check-input:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }

        #manualBookingModal .form-check-input:focus {
            border-color: rgba(79, 70, 229, 0.6);
            box-shadow: 0 0 0 0.15rem rgba(79, 70, 229, 0.18);
        }
    </style>

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

    <!-- Booking Notes Modal -->
    <div class="modal fade" id="bookingNotesModal" tabindex="-1" aria-labelledby="bookingNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingNotesModalLabel">Booking notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="booking-notes-id" value="">
                    <div class="mb-2 text-muted small">
                        Internal only. Use this to track contact attempts, open TODOs, etc.
                    </div>
                    <textarea id="booking-notes-text" class="form-control" rows="6" placeholder="e.g. 2026-04-16: Called customer, waiting for reply. TODO: confirm arrival time."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveBookingNotes()">
                        Save notes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function validateManualBookingForm() {
            const guidingId = document.getElementById('guiding-id-input').value;
            const errorEl = document.getElementById('guiding-error');
            if (!guidingId) {
                if (errorEl) {
                    errorEl.classList.remove('d-none');
                }
                return false;
            }
            if (errorEl) {
                errorEl.classList.add('d-none');
            }
            return true;
        }

        let currentBookingId = null;
        let currentInvoiceBookingId = null;

        document.addEventListener('DOMContentLoaded', function () {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Guiding dropdown search + selection
        (function () {
            const searchInput = document.getElementById('guiding-search-input');
            const guidingIdInput = document.getElementById('guiding-id-input');
            const dropdown = document.getElementById('guiding-dropdown');
            const listEl = document.getElementById('guiding-dropdown-list');
            const loadingEl = document.getElementById('guiding-dropdown-loading');
            const emptyEl = document.getElementById('guiding-dropdown-empty');
            if (!searchInput || !dropdown || !listEl) return;

            const apiUrl = @json(route('admin.bookings.guidings-search'));

            let currentTerm = '';
            let currentPage = 1;
            let nextPage = null;
            let isLoading = false;
            let debounceTimer = null;

            function openDropdown() {
                dropdown.classList.remove('d-none');
            }

            function closeDropdown() {
                dropdown.classList.add('d-none');
            }

            function setLoading(state) {
                isLoading = state;
                if (loadingEl) loadingEl.classList.toggle('d-none', !state);
            }

            function clearList() {
                listEl.innerHTML = '';
                if (emptyEl) emptyEl.classList.add('d-none');
            }

            function renderGuidingCard(item) {
                const div = document.createElement('div');
                div.className = 'guiding-card border-0 border-bottom';
                div.dataset.guidingId = item.id;
                div.dataset.label = item.title;
                const placeholderUrl = @json(asset('images/placeholder_guide.jpg'));
                div.innerHTML = `
                    <div class="card-body py-2 px-2 d-flex align-items-center">
                        <div class="me-3">
                            <img src="${item.thumbnail_url}"
                                 alt="Guiding thumbnail"
                                 class="rounded"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='${placeholderUrl}';"
                                 style="width: 40px; height: 40px; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">${item.title}</div>
                            <div class="text-muted small">
                                ID #${item.id}${item.location ? ' · ' + item.location : ''}
                            </div>
                            ${item.guide_name ? `<div class="text-muted small">Guide: ${item.guide_name}</div>` : ''}
                        </div>
                    </div>
                `;

                div.addEventListener('click', function () {
                    const id = this.dataset.guidingId;
                    const label = this.dataset.label || ('ID #' + id);
                    guidingIdInput.value = id;
                    searchInput.value = label;

                    const errorEl = document.getElementById('guiding-error');
                    if (errorEl) errorEl.classList.add('d-none');

                    closeDropdown();
                });

                return div;
            }

            function loadGuidings({ reset = false } = {}) {
                if (isLoading) return;

                if (reset) {
                    currentPage = 1;
                    nextPage = null;
                    clearList();
                }

                setLoading(true);

                const params = new URLSearchParams();
                params.set('page', String(currentPage));
                params.set('per_page', '20');
                if (currentTerm) params.set('q', currentTerm);

                fetch(apiUrl + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(json => {
                        const data = Array.isArray(json.data) ? json.data : [];

                        if (reset && data.length === 0) {
                            if (emptyEl) emptyEl.classList.remove('d-none');
                        } else {
                            if (emptyEl) emptyEl.classList.add('d-none');
                        }

                        data.forEach(item => listEl.appendChild(renderGuidingCard(item)));

                        nextPage = json.next_page || null;
                        currentPage = json.current_page || currentPage;
                    })
                    .catch(() => {
                        if (reset && emptyEl) {
                            emptyEl.textContent = 'Failed to load guidings.';
                            emptyEl.classList.remove('d-none');
                        }
                    })
                    .finally(() => setLoading(false));
            }

            function ensureInitialLoaded() {
                if (!listEl.childElementCount && !isLoading) {
                    loadGuidings({ reset: true });
                }
            }

            // Open on focus/click
            searchInput.addEventListener('focus', function () {
                openDropdown();
                ensureInitialLoaded();
            });
            searchInput.addEventListener('click', function () {
                openDropdown();
                ensureInitialLoaded();
            });

            // Debounced filter as user types
            searchInput.addEventListener('input', function () {
                currentTerm = this.value.toLowerCase().trim();
                if (debounceTimer) window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(() => loadGuidings({ reset: true }), 250);
            });

            // Infinite scroll
            dropdown.addEventListener('scroll', function () {
                if (!nextPage || isLoading) return;
                const threshold = 40;
                if (dropdown.scrollTop + dropdown.clientHeight + threshold >= dropdown.scrollHeight) {
                    currentPage = nextPage;
                    loadGuidings();
                }
            });

            // Close when clicking outside
            document.addEventListener('click', function (e) {
                if (!dropdown.contains(e.target) && e.target !== searchInput) {
                    closeDropdown();
                }
            });
        })();

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

        document.getElementById('confirmGuideInvoiceSend').addEventListener('change', function() {
            document.getElementById('confirmGuideInvoiceSendBtn').disabled = !this.checked;
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

        // Auto-save status changes from the dropdown and refresh the table
        let editBookingModalIsHydrating = false;
        document.addEventListener('DOMContentLoaded', () => {
            const statusEl = document.getElementById('edit-booking-status');
            if (!statusEl) return;

            statusEl.addEventListener('change', () => {
                if (editBookingModalIsHydrating) return;
                const statusGroup = document.getElementById('edit-booking-status-group');
                if (statusGroup && statusGroup.style.display === 'none') return;
                saveBookingEdit();
            });
        });

        function adminBookingSaveUrl(bookingId) {
            return `{{ url('/admin/bookings') }}/${bookingId}/save`;
        }

        function showBookingNotesModal(bookingId) {
            fetch(`/admin/bookings/${bookingId}/edit`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('booking-notes-id').value = data.id;
                    document.getElementById('booking-notes-text').value = data.admin_comment || '';
                    var modal = new bootstrap.Modal(document.getElementById('bookingNotesModal'));
                    modal.show();
                })
                .catch(() => alert('Failed to load booking notes.'));
        }

        function saveBookingNotes() {
            const bookingId = document.getElementById('booking-notes-id').value;
            const adminComment = document.getElementById('booking-notes-text').value;
            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement) {
                alert('CSRF token not found.');
                return;
            }
            const csrfToken = csrfTokenElement.getAttribute('content');
            fetch(adminBookingSaveUrl(bookingId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ admin_comment: adminComment })
            })
            .then(response => {
                if (!response.ok) return response.text().then(t => { throw new Error(t || response.status); });
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    var el = document.getElementById('bookingNotesModal');
                    var instance = bootstrap.Modal.getInstance(el);
                    if (instance) instance.hide();
                    location.reload();
                } else {
                    alert('Failed to save notes: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(() => alert('Error saving notes.'));
        }

        function showEditBookingModal(bookingId) {
            fetch(`/admin/bookings/${bookingId}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit-booking-id').value = data.id;
                    document.getElementById('edit-booking-email').value = data.email || '';
                    document.getElementById('edit-booking-phone').value = data.phone || '';
                    editBookingModalIsHydrating = true;
                    document.getElementById('edit-booking-status').value = data.status;
                    if (data.allowed_status_edit) {
                        document.getElementById('edit-booking-status-group').style.display = '';
                    } else {
                        document.getElementById('edit-booking-status-group').style.display = 'none';
                    }
                    var modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
                    modal.show();
                    setTimeout(() => { editBookingModalIsHydrating = false; }, 0);
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
            fetch(adminBookingSaveUrl(bookingId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                if (!response.ok) return response.text().then(t => { throw new Error(t || response.status); });
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Booking updated successfully.');
                    location.reload();
                } else {
                    alert('Failed to update booking: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error updating booking.');
            });
        }

        function showInvoiceConfirmModal(bookingId, guideName, guideEmail, tourDateTime, totalPrice, guideShare, cagShare) {
            currentInvoiceBookingId = bookingId;

            document.getElementById('invoice-booking-id').textContent = bookingId;
            document.getElementById('invoice-guide-name').textContent = guideName || 'N/A';
            document.getElementById('invoice-guide-email').textContent = guideEmail || 'N/A';
            document.getElementById('invoice-tour-datetime').textContent = tourDateTime || 'N/A';
            document.getElementById('invoice-total-price').textContent = totalPrice || 'N/A';
            document.getElementById('invoice-guide-share').textContent = guideShare || 'N/A';
            document.getElementById('invoice-cag-share').textContent = cagShare || 'N/A';

            document.getElementById('confirmGuideInvoiceSend').checked = false;
            document.getElementById('confirmGuideInvoiceSendBtn').disabled = true;

            const previewIframe = document.getElementById('guide-invoice-preview-iframe');
            const previewLoading = document.getElementById('guide-invoice-preview-loading');
            const previewNotAvailable = document.getElementById('guide-invoice-preview-not-available');

            previewIframe.srcdoc = '';
            previewLoading.classList.remove('d-none');
            previewNotAvailable.classList.add('d-none');

            fetch(`/admin/bookings/${bookingId}/guide-invoice-preview`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html) {
                        previewIframe.srcdoc = data.html;
                        previewNotAvailable.classList.add('d-none');
                    } else {
                        previewIframe.srcdoc = '';
                        previewNotAvailable.classList.remove('d-none');
                    }
                })
                .catch(() => {
                    previewIframe.srcdoc = '';
                    previewNotAvailable.classList.remove('d-none');
                })
                .finally(() => {
                    previewLoading.classList.add('d-none');
                });

            const modal = new bootstrap.Modal(document.getElementById('sendGuideInvoiceModal'));
            modal.show();
        }

        function confirmGuideInvoiceSend() {
            if (!currentInvoiceBookingId) {
                alert('Invalid booking selected.');
                return;
            }

            if (!document.getElementById('confirmGuideInvoiceSend').checked) {
                alert('Please confirm by checking the checkbox.');
                return;
            }

            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement) {
                alert('CSRF token not found.');
                return;
            }

            const confirmBtn = document.getElementById('confirmGuideInvoiceSendBtn');
            const originalText = confirmBtn.innerHTML;
            confirmBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...';
            confirmBtn.disabled = true;

            fetch(`/admin/bookings/${currentInvoiceBookingId}/send-guide-invoice`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    bootstrap.Modal.getInstance(document.getElementById('sendGuideInvoiceModal')).hide();
                    location.reload();
                } else {
                    alert('❌ ' + (data.message || 'Failed to send invoice email.'));
                }
            })
            .catch(error => {
                console.error('Error sending guide invoice:', error);
                alert('❌ Failed to send invoice email.');
            })
            .finally(() => {
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
            });
        }

        function updateGuideBillingStatus(bookingId, isGuideBilled) {
            const actionText = isGuideBilled ? 'mark this booking as billed' : 'unmark this booking as billed';
            if (!confirm(`Are you sure you want to ${actionText}?`)) {
                return;
            }

            const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
            if (!csrfTokenElement) {
                alert('CSRF token not found.');
                return;
            }

            fetch(`/admin/bookings/${bookingId}/update-guide-billing-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfTokenElement.getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    is_guide_billed: isGuideBilled ? 1 : 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ ' + (data.message || 'Failed to update billing status.'));
                }
            })
            .catch(error => {
                console.error('Error updating guide billing status:', error);
                alert('❌ Failed to update billing status.');
            });
        }
    </script>
@endsection
