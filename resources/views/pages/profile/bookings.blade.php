@extends('pages.profile.layouts.profile')
@section('title', 'All Bookings')

@section('profile-content')
    <style>
        /* Scope all styles to profile content area to prevent conflicts with main header */
        .profile-bookings-container {
            /* Container for all booking page styles */
        }
        
        .profile-bookings-container .bookings-header {
            background: linear-gradient(135deg, #313041, #252238);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .profile-bookings-container .bookings-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.5;
            animation: float 20s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateX(-100px) translateY(-100px); }
            100% { transform: translateX(100px) translateY(100px); }
        }
        
        .profile-bookings-container .bookings-header .stats {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }
        
        .profile-bookings-container .stat-item {
            text-align: center;
        }
        
        .profile-bookings-container .stat-item.clickable-stat {
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 10px;
            border-radius: 8px;
        }
        
        .profile-bookings-container .stat-item.clickable-stat:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .profile-bookings-container .stat-item.clickable-stat.active {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .profile-bookings-container .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }
        
        .profile-bookings-container .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .profile-bookings-container .booking-filters {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .profile-bookings-container .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .profile-bookings-container .filter-tab {
            padding: 12px 24px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            background: white;
            color: #6c757d;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .profile-bookings-container .filter-tab.active,
        .profile-bookings-container .filter-tab:hover {
            border-color: #313041;
            background: #313041;
            color: white;
        }
        
        .profile-bookings-container .search-filters {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .profile-bookings-container .search-input {
            flex: 1;
            min-width: 200px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .profile-bookings-container .status-filter {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            min-width: 150px;
        }
        
        .profile-bookings-container .bookings-container {
            display: grid;
            gap: 20px;
        }
        
        .profile-bookings-container .booking-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .profile-bookings-container .booking-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .profile-bookings-container .booking-card.my-booking {
            border-left-color: #17a2b8;
        }
        
        .profile-bookings-container .booking-card.guide-booking {
            border-left-color: #28a745;
        }
        
        .profile-bookings-container .booking-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .profile-bookings-container .booking-type {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .profile-bookings-container .booking-type.my-booking {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }
        
        .profile-bookings-container .booking-type.guide-booking {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .profile-bookings-container .booking-type i {
            margin-right: 5px;
        }
        
        .profile-bookings-container .booking-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #313041 !important;
            margin: 8px 0 12px 0 !important;
            line-height: 1.3 !important;
            display: block !important;
            word-wrap: break-word !important;
        }
        
        .profile-bookings-container .booking-subtitle {
            color: #6c757d !important;
            font-size: 0.95rem !important;
            font-weight: 500 !important;
            margin: 0 !important;
            display: block !important;
        }
        
        .profile-bookings-container .booking-status {
            margin-left: auto;
        }
        
        .profile-bookings-container .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .profile-bookings-container .status-accepted {
            background: #d4edda;
            color: #155724;
        }
        
        .profile-bookings-container .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .profile-bookings-container .status-cancelled,
        .profile-bookings-container .status-rejected,
        .profile-bookings-container .status-storniert {
            background: #f8d7da;
            color: #721c24;
        }
        
        .booking-content {
            padding: 20px;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        
        .detail-content h6 {
            margin: 0;
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-content p {
            margin: 4px 0 0 0;
            font-weight: 600;
            color: #333;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .profile-bookings-container .btn-action {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .profile-bookings-container .btn-primary {
            background: #313041;
            color: white;
        }
        
        .profile-bookings-container .btn-primary:hover {
            background: #252238;
            color: white;
        }
        
        .profile-bookings-container .btn-success {
            background: #28a745;
            color: white;
        }
        
        .profile-bookings-container .btn-success:hover {
            background: #218838;
            color: white;
        }
        
        .profile-bookings-container .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .profile-bookings-container .btn-danger:hover {
            background: #c82333;
            color: white;
        }
        
        .profile-bookings-container .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .profile-bookings-container .btn-warning:hover {
            background: #e0a800;
            color: #212529;
        }
        
        .profile-bookings-container .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        .profile-bookings-container .btn-info:hover {
            background: #138496;
            color: white;
        }
        
        .profile-bookings-container .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .profile-bookings-container .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }
        
        .profile-bookings-container .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .profile-bookings-container .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .profile-bookings-container .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .hidden {
            display: none !important;
        }
        
        @media (max-width: 768px) {
            .bookings-header .stats {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-tabs {
                flex-wrap: wrap;
            }
            
            .booking-details {
                grid-template-columns: 1fr;
            }
            
            .booking-actions {
                flex-direction: column;
            }
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
        
        .load-more-container {
            margin-top: 30px;
            text-align: center;
            padding: 20px;
        }
        
        .load-more-btn {
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 25px;
            border: 2px solid #313041;
            color: #313041;
            background: white;
            transition: all 0.3s ease;
            position: relative;
            min-width: 200px;
        }
        
        .load-more-btn:hover {
            background: #313041;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(49, 48, 65, 0.3);
        }
        
        .load-more-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .loading-spinner {
            margin-left: 10px;
        }
        
        .contact-info strong {
            color: #313041;
        }
        
        .profile-bookings-container .pagination-container {
            margin-top: 40px;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .profile-bookings-container .pagination-section {
            margin-bottom: 30px;
        }
        
        .profile-bookings-container .pagination-section:last-child {
            margin-bottom: 0;
        }
        
        .profile-bookings-container .pagination-section h5 {
            color: #313041;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .profile-bookings-container .pagination-wrapper {
            display: flex;
            justify-content: center;
        }
        
        .profile-bookings-container .pagination-wrapper .pagination {
            margin: 0;
        }
        
        .profile-bookings-container .pagination-wrapper .page-link {
            color: #313041;
            border: 1px solid #dee2e6;
            border-radius: 8px !important;
            margin: 0 3px;
            padding: 10px 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .profile-bookings-container .pagination-wrapper .page-link:hover {
            background: #313041;
            color: white;
            border-color: #313041;
        }
        
        .profile-bookings-container .pagination-wrapper .page-item.active .page-link {
            background: #313041;
            border-color: #313041;
            color: white;
        }
        
        .profile-bookings-container .pagination-wrapper .page-item.disabled .page-link {
            color: #6c757d;
            background: #f8f9fa;
            border-color: #dee2e6;
        }
        
        .load-more-container {
            text-align: center;
            margin-top: 30px;
        }
        
        .profile-bookings-container .btn-load-more {
            background: linear-gradient(135deg, #313041, #252238);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .profile-bookings-container .btn-load-more:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(49, 48, 65, 0.3);
            color: white;
        }
        
        .profile-bookings-container .btn-load-more:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .loading-spinner {
            display: none;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .rejection-message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 8px;
            color: #856404;
            font-size: 12px;
            margin-top: 8px;
        }



        /* Rejection details modal styles */
        .rejection-details-modal .modal-header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white !important;
            border-bottom: none;
        }

        .rejection-details-modal .modal-header .modal-title {
            color: white !important;
        }

        .rejection-details-modal .modal-header .btn-close {
            filter: invert(1);
        }

        .rejection-details-modal .rejection-content {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .rejection-details-modal .alternative-dates {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .rejection-details-modal .reschedule-info {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
        }

        /* Booking Details Modal Styles */
        .profile-bookings-container .booking-details-modal .modal-header {
            background: linear-gradient(135deg, #313041, #252238);
            color: white !important;
            border-bottom: none;
        }

        .profile-bookings-container .booking-details-modal .modal-header .modal-title {
            color: white !important;
            font-weight: 600;
        }

        .profile-bookings-container .booking-details-modal .modal-header .btn-close {
            filter: invert(1);
        }

        .profile-bookings-container .booking-details-modal .detail-row {
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0f0f0;
        }

        .profile-bookings-container .booking-details-modal .detail-row:last-child {
            border-bottom: none;
        }

        .profile-bookings-container .booking-details-modal .detail-row strong {
            color: #313041;
            font-weight: 600;
        }

        .profile-bookings-container .booking-details-modal h6 {
            color: #313041;
            font-weight: 700;
            border-bottom: 2px solid #313041;
            padding-bottom: 0.5rem;
        }

        .profile-bookings-container .booking-details-modal .modal-footer {
            border-top: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        /* Price Calculation Styles */
        .price-calculation-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .price-calculation-section .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .price-calculation-section .additional-services {
            background: #fff;
            border-radius: 6px;
            padding: 12px;
            margin: 10px 0;
        }
        
        .price-calculation-section .border-top {
            border-color: #dee2e6 !important;
        }
        
        .price-calculation-section .text-info {
            color: #0dcaf0 !important;
        }
        
        .price-calculation-section .text-success {
            color: #198754 !important;
        }
    </style>

    <!-- Wrap entire content in scoped container -->
    <div class="profile-bookings-container">
        <!-- Header Section -->
        <div class="bookings-header">
        <h1 class="mb-0 text-white">
            <i class="fas fa-calendar-check"></i>
            All Bookings
        </h1>
        <p class="mb-0 mt-2 text-white">Manage your fishing trip bookings and guide requests</p>
        
        <div class="stats">
            @php
                // Calculate all statistics
                $allMyBookings = auth()->user()->bookings;
                $allGuideBookings = collect();
                if(auth()->user()->is_guide) {
                    try {
                        $allGuideBookings = \App\Models\Booking::whereHas('guiding', function($query) {
                            $query->where('user_id', auth()->user()->id);
                        })->get();
                    } catch (\Exception $e) {
                        $allGuideBookings = collect();
                    }
                }
                
                $totalBookings = $allMyBookings->count() + $allGuideBookings->count();
                $pendingRequests = $allMyBookings->where('status', 'pending')->count() + $allGuideBookings->where('status', 'pending')->count();
                $confirmedBookings = $allMyBookings->where('status', 'accepted')->count() + $allGuideBookings->where('status', 'accepted')->count();
                $cancelledBookings = $allMyBookings->where('status', 'cancelled')->count() + $allGuideBookings->where('status', 'cancelled')->count();
                $rejectedBookings = $allMyBookings->where('status', 'rejected')->count() + $allGuideBookings->where('status', 'rejected')->count();
                $cancelledRejectedBookings = $cancelledBookings + $rejectedBookings; // Combined for regular users
                
                // Calculate completed bookings (accepted and past book_date)
                $completedBookings = 0;
                foreach($allMyBookings->where('status', 'accepted') as $booking) {
                    $bookDate = null;
                    if($booking->calendar_schedule && $booking->calendar_schedule->date) {
                        $bookDate = \Carbon\Carbon::parse($booking->calendar_schedule->date);
                    } elseif($booking->blocked_event && $booking->blocked_event->from) {
                        $bookDate = \Carbon\Carbon::parse($booking->blocked_event->from);
                    }
                    if($bookDate && $bookDate->isPast()) {
                        $completedBookings++;
                    }
                }
                foreach($allGuideBookings->where('status', 'accepted') as $booking) {
                    $bookDate = null;
                    if($booking->calendar_schedule && $booking->calendar_schedule->date) {
                        $bookDate = \Carbon\Carbon::parse($booking->calendar_schedule->date);
                    } elseif($booking->blocked_event && $booking->blocked_event->from) {
                        $bookDate = \Carbon\Carbon::parse($booking->blocked_event->from);
                    }
                    if($bookDate && $bookDate->isPast()) {
                        $completedBookings++;
                    }
                }
            @endphp
            
            <div class="stat-item clickable-stat" data-filter="all">
                <span class="stat-number">{{ $totalBookings }}</span>
                <span class="stat-label">Total Bookings</span>
            </div>
            <div class="stat-item clickable-stat" data-filter="pending">
                <span class="stat-number">{{ $pendingRequests }}</span>
                <span class="stat-label">Pending Requests</span>
            </div>
            @if(auth()->user()->is_guide)
                <div class="stat-item clickable-stat" data-filter="accepted">
                    <span class="stat-number">{{ $confirmedBookings }}</span>
                    <span class="stat-label">My Confirmed</span>
                </div>
                <div class="stat-item clickable-stat" data-filter="cancelled">
                    <span class="stat-number">{{ $cancelledBookings }}</span>
                    <span class="stat-label">Cancelled</span>
                </div>
                <div class="stat-item clickable-stat" data-filter="rejected">
                    <span class="stat-number">{{ $rejectedBookings }}</span>
                    <span class="stat-label">Rejected</span>
                </div>
                <div class="stat-item clickable-stat" data-filter="completed">
                    <span class="stat-number">{{ $completedBookings }}</span>
                    <span class="stat-label">Completed</span>
                </div>
            @else
                <!-- Regular User Stats - Show simplified stats -->
                <div class="stat-item clickable-stat" data-filter="accepted">
                    <span class="stat-number">{{ $confirmedBookings }}</span>
                    <span class="stat-label">Confirmed</span>
                </div>
                <div class="stat-item clickable-stat" data-filter="cancelled-rejected">
                    <span class="stat-number">{{ $cancelledRejectedBookings }}</span>
                    <span class="stat-label">Cancelled/Rejected</span>
                </div>
                <div class="stat-item clickable-stat" data-filter="completed">
                    <span class="stat-number">{{ $completedBookings }}</span>
                    <span class="stat-label">Past Bookings</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Filters Section -->
    <div class="booking-filters">
        @if(auth()->user()->is_guide)
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">
                <i class="fas fa-list"></i> All Bookings
            </button>
            <button class="filter-tab" data-filter="my-booking">
                <i class="fas fa-user"></i> My Bookings
            </button>
            <button class="filter-tab" data-filter="guide-booking">
                <i class="fas fa-fish"></i> Guide Bookings
            </button>
        </div>
        @endif
        
        <div class="search-filters">
            <input type="text" class="search-input" id="searchInput" placeholder="Search by guiding name, location, or guide...">
            <select class="status-filter" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="accepted">Confirmed</option>
                <option value="pending">Pending Requests</option>
                @if(auth()->user()->is_guide)
                    <option value="cancelled">Cancelled</option>
                    <option value="rejected">Rejected</option>
                @else
                    <option value="accepted">Confirmed</option>
                    <option value="cancelled-rejected">Cancelled/Rejected</option>
                @endif
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    <!-- Bookings Container -->
    <div class="bookings-container" id="bookingsContainer" 
         data-has-more-my="{{ $bookings->hasMorePages() ? 'true' : 'false' }}"
         data-has-more-guide="{{ auth()->user()->is_guide && $guideBookings && method_exists($guideBookings, 'hasMorePages') ? ($guideBookings->hasMorePages() ? 'true' : 'false') : 'false' }}">
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
                                
                                <button class="btn-action btn-info" data-bs-toggle="modal" data-bs-target="#contactModal{{ $index }}">
                                    <i class="fas fa-envelope"></i> Contact Guide
                                </button>
                            @elseif($booking->status == 'pending')
                                <span class="btn-action btn-secondary">
                                    <i class="fas fa-clock"></i> Waiting for Response
                                </span>
                            @elseif(in_array($booking->status, ['cancelled', 'rejected', 'storniert']))
                                <button class="btn-action btn-danger" data-bs-toggle="modal" data-bs-target="#rejectionModal{{ $index }}" 
                                    <i class="fas fa-exclamation-triangle"></i> {{ $booking->status == 'cancelled' ? 'Cancellation' : 'Rejection' }} Details
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Details Modal for My Bookings -->
                <div class="modal fade booking-details-modal" id="detailsModal{{ $index }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-file-alt"></i> Booking Confirmation Details - Reference #{{ $booking->id }}
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
                                            @if($booking->status == 'cancelled' && $booking->additional_information)
                                                <div class="detail-row">
                                                    <strong>Cancellation Reason:</strong> 
                                                    <p class="mt-1 text-danger">{{ $booking->additional_information }}</p>
                                                </div>
                                            @endif
                                            @if($booking->status == 'rejected' && $booking->additional_information)
                                                <div class="detail-row">
                                                    <strong>Rejection Reason:</strong> 
                                                    <p class="mt-1 text-danger">{{ $booking->additional_information }}</p>
                                                </div>
                                            @endif
                                            <div class="price-calculation-section">
                                                <h6 class="fw-bold mb-3"><i class="fas fa-calculator"></i> Price Breakdown</h6>
                                                <div class="detail-row">
                                                    <strong>Base Service Fee:</strong> 
                                                    <span class="text-success">€{{ number_format($booking->price - ($booking->total_extra_price ?? 0), 2) }}</span>
                                                </div>
                                                
                                                @if($booking->extras)
                                                    @php
                                                        $extras = is_string($booking->extras) ? unserialize($booking->extras) : $booking->extras;
                                                    @endphp
                                                    @if(is_array($extras) && count($extras) > 0)
                                                        <div class="additional-services mt-2">
                                                            <strong>Additional Services:</strong>
                                                            <div class="ms-3 mt-2">
                                                                @foreach($extras as $extra)
                                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                                        <span>+ {{ $extra['name'] ?? 'Additional Service' }}</span>
                                                                        <span class="text-info">€{{ number_format($extra['price'] ?? 0, 2) }}</span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                                
                                                <div class="detail-row border-top pt-2 mt-2">
                                                    <strong>Total Price:</strong> 
                                                    <span class="text-success fw-bold">€{{ number_format($booking->price, 2) }}</span>
                                                </div>
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
                                            @if(!in_array($booking->status, ['cancelled', 'pending', 'rejected']))
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
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    @if($booking->status == 'accepted')
                                        @if($booking->canBeReviewed())
                                            <a href="{{ route('ratings.show', ['token' => $booking->token]) }}" class="btn btn-warning">
                                                <i class="fas fa-star"></i> Rate Guide
                                            </a>
                                        @endif
                                    @endif
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Contact Modal for My Bookings -->
                <div class="modal fade contact-modal" id="contactModal{{ $index }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-address-book"></i> Guide Contact
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

                <!-- Rejection Details Modal for My Bookings -->
                @if(in_array($booking->status, ['cancelled', 'rejected', 'storniert']))
                    <div class="modal fade rejection-details-modal" id="rejectionModal{{ $index }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        {{ ucfirst($booking->status) }} Details - Booking #{{ $booking->id }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="rejection-content">
                                        <h6><i class="fas fa-info-circle"></i> {{ $booking->status == 'cancelled' ? 'Cancellation' : 'Rejection' }} Reason</h6>
                                        <p class="mb-0">{{ $booking->additional_information ?? __('emails.guest_booking_request_expired_text_2') }}</p>
                                    </div>

                                    @if($booking->status == 'rejected')
                                        {{-- Check for alternative dates or reschedule information --}}
                                        @php
                                            // Look for alternative dates in the booking or related records
                                            $alternativeDates = [];
                                            $rescheduleInfo = null;
                                            
                                            // You may need to adjust these based on your database structure
                                            // Check if there are any alternative dates suggested
                                            if($booking->alternative_dates) {
                                                $alternativeDates = is_string($booking->alternative_dates) ? 
                                                    json_decode($booking->alternative_dates, true) : 
                                                    $booking->alternative_dates;
                                            }
                                            
                                            // Check if booking was rescheduled
                                            if($booking->rescheduled_from_id) {
                                                $originalBooking = \App\Models\Booking::find($booking->rescheduled_from_id);
                                                $rescheduleInfo = $originalBooking;
                                            }
                                        @endphp

                                        @if(!empty($alternativeDates))
                                            <div class="alternative-dates">
                                                <h6><i class="fas fa-calendar-alt"></i> Suggested Alternative Dates</h6>
                                                <ul class="list-unstyled">
                                                    @foreach($alternativeDates as $altDate)
                                                        <li><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($altDate)->format('l, F j, Y') }}</li>
                                                    @endforeach
                                                </ul>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i> 
                                                    You can contact the guide to book one of these alternative dates.
                                                </small>
                                            </div>
                                        @endif

                                        @if($rescheduleInfo)
                                            <div class="reschedule-info">
                                                <h6><i class="fas fa-sync-alt"></i> Reschedule Information</h6>
                                                <p><strong>Original Booking Date:</strong> 
                                                    @if($rescheduleInfo->calendar_schedule)
                                                        {{ \Carbon\Carbon::parse($rescheduleInfo->calendar_schedule->date)->format('l, F j, Y') }}
                                                    @elseif($rescheduleInfo->blocked_event)
                                                        {{ \Carbon\Carbon::parse($rescheduleInfo->blocked_event->from)->format('l, F j, Y') }}
                                                    @else
                                                        Not available
                                                    @endif
                                                </p>
                                                <p><strong>Reschedule Reason:</strong> {{ $rescheduleInfo->additional_information ?? 'No reason provided' }}</p>
                                            </div>
                                        @endif
                                    @endif

                                    @if($booking->status == 'cancelled' || $booking->status == 'rejected')
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>What happens next?</strong> 
                                            {{ __('emails.guest_booking_request_expired_text_3') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    @if($booking->status == 'rejected' || $booking->status == 'cancelled' && $booking->guiding->user)
                                        <a href="{{ route('additional.contact') }}" class="btn btn-primary">
                                            <i class="fas fa-envelope"></i> Contact Us
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        <!-- Guide Bookings -->
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

                <!-- Details Modal for Guide Bookings -->
                <div class="modal fade booking-details-modal" id="guideDetailsModal{{ $gIndex }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-file-alt"></i> Guide Service Details - Reference #{{ $booking->id }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-3"><i class="fas fa-fish"></i> Service Details</h6>
                                            <div class="detail-row">
                                                <strong>Experience Title:</strong> {{ $booking->guiding->title ?? 'N/A' }}
                                            </div>
                                            <div class="detail-row">
                                                <strong>Service Location:</strong> {{ $booking->guiding->location ?? 'N/A' }}
                                            </div>
                                            <div class="detail-row">
                                                <strong>Service Duration:</strong> {{ $booking->guiding->duration ?? 'N/A' }} hours
                                            </div>
                                            <div class="detail-row">
                                                <strong>Fishing Type:</strong> {{ $booking->guiding->type_of_fishing ?? 'N/A' }}
                                            </div>
                                            @if($booking->guiding->description)
                                                <div class="detail-row">
                                                    <strong>Service Description:</strong> 
                                                    <p class="mt-1 text-muted">{{ Str::limit($booking->guiding->description, 200) }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-3"><i class="fas fa-calendar-check"></i> Booking Summary</h6>
                                            <div class="detail-row">
                                                <strong>Service Date:</strong> 
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
                                            @if($booking->additional_information)
                                                <div class="detail-row">
                                                    <strong>@if($booking->status == 'cancelled') Cancellation Reason: @else Rejection Reason: @endif</strong> 
                                                    <p class="mt-1 text-danger">{{ $booking->additional_information }}</p>
                                                </div>
                                            @endif

                                            <div class="detail-row">
                                                <strong>Base Service Fee:</strong> <span class="text-success">€{{ number_format($booking->price - ($booking->total_extra_price ?? 0), 2) }}</span>
                                            </div>
                                            @if($booking->extras)
                                                @php
                                                    $extras = is_string($booking->extras) ? unserialize($booking->extras) : $booking->extras;
                                                @endphp
                                                @if(is_array($extras) && count($extras) > 0)
                                                    <div class="detail-row">
                                                        <strong>Additional Services:</strong>
                                                        <div class="ms-3 mt-2">
                                                            @foreach($extras as $extra)
                                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                                    <span>+ {{ $extra['extra_name'] ?? 'Additional Service' }}</span>
                                                                    <span class="text-success">€{{ number_format($extra['extra_total_price'] ?? 0, 2) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                            <div class="detail-row border-top pt-2 mt-2">
                                                <strong>Total Price:</strong> <span class="text-success fw-bold">€{{ number_format($booking->price, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-3"><i class="fas fa-user"></i> Client Information</h6>
                                            <div class="detail-row">
                                                <strong>Client Name:</strong> {{ $booking->user->full_name ?? 'N/A' }}
                                            </div>
                                            @if (!in_array($booking->status, ['cancelled', 'pending', 'rejected']))
                                                <div class="detail-row">
                                                    <strong>Contact Email:</strong> {{ $booking->user->email ?? 'N/A' }}
                                                </div>
                                                @if($booking->phone)
                                                    <div class="detail-row">
                                                        <strong>Contact Phone:</strong> {{ $booking->phone }}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    @if($booking->status == 'accepted')
                                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#guideContactModal{{ $gIndex }}" data-bs-dismiss="modal">
                                            <i class="fas fa-envelope"></i> Contact Client
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Contact Modal for Guide Bookings -->
                <div class="modal fade contact-modal" id="guideContactModal{{ $gIndex }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-address-book"></i> Client Contact Information
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="contact-info">
                                        <strong>Email:</strong> {{ $booking->user->email ?? 'N/A' }}
                                    </div>
                                    @if($booking->phone)
                                        <div class="contact-info">
                                            <strong>Phone:</strong> {{ $booking->phone }}
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Rejection Details Modal for Guide Bookings -->
                @if(in_array($booking->status, ['cancelled', 'rejected', 'storniert']))
                    <div class="modal fade rejection-details-modal" id="guideRejectionModal{{ $gIndex }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        {{ ucfirst($booking->status) }} Details - Booking #{{ $booking->id }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="rejection-content">
                                        <h6><i class="fas fa-info-circle"></i> {{ $booking->status == 'cancelled' ? 'Cancellation' : 'Rejection' }} Reason</h6>
                                        <p class="mb-0">{{ $booking->additional_information ?? __('emails.guest_booking_request_expired_text_2') }}</p>
                                    </div>

                                    @if($booking->status == 'rejected')
                                        {{-- Check for alternative dates or reschedule information --}}
                                        @php
                                            // Look for alternative dates in the booking or related records
                                            $alternativeDates = [];
                                            $rescheduleInfo = null;
                                            
                                            // Check if there are any alternative dates suggested
                                            if($booking->alternative_dates) {
                                                $alternativeDates = is_string($booking->alternative_dates) ? 
                                                    json_decode($booking->alternative_dates, true) : 
                                                    $booking->alternative_dates;
                                            }
                                            
                                            // Check if booking was rescheduled
                                            if($booking->rescheduled_from_id) {
                                                $originalBooking = \App\Models\Booking::find($booking->rescheduled_from_id);
                                                $rescheduleInfo = $originalBooking;
                                            }
                                        @endphp

                                        @if(!empty($alternativeDates))
                                            <div class="alternative-dates">
                                                <h6><i class="fas fa-calendar-alt"></i> Suggested Alternative Dates</h6>
                                                <ul class="list-unstyled">
                                                    @foreach($alternativeDates as $altDate)
                                                        <li><i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($altDate)->format('l, F j, Y') }}</li>
                                                    @endforeach
                                                </ul>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i> 
                                                    These alternative dates were suggested to the client.
                                                </small>
                                            </div>
                                        @endif

                                        @if($rescheduleInfo)
                                            <div class="reschedule-info">
                                                <h6><i class="fas fa-sync-alt"></i> Reschedule Information</h6>
                                                <p><strong>Original Booking Date:</strong> 
                                                    @if($rescheduleInfo->calendar_schedule)
                                                        {{ \Carbon\Carbon::parse($rescheduleInfo->calendar_schedule->date)->format('l, F j, Y') }}
                                                    @elseif($rescheduleInfo->blocked_event)
                                                        {{ \Carbon\Carbon::parse($rescheduleInfo->blocked_event->from)->format('l, F j, Y') }}
                                                    @else
                                                        Not available
                                                    @endif
                                                </p>
                                                <p><strong>Reschedule Reason:</strong> {{ $rescheduleInfo->additional_information ?? 'No reason provided' }}</p>
                                            </div>
                                        @endif
                                    @endif

                                    @if($booking->status == 'cancelled')
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Booking Cancelled:</strong> 
                                            {{ __('emails.guest_booking_request_expired_text_3') }}
                                        </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    @if($booking->user)
                                        <a href="{{ route('additional.contact') }}" class="btn btn-primary">
                                            <i class="fas fa-envelope"></i> Contact Us
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        @if((!$bookings || $bookings->count() == 0) && (!$guideBookings || !method_exists($guideBookings, 'count') || $guideBookings->count() == 0))
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No Bookings Found</h3>
                <p>You don't have any bookings yet. Start exploring fishing guides!</p>
            </div>
        @endif
    </div>

    <!-- Load More Section -->
    @if($bookings->hasMorePages() || (auth()->user()->is_guide && $guideBookings && method_exists($guideBookings, 'hasMorePages') && $guideBookings->hasMorePages()))
        <div class="load-more-container">
            <button id="loadMoreBtn" class="btn btn-outline-primary load-more-btn">
                <i class="fas fa-plus"></i> Load More Bookings
                <span class="loading-spinner" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                </span>
            </button>
        </div>
    @endif
    </div> <!-- End profile-bookings-container -->
@endsection

@section('js_after')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterTabs = document.querySelectorAll('.profile-bookings-container .filter-tab');
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            const clickableStats = document.querySelectorAll('.profile-bookings-container .clickable-stat');
            let myBookingsPage = {{ $bookings->currentPage() }};
            let guideBookingsPage = {{ $guideBookings && method_exists($guideBookings, 'currentPage') ? $guideBookings->currentPage() : 1 }};
            let hasMoreMyBookings = {{ $bookings->hasMorePages() ? 'true' : 'false' }};
            let hasMoreGuideBookings = {{ auth()->user()->is_guide && $guideBookings && method_exists($guideBookings, 'hasMorePages') ? ($guideBookings->hasMorePages() ? 'true' : 'false') : 'false' }};

            // Get all booking cards (including dynamically loaded ones)
            function getBookingCards() {
                return document.querySelectorAll('.profile-bookings-container .booking-card');
            }

            // Filter functionality
            function filterBookings() {
                const activeTab = document.querySelector('.profile-bookings-container .filter-tab.active');
                // For regular users without tabs, default to 'all'
                const activeFilter = activeTab ? activeTab.dataset.filter : 'all';
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const statusValue = statusFilter ? statusFilter.value : '';
                const bookingCards = getBookingCards();

                bookingCards.forEach(card => {
                    const cardType = card.dataset.type;
                    const cardStatus = card.dataset.status;
                    const cardCompleted = card.dataset.completed === 'true';
                    const cardSearch = card.dataset.search || '';

                    let showCard = true;

                    // Type filter (only apply if we have tabs - for guides)
                    if (activeTab && activeFilter !== 'all' && cardType !== activeFilter) {
                        showCard = false;
                    }

                    // Search filter
                    if (searchTerm && !cardSearch.includes(searchTerm)) {
                        showCard = false;
                    }

                    // Status filter
                    if (statusValue) {
                        if (statusValue === 'completed') {
                            // Show only completed bookings (accepted and past date)
                            if (!cardCompleted) {
                                showCard = false;
                            }
                        } else if (statusValue === 'cancelled-rejected') {
                            // Show both cancelled and rejected bookings
                            if (cardStatus !== 'cancelled' && cardStatus !== 'rejected') {
                                showCard = false;
                            }
                        } else {
                            // Regular status filter
                            if (cardStatus !== statusValue) {
                                showCard = false;
                            }
                        }
                    }

                    if (showCard) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });

                // Show empty state if no cards are visible
                const visibleCards = document.querySelectorAll('.profile-bookings-container .booking-card:not(.hidden)');
                const container = document.getElementById('bookingsContainer');
                let dynamicEmptyState = container.querySelector('.empty-state.search-empty');
                
                if (visibleCards.length === 0 && !dynamicEmptyState) {
                    const emptyDiv = document.createElement('div');
                    emptyDiv.className = 'empty-state search-empty';
                    emptyDiv.innerHTML = `
                        <i class="fas fa-search"></i>
                        <h3>No Results Found</h3>
                        <p>Try adjusting your filters or search terms.</p>
                    `;
                    container.appendChild(emptyDiv);
                } else if (visibleCards.length > 0 && dynamicEmptyState) {
                    dynamicEmptyState.remove();
                }
            }

            // Clickable stats functionality
            clickableStats.forEach(stat => {
                stat.addEventListener('click', function() {
                    const filterValue = this.dataset.filter;
                    
                    // Update visual state
                    clickableStats.forEach(s => s.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update status filter dropdown
                    if (statusFilter) {
                        if (filterValue === 'all') {
                            statusFilter.value = '';
                        } else {
                            statusFilter.value = filterValue;
                        }
                    }
                    
                    // For regular users without tabs, we need to ensure the filter works
                    // Update filter tabs if they exist (for guides)
                    const filterTabs = document.querySelectorAll('.profile-bookings-container .filter-tab');
                    if (filterTabs.length > 0) {
                        // Guide user - use existing tab logic
                        const allTab = document.querySelector('.profile-bookings-container .filter-tab[data-filter="all"]');
                        if (allTab) {
                            filterTabs.forEach(t => t.classList.remove('active'));
                            allTab.classList.add('active');
                        }
                    }
                    
                    // Apply filter
                    filterBookings();
                });
            });

            // Tab click handlers
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    filterTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    filterBookings();
                });
            });

            // Search input handler
            if (searchInput) {
                searchInput.addEventListener('input', filterBookings);
            }

            // Status filter handler
            if (statusFilter) {
                statusFilter.addEventListener('change', function() {
                    // Update clickable stats visual state
                    clickableStats.forEach(s => s.classList.remove('active'));
                    const matchingStat = document.querySelector(`.clickable-stat[data-filter="${this.value}"]`) || 
                                       document.querySelector('.clickable-stat[data-filter="all"]');
                    if (matchingStat) {
                        matchingStat.classList.add('active');
                    }
                    
                    // For guides with tabs, ensure "All Bookings" tab is active when filtering by status
                    const filterTabs = document.querySelectorAll('.profile-bookings-container .filter-tab');
                    if (filterTabs.length > 0) {
                        const allTab = document.querySelector('.profile-bookings-container .filter-tab[data-filter="all"]');
                        if (allTab) {
                            filterTabs.forEach(t => t.classList.remove('active'));
                            allTab.classList.add('active');
                        }
                    }
                    
                    filterBookings();
                });
            }

            // Load more functionality
            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    const button = this;
                    const spinner = button.querySelector('.loading-spinner');
                    
                    // Show loading state
                    button.disabled = true;
                    spinner.style.display = 'inline-block';
                    
                    // Determine which type to load (prioritize my bookings first)
                    let type = 'my';
                    let page = myBookingsPage + 1;
                    
                    if (hasMoreMyBookings) {
                        type = 'my';
                        page = myBookingsPage + 1;
                    } else if (hasMoreGuideBookings) {
                        type = 'guide';
                        page = guideBookingsPage + 1;
                    }
                    
                    // Make AJAX request
                    fetch(`{{ route('profile.bookings.load-more') }}?type=${type}&page=${page}`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Parse the HTML and append new booking cards
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        const newCards = tempDiv.querySelectorAll('.booking-card');
                        
                        const container = document.querySelector('.profile-bookings-container .bookings-container');
                        newCards.forEach(card => {
                            container.appendChild(card);
                        });
                        
                        // Update page counters
                        if (type === 'my') {
                            myBookingsPage++;
                            // Check if there are more pages
                            if (newCards.length < 5) {
                                hasMoreMyBookings = false;
                            }
                        } else {
                            guideBookingsPage++;
                            if (newCards.length < 5) {
                                hasMoreGuideBookings = false;
                            }
                        }
                        
                        // Hide button if no more pages
                        if (!hasMoreMyBookings && !hasMoreGuideBookings) {
                            button.style.display = 'none';
                        }
                        
                        // Re-apply filters to new cards
                        filterBookings();
                    })
                    .catch(error => {
                        console.error('Error loading more bookings:', error);
                        alert('Error loading more bookings. Please try again.');
                    })
                    .finally(() => {
                        // Hide loading state
                        button.disabled = false;
                        spinner.style.display = 'none';
                    });
                });
            }

            // Initialize with "Total Bookings" active
            const totalStat = document.querySelector('.clickable-stat[data-filter="all"]');
            if (totalStat) {
                totalStat.classList.add('active');
            }
            
            // For regular users without tabs, run initial filter to show all bookings
            const hasFilterTabs = document.querySelectorAll('.profile-bookings-container .filter-tab').length > 0;
            if (!hasFilterTabs) {
                // Regular user - run initial filter
                filterBookings();
            }
        });
    </script>
@endsection