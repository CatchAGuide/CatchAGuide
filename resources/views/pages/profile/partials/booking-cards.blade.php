@if($bookings && $bookings->count() > 0)
    <!-- My Bookings -->
    @foreach($bookings as $index => $booking)
        <div class="booking-card my-booking" data-type="my-booking" data-status="{{ $booking->status }}" 
             data-completed="{{ 
                $booking->status == 'accepted' && 
                (
                    ($booking->calendar_schedule && \Carbon\Carbon::parse($booking->calendar_schedule->date)->isPast()) ||
                    ($booking->blocked_event && \Carbon\Carbon::parse($booking->blocked_event->from)->isPast())
                ) ? 'true' : 'false' 
             }}"
             data-search="{{ strtolower($booking->guiding->title ?? 'untitled') }} {{ strtolower($booking->guiding->location ?? '') }}">
            <div class="booking-header">
                <div style="flex: 1;">
                    <div class="booking-type my-booking">
                        <i class="fas fa-user"></i>
                        My Booking
                    </div>
                    <div class="booking-id-tag">
                        <i class="fas fa-hashtag"></i>
                        <strong>ID: {{ $booking->id }}</strong>
                    </div>
                    <h3 class="booking-title">{{ $booking->guiding->title ?? 'Untitled Guiding' }}</h3>
                    <p class="booking-subtitle">📍 {{ $booking->guiding->location ?? 'Location not specified' }}</p>
                </div>
                <div class="booking-status">
                    <span class="status-badge status-{{ $booking->status }}">
                        {{ ucfirst(translate($booking->status)) }}
                    </span>
                </div>
            </div>
            
            <div class="booking-content">
                <div class="booking-details">
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Requested at</h6>
                            <p>{{ $booking->created_at->format('D, M j, Y') }}</p>
                        </div>
                    </div>

                    @if($booking->status == 'cancelled')
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Cancelled Date</h6>
                                <p>{{ $booking->updated_at->format('D, M j, Y') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Trip Date</h6>
                            <p>
                                @if($booking->calendar_schedule)
                                    {{ \Carbon\Carbon::parse($booking->calendar_schedule->date)->format('D, M j, Y') }}
                                @elseif($booking->blocked_event)
                                    {{ \Carbon\Carbon::parse($booking->blocked_event->from)->format('D, M j, Y') }}
                                @else
                                    <span class="text-danger">Cancelled</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Price</h6>
                            <p>{{ two($booking->price) }} €</p>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Guide</h6>
                            <p>{{ $booking->guiding->user->full_name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Guests</h6>
                            <p>{{ $booking->count_of_users ?? 1 }} Person(s)</p>
                        </div>
                    </div>
                </div>
                
                <div class="booking-actions">
                    <!-- View Details button for all statuses -->
                    <button class="btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $index }}">
                        <i class="fas fa-eye"></i> View Details
                    </button>

                    @if($booking->status == 'accepted')
                        @if($booking->isBookingOver() && !auth()->user()->hasratet($booking->user_id) && !$booking->is_reviewed)
                            <a href="{{ route('ratings.show', ['token' => $booking->token]) }}" class="btn-action btn-warning" target="_blank">
                                <i class="fas fa-star"></i> Rate Guide
                            </a>
                        @endif
                        
                        <button id="contact-product" id="contact-product" class="btn-action btn-info" data-bs-toggle="modal" data-bs-target="#contactModal{{ $index }}">
                            <i class="fas fa-envelope"></i> Contact Guide
                        </button>
                    @elseif($booking->status == 'pending')
                        <span class="btn-action btn-secondary">
                            <i class="fas fa-clock"></i> Waiting for Response
                        </span>
                    @elseif(in_array($booking->status, ['cancelled', 'rejected', 'storniert']))
                        <button class="btn-action btn-danger" data-bs-toggle="modal" data-bs-target="#rejectionModal{{ $index }}">
                            <i class="fas fa-exclamation-triangle"></i> {{ $booking->status == 'cancelled' ? 'Cancellation' : 'Rejection' }} Details
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endif

@if(auth()->user()->is_guide && $guideBookings && method_exists($guideBookings, 'count') && $guideBookings->count() > 0)
    @foreach($guideBookings as $gIndex => $booking)
        <div class="booking-card guide-booking" data-type="guide-booking" data-status="{{ $booking->status }}" 
             data-completed="{{ 
                $booking->status == 'accepted' && 
                (
                    ($booking->calendar_schedule && \Carbon\Carbon::parse($booking->calendar_schedule->date)->isPast()) ||
                    ($booking->blocked_event && \Carbon\Carbon::parse($booking->blocked_event->from)->isPast())
                ) ? 'true' : 'false' 
             }}"
             data-search="{{ strtolower($booking->guiding->title ?? 'untitled') }} {{ strtolower($booking->guiding->location ?? '') }}">
            <div class="booking-header">
                <div style="flex: 1;">
                    <div class="booking-type guide-booking">
                        <i class="fas fa-fish"></i>
                        Guide Booking
                    </div>
                    <div class="booking-id-tag">
                        <i class="fas fa-hashtag"></i>
                        <strong>ID: {{ $booking->id }}</strong>
                    </div>
                    <h3 class="booking-title">{{ $booking->guiding->title ?? 'Untitled Guiding' }}</h3>
                    <p class="booking-subtitle">📍 {{ $booking->guiding->location ?? 'Location not specified' }}</p>
                </div>
                <div class="booking-status">
                    <span class="status-badge status-{{ $booking->status }}">
                        {{ ucfirst(translate($booking->status)) }}
                    </span>
                </div>
            </div>
            
            <div class="booking-content">
                <div class="booking-details">
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Booked Date</h6>
                            <p>{{ $booking->created_at->format('D, M j, Y') }}</p>
                        </div>
                    </div>

                    @if($booking->status == 'cancelled')
                        <div class="detail-item">
                            <div class="detail-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="detail-content">
                                <h6>Cancelled Date</h6>
                                <p>{{ $booking->updated_at->format('D, M j, Y') }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Trip Date</h6>
                            <p>
                                @if($booking->calendar_schedule)
                                    {{ \Carbon\Carbon::parse($booking->calendar_schedule->date)->format('D, M j, Y') }}
                                @elseif($booking->blocked_event)
                                    {{ \Carbon\Carbon::parse($booking->blocked_event->from)->format('D, M j, Y') }}
                                @else
                                    <span class="text-danger">Not Available</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-euro-sign"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Price</h6>
                            <p>{{ $booking->price }} €</p>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Customer</h6>
                            <p>{{ $booking->user->full_name ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="detail-content">
                            <h6>Guests</h6>
                            <p>{{ $booking->count_of_users ?? 1 }} Person(s)</p>
                        </div>
                    </div>
                </div>
                
                <div class="booking-actions">
                    <!-- View Details button for all statuses -->
                    <button class="btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#guideDetailsModal{{ $gIndex }}">
                        <i class="fas fa-eye"></i> View Details
                    </button>

                    @if($booking->status == 'pending')
                        <a href="{{ route('profile.guidebookings.accept', $booking) }}" class="btn-action btn-success">
                            <i class="fas fa-check"></i> Accept
                        </a>
                        <a href="{{ route('profile.guidebookings.reject', $booking) }}" class="btn-action btn-danger">
                            <i class="fas fa-times"></i> Reject
                        </a>
                    @elseif($booking->status == 'accepted')
                        <button class="btn-action btn-info" data-bs-toggle="modal" data-bs-target="#guideContactModal{{ $gIndex }}">
                            <i class="fas fa-envelope"></i> Contact Customer
                        </button>
                    @elseif(in_array($booking->status, ['cancelled', 'rejected', 'storniert']))
                        <button class="btn-action btn-danger" data-bs-toggle="modal" data-bs-target="#guideRejectionModal{{ $gIndex }}">
                            <i class="fas fa-exclamation-triangle"></i> {{ $booking->status == 'cancelled' ? 'Cancellation' : 'Rejection' }} Details
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endif 