@extends('pages.profile.layouts.profile')
@section('title', __('profile.profile'))
@section('profile-content')

    <!-- Dashboard Header -->
    <div class="bookings-header">
        <h1 class="mb-0 text-white">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard Overview
        </h1>
        <p class="mb-0 mt-2 text-white">Manage your fishing experiences and bookings</p>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row stats-cards">
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('profile.bookings') }}" class="stat-card">
                <div class="stat-icon bookings">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <h3>{{ Auth::user()->bookings()->count() }}</h3>
                    <p>Total Bookings</p>
                </div>
            </a>
        </div>

        @if (Auth::user()->is_guide)
            <div class="col-lg-4 col-md-6 mb-4">
                <a href="{{ route('profile.myguidings') }}" class="stat-card">
                    <div class="stat-icon guidings">
                        <i class="fas fa-fish"></i>
                    </div>
                    <div class="stat-content">
                        @php
                            $guidingsCount = 0;
                            try {
                                $guidingsCount = Auth::user()->guidings()->count();
                            } catch (Exception $e) {
                                $guidingsCount = 0;
                            }
                        @endphp
                        <h3>{{ $guidingsCount }}</h3>
                        <p>Active Guidings</p>
                    </div>
                </a>
            </div>

            {{-- <div class="col-lg-4 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon earnings">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-content">
                        @php
                            // Get bookings where the user is the guide
                            $guideBookings = \App\Models\Booking::whereHas('guiding', function ($query) {
                                $query->where('user_id', Auth::id());
                            })->where('status', 'accepted');
                            $totalEarnings = $guideBookings->sum('price') ?? 0;
                        @endphp
                        <h3>€{{ number_format($totalEarnings, 0) }}</h3>
                        <p>Total Earnings</p>
                    </div>
                </div>
            </div> --}}
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-section">
        <h4>Quick Actions</h4>
        <div class="row">
            @if (Auth::user()->is_guide)
                <div class="col-lg-6 col-md-12 mb-3">
                    <div class="action-card featured">
                        <div class="action-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="action-content">
                            <h5>Create New Guiding</h5>
                            <p>Add a new fishing experience for anglers to book</p>
                            <a href="{{ route('profile.newguiding') }}" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 mb-3">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="action-content">
                            <h5>Manage Calendar</h5>
                            <p>Update your availability and blocked dates</p>
                            <a href="{{ route('profile.calendar') }}" class="btn btn-outline-primary">Open Calendar</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-lg-6 col-md-12 mb-3">
                    <div class="action-card featured">
                        <div class="action-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="action-content">
                            <h5>Find Fishing Guides</h5>
                            <p>Discover amazing fishing experiences</p>
                            <a href="{{ route('welcome') }}" class="btn btn-primary">Browse Guidings</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 mb-3">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="action-content">
                            <h5>Become a Guide</h5>
                            <p>Share your fishing expertise with others</p>
                            <a href="{{ route('profile.becomeguide') }}" class="btn btn-outline-primary">Learn More</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Upcoming Bookings -->
    <div class="upcoming-bookings-section mb-5">
        <h4 class="profile-section-title">
            <i class="fas fa-calendar-day"></i>
            Upcoming Bookings
        </h4>
        <div class="activity-list">
            @php
                $upcomingBookings = Auth::user()->bookings()
                    ->with(['guiding', 'calendar_schedule'])
                    ->join('calendar_schedule', 'bookings.blocked_event_id', '=', 'calendar_schedule.id')
                    ->whereIn('bookings.status', ['accepted', 'pending'])
                    ->where('calendar_schedule.date', '>=', now()->format('Y-m-d'))
                    ->orderBy('calendar_schedule.date', 'asc')
                    ->select('bookings.*')
                    ->take(3)
                    ->get();
            @endphp
            @forelse($upcomingBookings as $index => $booking)
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="activity-content">
                        <h6 class="activity-item-title d-flex align-items-center">
                            <span>{{ $booking->guiding->title ?? 'Booking' }}</span>
                            <span class="booking-id-tag ms-2">
                                <i class="fas fa-hashtag"></i>
                                <strong>ID: {{ $booking->id }}</strong>
                            </span>
                        </h6>
                        <p class="activity-item-meta">
                            <span class="profile-date-display">
                                @if($booking->calendar_schedule)
                                    {{ \Carbon\Carbon::parse($booking->calendar_schedule->date)->format('D, M j, Y') }}
                                @else
                                    {{ $booking->created_at->format('D, M j, Y') }}
                                @endif
                            </span>
                            •
                            <span class="profile-status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                        </p>
                    </div>
                    <div class="activity-action">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $index }}">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                </div>

                <!-- Details Modal -->
                <div class="modal fade booking-details-modal" id="detailsModal{{ $index }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-file-alt"></i> Booking Details - Reference #{{ $booking->id }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-fish"></i> Fishing Experience Details</h6>
                                        <div class="detail-row">
                                            <strong>Experience Title:</strong> {{ $booking->guiding->title ?? 'N/A' }}
                                        </div>
                                        <div class="detail-row">
                                            <strong>Fishing Location:</strong> {{ $booking->guiding->location ?? 'N/A' }}
                                        </div>
                                        <div class="detail-row">
                                            <strong>Experience Duration:</strong> {{ $booking->guiding->duration ?? 'N/A' }} hours
                                        </div>
                                        <div class="detail-row">
                                            <strong>Fishing Type:</strong> {{ $booking->guiding->type_of_fishing ?? 'N/A' }}
                                        </div>
                                        @if($booking->guiding->description)
                                            <div class="detail-row">
                                                <strong>Experience Description:</strong> 
                                                <p class="mt-1 text-muted">{{ Str::limit($booking->guiding->description, 200) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-calendar-check"></i> Reservation Summary</h6>
                                        <div class="detail-row">
                                            <strong>Reservation Date:</strong> {{ $booking->created_at->format('l, F j, Y \a\t g:i A') }}
                                        </div>
                                        <div class="detail-row">
                                            <strong>Scheduled Date:</strong> 
                                            @if($booking->calendar_schedule)
                                                {{ \Carbon\Carbon::parse($booking->calendar_schedule->date)->format('l, F j, Y') }}
                                            @elseif($booking->blocked_event)
                                                {{ \Carbon\Carbon::parse($booking->blocked_event->from)->format('l, F j, Y') }}
                                            @else
                                                <span class="text-danger">Date Not Available</span>
                                            @endif
                                        </div>
                                        <div class="detail-row">
                                            <strong>Party Size:</strong> {{ $booking->count_of_users ?? 1 }} {{ ($booking->count_of_users ?? 1) == 1 ? 'Guest' : 'Guests' }}
                                        </div>
                                        <div class="detail-row">
                                            <strong>Booking Status:</strong> 
                                            <span class="badge bg-{{ $booking->status == 'accepted' ? 'success' : ($booking->status == 'pending' ? 'warning' : ($booking->status == 'cancelled' ? 'secondary' : 'danger')) }}">{{ ucfirst($booking->status) }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <strong>Total Amount:</strong> <span class="text-success fw-bold">€{{ number_format($booking->price, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-user-tie"></i> Guide Information</h6>
                                        <div class="detail-row">
                                            <strong>Name:</strong> {{ $booking->guiding->user->full_name ?? 'N/A' }}
                                        </div>
                                        @if($booking->status == 'accepted')
                                            <div class="detail-row">
                                                <strong>Email:</strong> {{ $booking->guiding->user->email ?? 'N/A' }}
                                            </div>
                                            @if($booking->guiding->user->phone)
                                                <div class="detail-row">
                                                    <strong>Phone:</strong> {{ $booking->guiding->user->phone }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        @if($booking->extras)
                                            <h6 class="fw-bold mb-3"><i class="fas fa-plus-circle"></i> Additional Services</h6>
                                            @php
                                                $extras = is_string($booking->extras) ? unserialize($booking->extras) : $booking->extras;
                                            @endphp
                                            @if(is_array($extras) && count($extras) > 0)
                                                @foreach($extras as $extra)
                                                    <div class="detail-row">
                                                        <strong>{{ $extra['extra_name'] ?? 'Additional Service' }}:</strong> €{{ number_format($extra['extra_total_price'] ?? 0, 2) }}
                                                    </div>
                                                @endforeach
                                                <div class="detail-row">
                                                    <strong>Total Additional Services:</strong> <span class="text-info">€{{ number_format($booking->total_extra_price ?? 0, 2) }}</span>
                                                </div>
                                            @else
                                                <p class="text-muted">No additional services selected</p>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                @if($booking->status == 'accepted')
                                    @if($booking->canBeReviewed())
                                        <a href="{{ route('ratings.show', ['token' => $booking->token]) }}" class="btn btn-warning">
                                            <i class="fas fa-star"></i> Rate Guide
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#contactModal{{ $index }}" data-bs-dismiss="modal">
                                        <i class="fas fa-envelope"></i> Contact Guide
                                    </button>
                                @endif
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Modal -->
                @if($booking->status == 'accepted')
                    <div class="modal fade contact-modal" id="contactModal{{ $index }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-address-book"></i> Guide Contact Information
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="contact-info">
                                        <strong>Email:</strong> {{ $booking->guiding->user->email ?? 'N/A' }}
                                    </div>
                                    @if($booking->guiding->user->phone)
                                        <div class="contact-info">
                                            <strong>Phone:</strong> {{ $booking->guiding->user->phone }}
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="empty-state">
                    <i class="fas fa-calendar-plus"></i>
                    <p>No upcoming bookings</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity-section">
        <h4 class="profile-section-title">
            <i class="fas fa-calendar-check"></i>
            Booking Overview
        </h4>
        <div class="activity-list">
            @php
                $recentBookings = Auth::user()->bookings()->latest()->take(3)->get();
            @endphp
            @forelse($recentBookings as $index => $booking)
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="activity-content">
                        <h6 class="activity-item-title d-flex align-items-center">
                            <span>{{ $booking->guiding->title ?? 'Booking' }}</span>
                            <span class="booking-id-tag ms-2">
                                <i class="fas fa-hashtag"></i>
                                <strong>ID: {{ $booking->id }}</strong>
                            </span>
                        </h6>
                        <p class="activity-item-meta">
                            <span class="profile-date-display">{{ $booking->created_at->format('D, M j, Y') }}</span>
                            •
                            <span class="profile-status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                        </p>
                    </div>
                    <div class="activity-action">
                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#recentDetailsModal{{ $index }}">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                </div>

                <!-- Details Modal -->
                <div class="modal fade booking-details-modal" id="recentDetailsModal{{ $index }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-file-alt"></i> Booking Details - Reference #{{ $booking->id }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-fish"></i> Fishing Experience Details</h6>
                                        <div class="detail-row">
                                            <strong>Experience Title:</strong> {{ $booking->guiding->title ?? 'N/A' }}
                                        </div>
                                        <div class="detail-row">
                                            <strong>Fishing Location:</strong> {{ $booking->guiding->location ?? 'N/A' }}
                                        </div>
                                        <div class="detail-row">
                                            <strong>Experience Duration:</strong> {{ $booking->guiding->duration ?? 'N/A' }} hours
                                        </div>
                                        <div class="detail-row">
                                            <strong>Fishing Type:</strong> {{ $booking->guiding->type_of_fishing ?? 'N/A' }}
                                        </div>
                                        @if($booking->guiding->description)
                                            <div class="detail-row">
                                                <strong>Experience Description:</strong> 
                                                <p class="mt-1 text-muted">{{ Str::limit($booking->guiding->description, 200) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-calendar-check"></i> Reservation Summary</h6>
                                        <div class="detail-row">
                                            <strong>Reservation Date:</strong> {{ $booking->created_at->format('l, F j, Y \a\t g:i A') }}
                                        </div>
                                        <div class="detail-row">
                                            <strong>Scheduled Date:</strong> 
                                            @if($booking->calendar_schedule)
                                                {{ \Carbon\Carbon::parse($booking->calendar_schedule->date)->format('l, F j, Y') }}
                                            @elseif($booking->blocked_event)
                                                {{ \Carbon\Carbon::parse($booking->blocked_event->from)->format('l, F j, Y') }}
                                            @else
                                                <span class="text-danger">Date Not Available</span>
                                            @endif
                                        </div>
                                        <div class="detail-row">
                                            <strong>Party Size:</strong> {{ $booking->count_of_users ?? 1 }} {{ ($booking->count_of_users ?? 1) == 1 ? 'Guest' : 'Guests' }}
                                        </div>
                                        <div class="detail-row">
                                            <strong>Booking Status:</strong> 
                                            <span class="badge bg-{{ $booking->status == 'accepted' ? 'success' : ($booking->status == 'pending' ? 'warning' : ($booking->status == 'cancelled' ? 'secondary' : 'danger')) }}">{{ ucfirst($booking->status) }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <strong>Total Amount:</strong> <span class="text-success fw-bold">€{{ number_format($booking->price, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="fw-bold mb-3"><i class="fas fa-user-tie"></i> Guide Information</h6>
                                        <div class="detail-row">
                                            <strong>Name:</strong> {{ $booking->guiding->user->full_name ?? 'N/A' }}
                                        </div>
                                        @if($booking->status == 'accepted')
                                            <div class="detail-row">
                                                <strong>Email:</strong> {{ $booking->guiding->user->email ?? 'N/A' }}
                                            </div>
                                            @if($booking->guiding->user->phone)
                                                <div class="detail-row">
                                                    <strong>Phone:</strong> {{ $booking->guiding->user->phone }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        @if($booking->extras)
                                            <h6 class="fw-bold mb-3"><i class="fas fa-plus-circle"></i> Additional Services</h6>
                                            @php
                                                $extras = is_string($booking->extras) ? unserialize($booking->extras) : $booking->extras;
                                            @endphp
                                            @if(is_array($extras) && count($extras) > 0)
                                                @foreach($extras as $extra)
                                                    <div class="detail-row">
                                                        <strong>{{ $extra['extra_name'] ?? 'Additional Service' }}:</strong> €{{ number_format($extra['extra_total_price'] ?? 0, 2) }}
                                                    </div>
                                                @endforeach
                                                <div class="detail-row">
                                                    <strong>Total Additional Services:</strong> <span class="text-info">€{{ number_format($booking->total_extra_price ?? 0, 2) }}</span>
                                                </div>
                                            @else
                                                <p class="text-muted">No additional services selected</p>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                @if($booking->status == 'accepted')
                                    @if($booking->canBeReviewed())
                                        <a href="{{ route('ratings.show', ['token' => $booking->token]) }}" class="btn btn-warning">
                                            <i class="fas fa-star"></i> Rate Guide
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#recentContactModal{{ $index }}" data-bs-dismiss="modal">
                                        <i class="fas fa-envelope"></i> Contact Guide
                                    </button>
                                @endif
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Modal -->
                @if($booking->status == 'accepted')
                    <div class="modal fade contact-modal" id="recentContactModal{{ $index }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-address-book"></i> Guide Contact Information
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="contact-info">
                                        <strong>Email:</strong> {{ $booking->guiding->user->email ?? 'N/A' }}
                                    </div>
                                    @if($booking->guiding->user->phone)
                                        <div class="contact-info">
                                            <strong>Phone:</strong> {{ $booking->guiding->user->phone }}
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No recent activity</p>
                </div>
            @endforelse
        </div>
    </div>

    <style>
        .booking-id-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: inherit;
            color: #6c757d;
        }
        .booking-id-tag i {
            font-size: inherit;
        }
        .activity-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        .activity-content {
            flex: 1;
        }
        .activity-item-title {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        .activity-item-meta {
            margin: 5px 0 0;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .profile-status-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-accepted {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-cancelled, .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .booking-details-modal .modal-header {
            background: linear-gradient(135deg, #313041, #252238);
            color: white !important;
            border-bottom: none;
        }
        .booking-details-modal .modal-header .modal-title {
            color: white !important;
            font-weight: 600;
        }
        .booking-details-modal .modal-header .btn-close {
            filter: invert(1);
        }
        .booking-details-modal .detail-row {
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .booking-details-modal .detail-row:last-child {
            border-bottom: none;
        }
        .booking-details-modal .detail-row strong {
            color: #313041;
            font-weight: 600;
        }
        .contact-modal .modal-content {
            border-radius: 12px;
        }
        .contact-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .contact-info strong {
            color: #313041;
        }
    </style>
@endsection
