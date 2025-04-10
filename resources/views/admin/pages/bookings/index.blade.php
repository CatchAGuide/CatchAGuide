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
        <div class="modal-dialog " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="emailPreviewModalLabel">Email Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="emailTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="booking-request-tab" data-bs-toggle="tab" data-bs-target="#booking-request" type="button" role="tab" aria-controls="booking-request" aria-selected="true">Booking Request Email</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="expired-booking-tab" data-bs-toggle="tab" data-bs-target="#expired-booking" type="button" role="tab" aria-controls="expired-booking" aria-selected="false">Expired Booking Email</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-3" id="emailTabsContent">
                        <div class="tab-pane fade show active" id="guest-booking-request" role="tabpanel" aria-labelledby="booking-request-tab">
                            <div class="email-preview-container" style="height: 600px; overflow-y: auto;">
                                <iframe id="booking-request-iframe" style="width: 100%; height: 100%; border: 1px solid #ddd;"></iframe>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="guest-expired-booking" role="tabpanel" aria-labelledby="expired-booking-tab">
                            <div class="email-preview-container" style="height: 600px; overflow-y: auto;">
                                <iframe id="expired-booking-iframe" style="width: 100%; height: 100%; border: 1px solid #ddd;"></iframe>
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
            // Load the email templates via AJAX
            fetch(`/admin/bookings/${bookingId}/email-preview`)
                .then(response => response.json())
                .then(data => {
                    // Set the iframe sources with the HTML content
                    document.getElementById('guest-booking-request-iframe').srcdoc = data.bookingRequestEmail;
                    document.getElementById('guest-expired-booking-iframe').srcdoc = data.expiredBookingEmail;
                    
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
