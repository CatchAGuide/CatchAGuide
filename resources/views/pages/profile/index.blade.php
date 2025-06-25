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

            <div class="col-lg-4 col-md-6 mb-4">
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
            </div>
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
            @forelse($upcomingBookings as $booking)
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="activity-content">
                        <h6 class="activity-item-title">{{ $booking->guiding->title ?? 'Booking' }}</h6>
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
                        <a href="{{ route('profile.showbooking', $booking->id) }}"
                            class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
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
            Recent Activity
        </h4>
        <div class="activity-list">
            @php
                $recentBookings = Auth::user()->bookings()->latest()->take(3)->get();
            @endphp
            @forelse($recentBookings as $booking)
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                                    <div class="activity-content">
                    <h6 class="activity-item-title">{{ $booking->guiding->title ?? 'Booking' }}</h6>
                    <p class="activity-item-meta">
                        <span class="profile-date-display">{{ $booking->created_at->format('D, M j, Y') }}</span>
                        •
                        <span class="profile-status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                    </p>
                </div>
                    <div class="activity-action">
                        <a href="{{ route('profile.showbooking', $booking->id) }}"
                            class="btn btn-sm btn-outline-primary">View</a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No recent activity</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
