@extends('pages.profile.layouts.profile')

@section('title', __('profile.calendar'))
@section('css_after')
    <!-- Litepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>
    <style>
        .fc .fc-button-primary {
            background-color: #313041;
            border-color: #313041;
        }
        .fc .fc-toolbar-title {
            color: #313041;
        }

        a:hover {
            color: #313041;
        }
        
        .fc-daygrid-event-dot {
            margin: 0 4px;
            box-sizing: content-box;
            width: 0;
            height: 0;
            border: 4px solid #3788d8;
            border: calc(var(--fc-daygrid-event-dot-width,8px)/ 2) solid #313041;
            border-radius: 4px;
            border-radius: calc(var(--fc-daygrid-event-dot-width,8px)/ 2);
        }

        /* 3-Panel Layout Styles */
        .calendar-panel, .tour-filter-panel {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            height: fit-content;
            min-height: auto;
        }
        
        .calendar-panel {
            display: flex;
            flex-direction: column;
            height: fit-content;
        }
        
        .calendar-container {
            flex: 1;
            min-height: 450px !important;
            position: relative;
            margin-bottom: 0px;
        }
        
        /* Calendar Loading Overlay */
        .calendar-loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 12px;
        }
        
        .calendar-loading-overlay.show {
            display: flex;
        }
        
        .calendar-loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--thm-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            color: var(--thm-primary);
            font-weight: 600;
            font-size: 14px;
        }
        
        /* Force Litepicker to be large */
        #lite-datepicker {
            width: 100% !important;
            height: 100% !important;
        }
        
        .litepicker {
            width: 100% !important;
            height: 100% !important;
            font-size: 16px !important;
            transform: scale(1.05) !important;
            transform-origin: center !important;
        }
        
        .litepicker .container__main {
            width: 100% !important;
            height: 100% !important;
        }
        
        .litepicker .container__months {
            width: 100% !important;
            height: 100% !important;
            display: flex !important;
            justify-content: space-between !important;
        }
        
        .litepicker .container__months .month-item {
            flex: 1 !important;
            margin: 0 10px !important;
        }
        
        .litepicker .month-item-header {
            font-size: 20px !important;
            font-weight: bold !important;
            padding: 15px 0 !important;
        }
        
        .litepicker .month-item-weekdays-row {
            padding: 10px 0 !important;
        }
        
        .litepicker .month-item-weekdays-row > div {
            font-size: 14px !important;
            font-weight: 600 !important;
            padding: 8px 0 !important;
        }
        
        .litepicker .day-item {
            width: 45px !important;
            height: 45px !important;
            line-height: 45px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            margin: 2px !important;
            border-radius: 6px !important;
            transition: all 0.2s ease !important;
            position: relative !important;
            cursor: pointer !important;
        }
        
        /* Default white background for all dates */
        .litepicker .day-item {
            background-color: white !important;
            border: 2px solid #e9ecef !important;
            color: #495057 !important;
        }
        
        /* Booking Status Colors (Priority) */
        .litepicker .day-item.booking-accepted {
            background-color: #28a745 !important;
            border: 2px solid #28a745 !important;
            color: white !important;
        }
        
        .litepicker .day-item.booking-pending {
            background-color: #ffc107 !important;
            border: 2px solid #ffc107 !important;
            color: #000 !important;
        }
        
        .litepicker .day-item.booking-rejected {
            background-color: #dc3545 !important;
            border: 2px solid #dc3545 !important;
            color: white !important;
        }
        
        .litepicker .day-item.booking-cancelled {
            background-color: #6c757d !important;
            border: 2px solid #6c757d !important;
            color: white !important;
        }
        
        /* Custom Event Color (when no bookings) - High Priority */
        .litepicker .day-item.custom-event {
            background-color: #17a2b8 !important;
            border: 2px solid #17a2b8 !important;
            color: white !important;
        }
        
        /* Override availability colors when custom event is present */
        .litepicker .day-item.custom-event.tour-available,
        .litepicker .day-item.custom-event.tour-blocked {
            background-color: #17a2b8 !important;
            border: 2px solid #17a2b8 !important;
            color: white !important;
        }
        
        /* Blocked Tour Indicator (dot on upper right) */
        .litepicker .day-item.blocked-tour::after {
            content: '';
            position: absolute !important;
            top: 4px !important;
            right: 4px !important;
            width: 10px !important;
            height: 10px !important;
            background-color: #fd7e14 !important;
            border-radius: 50% !important;
            z-index: 10 !important;
        }
        
        .litepicker .day-item:hover {
            transform: scale(1.1) !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
            z-index: 5 !important;
        }
        
        .litepicker .day-item.is-selected,
        .litepicker .day-item.is-start-date,
        .litepicker .day-item.is-end-date {
            border: 3px solid #000 !important;
            transform: scale(1.1) !important;
            z-index: 10 !important;
            font-weight: bold !important;
            box-shadow: 0 0 0 1px #000 !important;
        }
        
        /* Previous/Next month buttons */
        .litepicker .button-previous-month,
        .litepicker .button-next-month {
            width: 40px !important;
            height: 40px !important;
            font-size: 18px !important;
            border-radius: 50% !important;
        }

        /* Updated Legend Styles */
        .calendar-legend {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .legend-color {
            width: 14px;
            height: 14px;
            border-radius: 3px;
            border: 1px solid;
        }
        
        .legend-accepted {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .legend-pending {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        
        .legend-rejected {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .legend-custom {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        
        .legend-blocked {
            background-color: white;
            border-color: #e9ecef;
            position: relative;
        }
        
        .legend-blocked::after {
            content: '';
            position: absolute;
            top: 1px;
            right: 1px;
            width: 5px;
            height: 5px;
            background-color: #fd7e14;
            border-radius: 50%;
        }
        
        .legend-available {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .legend-unavailable {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .legend-text {
            font-size: 13px;
            color: #6c757d;
        }

        /* Tour Filter Dropdown Styles with Scrolling */
        .dropdown-menu {
            max-height: 350px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }
        
        .tour-filter-card-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
        }
        
        .tour-filter-image-dropdown {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .tour-filter-thumbnail-dropdown {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .tour-filter-icon-dropdown {
            width: 40px;
            height: 40px;
            background: #313041;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .tour-filter-info-dropdown {
            flex: 1;
            min-width: 0;
        }
        
        .tour-filter-title-dropdown {
            font-size: 14px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .tour-filter-location-dropdown {
            font-size: 12px;
            color: #6c757d;
            display: block;
            margin-bottom: 2px;
        }
        
        .tour-filter-price-dropdown {
            font-size: 12px;
            color: #313041;
            font-weight: 600;
        }
        
        .tour-filter-subtitle-dropdown {
            font-size: 12px;
            color: #6c757d;
        }
        
        .tour-filter-option.active {
            background-color: rgba(49, 48, 65, 0.1);
        }

        /* Filter Controls */
        .filter-group {
            margin-bottom: 15px;
        }
        
        .filter-group .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #313041;
        }
        
        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 12px;
        }
        
        .filter-btn.active {
            background: #313041;
            color: white;
            border-color: #313041;
        }
        
        .filter-btn:hover {
            background: #313041;
            color: white;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Detail Panel Updates */
        .calendar-detail-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            max-height: 200px;
            opacity: 1;
        }
        
        .calendar-detail-panel.show {
            max-height: none;
            opacity: 1;
        }

        .detail-content {
            min-height: 200px;
        }

        .schedule-item {
            background: #f8f9fa;
            transition: all 0.2s ease;
        }

        .schedule-item:hover {
            background: #e9ecef;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .schedule-details {
            line-height: 1.6;
        }

        .schedule-details i {
            width: 16px;
            text-align: center;
            margin-right: 5px;
        }
        
        /* Detail Panel Styling */
        .calendar-detail-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
            max-height: 0;
            opacity: 0;
        }
        
        .calendar-detail-panel.show {
            max-height: 800px;
            opacity: 1;
        }
        
        .detail-panel-header {
            background: linear-gradient(135deg, #313041, #2c5aa0);
            color: white !important;
            padding: 20px;
            position: relative;
        }
        
        .detail-panel-header h4,
        .detail-panel-header p {
            color: white !important;
        }
        
        .detail-panel-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffd700, #ff6b6b, #4ecdc4, #45b7d1);
        }
        
        .detail-panel-body {
            padding: 25px;
        }
        
                .detail-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid #313041;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .detail-card h6 {
            color: #313041;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .detail-card-content {
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 5px;
        }

        .detail-card-content::-webkit-scrollbar {
            width: 6px;
        }

        .detail-card-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .detail-card-content::-webkit-scrollbar-thumb {
            background: #313041;
            border-radius: 3px;
        }

        .detail-card-content::-webkit-scrollbar-thumb:hover {
            background: #d63384;
        }
        
        .close-panel-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .close-panel-btn:hover {
            transform: scale(1.2);
        }
        
        .schedule-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.2s ease;
        }
        
        .schedule-item:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .schedule-details {
            margin-top: 10px;
        }
        
        .schedule-details small {
            display: block;
            margin-bottom: 5px;
            color: #495057 !important;
            font-weight: 500;
        }
        
        .schedule-details i {
            width: 16px;
            text-align: center;
            margin-right: 8px;
            color: #313041;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-accepted { background: #28a745; color: white; }
        .status-pending { background: #ffc107; color: #000; }
        .status-cancelled { background: #6c757d; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .status-blocked { background: #fd7e14; color: white; }
        .status-vacation { background: #6f42c1; color: white; }
        .status-custom { background: #20c997; color: white; }

        @media screen and (max-width:767px) { 
            .calendar-panel, .tour-filter-panel {
                padding: 15px;
                margin-bottom: 20px;
                min-height: 500px !important;
                height: auto;
            }
            
            .calendar-container {
                min-height: 400px !important;
            }
            
            .detail-card-content {
                max-height: 250px;
            }
            
            .detail-card {
                padding: 12px !important;
                margin-bottom: 15px !important;
            }
            
            .detail-card h6 {
                font-size: 16px !important;
                margin-bottom: 12px !important;
            }
            
            .d-flex.justify-content-between {
                flex-direction: column !important;
                align-items: flex-start !important;
            }
            
            .d-flex.justify-content-between .status-badge,
            .d-flex.justify-content-between .badge {
                align-self: flex-end !important;
                margin-top: 5px !important;
            }
            
            .litepicker {
                transform: scale(1) !important;
                font-size: 14px !important;
            }
            
            .litepicker .container__months {
                flex-direction: column !important;
            }
            
            .litepicker .container__months .month-item {
                margin: 10px 0 !important;
            }
            
            .litepicker .day-item {
                width: 36px !important;
                height: 36px !important;
                line-height: 36px !important;
                font-size: 14px !important;
                margin: 1px !important;
            }
            
            .litepicker .month-item-header {
                font-size: 16px !important;
                padding: 10px 0 !important;
            }
            
            .col-md-8, .col-md-4 {
                width: 100%;
                max-width: 100%;
                flex: 0 0 100%;
            }
            
            .legend-items {
                gap: 8px;
                justify-content: flex-start;
            }
            
            .filter-buttons {
                gap: 5px;
            }
            
            .filter-btn {
                padding: 4px 8px;
                font-size: 11px;
            }
            
            .quick-actions {
                justify-content: center;
            }
            
            .calendar-detail-panel {
                margin-top: 15px;
            }
            
            .detail-panel-header h4 {
                font-size: 1.1rem;
            }
            
            .tour-filter-card-dropdown {
                padding: 6px 0;
                gap: 8px;
            }
            
            .tour-filter-image-dropdown,
            .tour-filter-icon-dropdown {
                width: 35px;
                height: 35px;
            }
            
            .schedule-item {
                padding: 12px !important;
                margin-bottom: 10px !important;
            }
            
            .schedule-item h6 {
                font-size: 14px !important;
                margin-bottom: 8px !important;
            }
            
            .schedule-details small {
                font-size: 12px !important;
                margin-bottom: 3px !important;
            }
            
            .status-badge {
                font-size: 10px !important;
                padding: 2px 8px !important;
            }
            
            .btn-sm {
                font-size: 11px !important;
                padding: 4px 8px !important;
            }
            
            .calendar-panel {
                display: block;
            }
        }
    </style>
@stop

@section('profile-content')

<div class="container" style="margin-bottom: 20px;">
    <!-- 3-Panel Layout -->
    <div class="row">
        <!-- Top Left Panel: Calendar -->
        <div class="col-md-8">
            <div class="calendar-panel">
                <!-- Calendar Container -->
                <div class="calendar-container">
                    <div id="lite-datepicker"></div>
                    <!-- Loading Overlay -->
                    <div id="calendarLoadingOverlay" class="calendar-loading-overlay">
                        <div class="calendar-loading-spinner">
                            <div class="spinner"></div>
                            <div class="loading-text">Applying calendar colors...</div>
                        </div>
                    </div>
                </div>
                
                <!-- Calendar Legend - Below Calendar -->
                <div class="calendar-legend mt-1">
                    <div class="legend-items" id="calendarLegend">
                        <div class="legend-item">
                            <div class="legend-color legend-accepted"></div>
                            <span class="legend-text">@lang('profile.confirmed')</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-pending"></div>
                            <span class="legend-text">@lang('profile.pending')</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-rejected"></div>
                            <span class="legend-text">@lang('profile.rejected')</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-custom"></div>
                            <span class="legend-text">@lang('profile.custom')</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color legend-blocked"></div>
                            <span class="legend-text">@lang('profile.blocked')</span>
                        </div>
                        <div class="legend-item" id="availableLegend" style="display: none;">
                            <div class="legend-color legend-available"></div>
                            <span class="legend-text">@lang('profile.available')</span>
                        </div>
                        <div class="legend-item" id="unavailableLegend" style="display: none;">
                            <div class="legend-color legend-unavailable"></div>
                            <span class="legend-text">@lang('profile.unavailable')</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Right Panel: Tour Filter -->
        <div class="col-md-4">
            <div class="tour-filter-panel d-flex flex-column">
                <div class="flex-grow-1">
                    <h3 class="mb-3">@lang('profile.select-tour-to-filter')</h3>
                    
                    <!-- Tour Filter Dropdown -->
                    <div class="dropdown mb-3">
                        <button class="btn btn-outline-primary dropdown-toggle w-100" type="button" id="tourFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <span id="selectedTourText">@lang('profile.all-tours')</span>
                        </button>
                        <ul class="dropdown-menu w-100" aria-labelledby="tourFilterDropdown" id="tourDropdownMenu">
                            <!-- All Tours Option -->
                            <li>
                                <a class="dropdown-item tour-filter-option active" href="#" data-guiding-id="">
                                    <div class="tour-filter-card-dropdown">
                                        <div class="tour-filter-icon-dropdown">
                                            <i class="fas fa-list"></i>
                                        </div>
                                        <div class="tour-filter-info-dropdown">
                                            <div class="tour-filter-title-dropdown">@lang('profile.all-tours')</div>
                                            <small class="tour-filter-subtitle-dropdown">@lang('profile.show-all-events')</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            
                            @foreach($userGuidings as $guiding)
                                <li>
                                    <a class="dropdown-item tour-filter-option" href="#" data-guiding-id="{{ $guiding->id }}">
                                        <div class="tour-filter-card-dropdown">
                                            <div class="tour-filter-image-dropdown">
                                                @if(get_featured_image_link($guiding))
                                                    <img src="{{get_featured_image_link($guiding)}}" alt="{{ $guiding->title }}" class="tour-filter-thumbnail-dropdown">
                                                @else
                                                    <img src="{{asset('images/placeholder_guide.webp')}}" alt="{{ $guiding->title }}" class="tour-filter-thumbnail-dropdown">
                                                @endif
                                            </div>
                                            <div class="tour-filter-info-dropdown">
                                                <div class="tour-filter-title-dropdown">{{ Str::limit($guiding->title, 25) }}</div>
                                                <small class="tour-filter-location-dropdown">{{ Str::limit($guiding->location, 20) }}</small>
                                                <small class="tour-filter-price-dropdown">{{ $guiding->getLowestPrice() }}€</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                
                <!-- Filter Controls -->
                {{-- <div class="filter-controls">
                    <div class="filter-group mb-3">
                        <label class="form-label">@lang('profile.filter-by-type'):</label>
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-type="">@lang('profile.all')</button>
                            <button class="filter-btn" data-type="tour_request">@lang('profile.bookings')</button>
                            <button class="filter-btn" data-type="tour_schedule">@lang('profile.blocked')</button>
                            <button class="filter-btn" data-type="vacation_schedule">@lang('profile.vacation')</button>
                            <button class="filter-btn" data-type="custom_schedule">@lang('profile.custom')</button>
                        </div>
                    </div>
                    
                    <div class="filter-group mb-3">
                        <label class="form-label">@lang('profile.filter-by-status'):</label>
                        <div class="filter-buttons">
                            <button class="filter-btn active" data-status="">@lang('profile.all')</button>
                            <button class="filter-btn" data-status="accepted">@lang('profile.confirmed')</button>
                            <button class="filter-btn" data-status="pending">@lang('profile.pending')</button>
                            <button class="filter-btn" data-status="cancelled">@lang('profile.cancelled')</button>
                            <button class="filter-btn" data-status="rejected">@lang('profile.rejected')</button>
                        </div>
                    </div>
                </div>--}}
                
                <!-- Quick Actions at Bottom -->
                <div class="mt-auto">
                    <div class="quick-actions">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="fas fa-plus"></i> @lang('profile.add-event')
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="refreshCalendar()">
                            <i class="fas fa-sync"></i> @lang('profile.refresh')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Panel: Details Display -->
    <div class="row mt-4">
        <div class="col-12">
            <div id="detailPanel" class="calendar-detail-panel">
                            <div class="detail-panel-header">
                <button class="close-panel-btn" onclick="closeDetailPanel()">&times;</button>
                    <h4 id="detailPanelTitle">Schedule for Date</h4>
                    <p id="detailPanelDate" class="mb-0"></p>
            </div>
            <div class="detail-panel-body">
                    <div id="detailPanelContent" class="detail-content">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                            <p>Click on a date to view schedule details</p>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Blockade Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel">@lang('profile.add-calendar-event')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEventForm">
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
                        
                        <div class="col-12 mt-3">
                            <div class="form-group">
                                <label for="eventType">@lang('profile.event-type')</label>
                                <select id="eventType" class="form-select" name="type" required>
                                    <option value="custom_schedule">@lang('profile.custom-event')</option>
                                    <option value="vacation_schedule">@lang('profile.vacation')</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-3">
                            <div class="form-group">
                                <label for="eventNote">@lang('profile.note')</label>
                                <input type="text" id="eventNote" class="form-control" name="note" placeholder="@lang('profile.event-description')">
                            </div>
                        </div>
                        
                        <div class="col-12 mt-3">
                            <div class="form-group">
                                <label for="eventGuiding">@lang('profile.associate-with-tour') (@lang('profile.optional'))</label>
                                <select id="eventGuiding" class="form-select" name="guiding_id">
                                    <option value="">@lang('profile.no-tour')</option>
                                    @foreach($userGuidings as $guiding)
                                        <option value="{{ $guiding->id }}">{{ $guiding->title }} - {{ $guiding->location }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12 my-3">
                            <span class="color-primary">@lang('profile.block-by-weekday') (@lang('profile.optional'))</span>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" value="1">
                                            @lang('message.monday')
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" value="2">
                                            @lang('message.tuesday')
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" value="3">
                                            @lang('message.wednesday')
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" value="4">
                                            @lang('message.thursday')
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" value="5">
                                            @lang('message.friday')
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" value="6">
                                            @lang('message.saturday')
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="day[]" value="7">
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
                <button type="button" id="confirmDelete" class="btn btn-danger">@lang('profile.clearBD')</button>
            </div>
        </div>
    </div>
</div>

<!-- Booking Detail Modal -->
<div class="modal fade" id="bookingDetailModal" tabindex="-1" aria-labelledby="bookingDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">@lang('message.booking-overview')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="card border-0">
                <div class="card-body mx-4">
                    <div id="bookingDetails">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">@lang('profile.close')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white">@lang('profile.event-details')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="card border-0">
                <div class="card-body mx-4">
                    <div id="eventDetails">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">@lang('profile.close')</button>
                        <button type="button" id="deleteEventBtn" class="btn btn-danger ms-2" style="display: none;">@lang('profile.delete')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom_style')
<style>
        /* Subtle Tour Filter Styles */
        .tours-filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .tours-slider-container {
            position: relative;
        }

        .tours-slider.owl-carousel {
            margin: 0;
        }

        .tour-filter-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 3px;
            height: 70px;
            display: flex;
            align-items: center;
        }

        .tour-filter-card:hover {
            border-color: #ced4da;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .tour-filter-card.active {
            border-color: #313041;
            background: rgba(49, 48, 65, 0.05);
            box-shadow: 0 2px 12px rgba(49, 48, 65, 0.2);
        }

        .tour-filter-content {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }

        .tour-filter-image {
            width: 45px;
            height: 45px;
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .tour-filter-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tour-filter-icon {
            width: 45px;
            height: 45px;
            background: #313041;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            flex-shrink: 0;
        }

        .tour-filter-info {
            flex: 1;
            min-width: 0;
        }

        .tour-filter-title {
            font-size: 13px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tour-filter-location {
            font-size: 11px;
            color: #6c757d;
            display: block;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tour-filter-price {
            font-size: 11px;
            color: #313041;
            font-weight: 600;
        }

        .tour-filter-subtitle {
            font-size: 11px;
            color: #6c757d;
        }

        /* Owl Carousel minimal navigation */
        .tours-slider .owl-nav {
            margin: 0;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 100%;
            display: flex;
            justify-content: space-between;
            pointer-events: none;
        }

        .tours-slider .owl-nav button {
            background: rgba(255,255,255,0.9) !important;
            color: #6c757d !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            font-size: 12px;
            margin: 0;
            pointer-events: all;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .tours-slider .owl-nav button:hover {
            background: white !important;
            color: #313041 !important;
            border-color: #313041 !important;
        }

        .tours-slider .owl-nav .owl-prev {
            left: -15px;
        }

        .tours-slider .owl-nav .owl-next {
            right: -15px;
        }

        .tours-slider .owl-dots {
            text-align: center;
            margin-top: 8px;
        }

        .tours-slider .owl-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #dee2e6;
            border-radius: 50%;
            margin: 0 3px;
            transition: all 0.2s ease;
        }

        .tours-slider .owl-dot.active {
            background: #313041;
            width: 20px;
            border-radius: 10px;
        }
        
        /* Button Primary Styling */
        .btn-primary {
            background-color: #313041 !important;
            border-color: #313041 !important;
            color: white !important;
        }
        
        .btn-primary:hover {
            background-color: #2a2938 !important;
            border-color: #2a2938 !important;
            color: white !important;
        }
        
        .btn-primary:focus,
        .btn-primary.focus {
            background-color: #2a2938 !important;
            border-color: #2a2938 !important;
            box-shadow: 0 0 0 0.2rem rgba(49, 48, 65, 0.5) !important;
        }
        
        .btn-primary:not(:disabled):not(.disabled):active,
        .btn-primary:not(:disabled):not(.disabled).active {
            background-color: #232230 !important;
            border-color: #232230 !important;
        }
        
        /* Tour Availability Colors */
        .litepicker .day-item.tour-available {
            background-color: #28a745 !important;
            border: 2px solid #28a745 !important;
            color: white !important;
        }
        
        .litepicker .day-item.tour-blocked {
            background-color: #dc3545 !important;
            border: 2px solid #dc3545 !important;
            color: white !important;
        }
        
        /* Higher specificity for custom events to always override availability */
        .litepicker .day-item.custom-event {
            background-color: #17a2b8 !important;
            border: 2px solid #17a2b8 !important;
            color: white !important;
        }
</style>
@endsection

@section('js_after')
    <!-- Litepicker JS -->
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
    <script>
        let picker;
        let currentFilters = {
            guiding_id: '',
            type: '',
            status: ''
        };
        let calendarEvents = {};
        let selectedDate = null;

        document.addEventListener('DOMContentLoaded', function() {
            @if(app()->getLocale() == 'de')
                var locale = 'de';
            @elseif(app()->getLocale() == 'en')
                var locale = 'en';
            @endif

            // Initialize calendar
            initializeCalendar();
            
            // Initialize filters
            initializeFilters();
            
            // Initialize forms
            initializeForms();
            
            // Initialize tour dropdown
            initializeTourDropdown();
            
            // Load initial events
            loadCalendarEvents();
        });

        function initializeCalendar() {
            // Get blocked events for calendar
            const blockedEvents = @json($blocked_events ?? []);
            console.log(blockedEvents);
            let lockDays = [];
            
            if (blockedEvents && typeof blockedEvents === 'object') {
                lockDays = Object.values(blockedEvents).flatMap(event => {
                    const fromDate = new Date(event.from);
                    const dueDate = new Date(event.due);
                    
                    const dates = [];
                    for (let d = new Date(fromDate); d <= dueDate; d.setDate(d.getDate() + 1)) {
                        dates.push(d.toISOString().split('T')[0]);
                    }
                    return dates;
                });
            }

            picker = new Litepicker({
                element: document.getElementById('lite-datepicker'),
                inlineMode: true,
                singleDate: true,
                numberOfColumns: window.innerWidth < 768 ? 1 : 2,
                numberOfMonths: window.innerWidth < 768 ? 1 : 2,
                lang: '{{app()->getLocale()}}',
                lockDaysFormat: 'YYYY-MM-DD',
                disallowLockDaysInRange: false,
                allowRepick: true,
                autoRefresh: true,
                setup: (picker) => {
                    window.addEventListener('resize', () => {
                        picker.setOptions({
                            numberOfColumns: window.innerWidth < 768 ? 1 : 2,
                            numberOfMonths: window.innerWidth < 768 ? 1 : 2
                        });
                        // Reapply styles after resize
                        setTimeout(updateCalendarDisplay, 500);
                    });
                },
                onSelect: (date1, date2) => {
                    if (date1) {
                        let selectedDateStr;
                        
                        try {
                            if (typeof date1 === 'string') {
                                selectedDateStr = date1;
                            } else if (date1.toISOString) {
                                selectedDateStr = date1.toISOString().split('T')[0];
                            } else if (date1.format) {
                                selectedDateStr = date1.format('YYYY-MM-DD');
                            } else if (date1.dateInstance) {
                                selectedDateStr = date1.dateInstance.toISOString().split('T')[0];
                            } else {
                                const newDate = new Date(date1);
                                selectedDateStr = newDate.toISOString().split('T')[0];
                            }
                            
                            selectedDate = selectedDateStr;
                            showDayDetails(selectedDateStr);
                        } catch (error) {
                            console.error('Error formatting selected date:', error, date1);
                        }
                    }
                },
                onShow: () => {
                    setTimeout(updateCalendarDisplay, 1000);
                },
                onChangeMonth: () => {
                    setTimeout(updateCalendarDisplay, 500);
                },
                onRender: () => {
                    setTimeout(updateCalendarDisplay, 500);
                }
            });

            // Additional fallback for date selection and color monitoring
            setTimeout(() => {
                const calendarContainer = document.getElementById('lite-datepicker');
                if (calendarContainer) {
                    // Click fallback
                    calendarContainer.addEventListener('click', function(event) {
                        const dayElement = event.target.closest('.day-item');
                        if (dayElement && !dayElement.classList.contains('is-locked')) {
                            const dayText = dayElement.textContent.trim();
                            if (dayText && /^\d+$/.test(dayText)) {
                                const monthContainer = dayElement.closest('.month-item');
                                if (monthContainer) {
                                    setTimeout(() => {
                                        const currentDate = picker.getDate();
                                        if (currentDate) {
                                            let dateStr;
                                            if (typeof currentDate === 'string') {
                                                dateStr = currentDate;
                                            } else if (currentDate.format) {
                                                dateStr = currentDate.format('YYYY-MM-DD');
                                            } else {
                                                dateStr = currentDate.toISOString().split('T')[0];
                                            }
                                            selectedDate = dateStr;
                                            showDayDetails(dateStr);
                                        }
                                    }, 100);
                                }
                            }
                        }
                    });
                    
                    // Monitor DOM changes to reapply colors when calendar re-renders
                    const observer = new MutationObserver(function(mutations) {
                        let shouldUpdate = false;
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'childList') {
                                const addedNodes = Array.from(mutation.addedNodes);
                                if (addedNodes.some(node => 
                                    node.nodeType === Node.ELEMENT_NODE && 
                                    (node.classList?.contains('day-item') || 
                                     node.querySelector?.('.day-item') ||
                                     node.classList?.contains('month-item') ||
                                     node.querySelector?.('.month-item'))
                                )) {
                                    shouldUpdate = true;
                                }
                            }
                        });
                        
                        if (shouldUpdate) {
                            setTimeout(updateCalendarDisplay, 300);
                        }
                    });
                    
                    observer.observe(calendarContainer, {
                        childList: true,
                        subtree: true
                    });
                }
            }, 1000);
        }

        function loadCalendarEvents() {
            const startDate = new Date();
            startDate.setMonth(startDate.getMonth() - 6); // Load 6 months back
            const endDate = new Date();
            endDate.setMonth(endDate.getMonth() + 6); // Load 6 months forward
            
            fetch('/events?' + new URLSearchParams({
                start: startDate.toISOString().split('T')[0],
                end: endDate.toISOString().split('T')[0],
                ...currentFilters
            }))
            .then(response => {
                return response.json();
            })
            .then(data => {
                calendarEvents = {};
                
                if (Array.isArray(data)) {
                    data.forEach(event => {
                        const dateKey = event.start ? event.start.split('T')[0] : event.date;
                        if (dateKey) {
                            if (!calendarEvents[dateKey]) {
                                calendarEvents[dateKey] = [];
                            }
                            calendarEvents[dateKey].push(event);
                        }
                    });
                } else {
                    console.error('Events data is not an array:', data);
                }
                
                updateCalendarDisplay();
            })
            .catch(error => {
                console.error('Error loading calendar events:', error);
            });
        }

        function updateCalendarDisplay() {
            // Show loading overlay
            const loadingOverlay = document.getElementById('calendarLoadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('show');
            }
            
            setTimeout(() => {
                // First, completely reset ALL day elements
                const allDayElements = document.querySelectorAll('.day-item');
                
                allDayElements.forEach(dayEl => {
                    // Remove ALL possible color classes
                    dayEl.classList.remove('booking-accepted', 'booking-pending', 'booking-rejected', 'booking-cancelled', 'custom-event', 'blocked-tour', 'tour-available', 'tour-blocked');
                    // Reset to default white background
                    dayEl.style.backgroundColor = '';
                    dayEl.style.border = '';
                    dayEl.style.color = '';
                    dayEl.style.cursor = 'pointer';
                });
                
                // Apply default availability colors if a specific tour is selected
                // This will be overridden by specific events later
                if (currentFilters.guiding_id !== '') {
                    allDayElements.forEach(dayEl => {
                        if (dayEl.textContent.trim() && /^\d+$/.test(dayEl.textContent.trim())) {
                            const dayNumber = parseInt(dayEl.textContent.trim());
                            const monthContainer = dayEl.closest('.month-item');
                            
                            if (monthContainer) {
                                const monthHeader = monthContainer.querySelector('.month-item-header div');
                                if (monthHeader) {
                                    const headerText = monthHeader.textContent.trim();
                                    
                                    // Extract year and month from header
                                    const yearMatch = headerText.match(/\d{4}/);
                                    const year = yearMatch ? parseInt(yearMatch[0]) : new Date().getFullYear();
                                    
                                    // Map month names to numbers (for multiple languages)
                                    const monthNames = {
                                        'january': 0, 'januar': 0,
                                        'february': 1, 'februar': 1,
                                        'march': 2, 'märz': 2,
                                        'april': 3,
                                        'may': 4, 'mai': 4,
                                        'june': 5, 'juni': 5,
                                        'july': 6, 'juli': 6,
                                        'august': 7,
                                        'september': 8,
                                        'october': 9, 'oktober': 9,
                                        'november': 10,
                                        'december': 11, 'dezember': 11
                                    };
                                    
                                    let monthNumber = -1;
                                    Object.keys(monthNames).forEach(monthName => {
                                        if (headerText.toLowerCase().includes(monthName)) {
                                            monthNumber = monthNames[monthName];
                                        }
                                    });
                                    
                                    if (monthNumber !== -1) {
                                        const currentDate = new Date(year, monthNumber, dayNumber);
                                        const today = new Date();
                                        today.setHours(0, 0, 0, 0);
                                        
                                        // Apply default availability colors (will be overridden by events)
                                        if (currentDate < today) {
                                            dayEl.classList.add('tour-blocked');
                                        } else {
                                            dayEl.classList.add('tour-available');
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Get all month containers for better date matching
                const monthContainers = document.querySelectorAll('.month-item');
                
                // Get currently visible months from the calendar
                const visibleMonths = [];
                monthContainers.forEach(monthContainer => {
                    const monthHeader = monthContainer.querySelector('.month-item-header div');
                    if (monthHeader) {
                        const headerText = monthHeader.textContent.trim();
                        visibleMonths.push(headerText);
                    }
                });

                Object.keys(calendarEvents).forEach(dateKey => {
                    const events = calendarEvents[dateKey];
                    const date = new Date(dateKey);
                    const dayNumber = date.getDate();
                    const monthName = date.toLocaleDateString('en-US', { month: 'long' });
                    const year = date.getFullYear();
                    
                    // Check if this event date is for a currently visible month
                    const isMonthVisible = visibleMonths.some(visibleMonth => {
                        // Handle different month name formats (Juni vs June, etc.)
                        const cleanVisible = visibleMonth.toLowerCase().replace(/\s+/g, '');
                        const targetMonth = monthName.toLowerCase();
                        const targetYear = year.toString();
                        
                        // Check various month name variations
                        const monthVariations = {
                            'january': ['januar', 'january'],
                            'february': ['februar', 'february'],
                            'march': ['märz', 'march'],
                            'april': ['april'],
                            'may': ['mai', 'may'],
                            'june': ['juni', 'june'],
                            'july': ['juli', 'july'],
                            'august': ['august'],
                            'september': ['september'],
                            'october': ['oktober', 'october'],
                            'november': ['november'],
                            'december': ['dezember', 'december']
                        };
                        
                        const possibleNames = monthVariations[targetMonth] || [targetMonth];
                        
                        return possibleNames.some(name => 
                            cleanVisible.includes(name) && cleanVisible.includes(targetYear)
                        );
                    });
                    
                    if (!isMonthVisible) {
                        return;
                    }
                    
                    
                    let foundDayElements = [];
                    
                    // Strategy: Find the correct month container first, then find the day
                    monthContainers.forEach(monthContainer => {
                        const monthHeader = monthContainer.querySelector('.month-item-header div');
                        if (monthHeader) {
                            const headerText = monthHeader.textContent.trim();
                            
                            // More flexible month matching
                            const cleanHeader = headerText.toLowerCase().replace(/\s+/g, '');
                            const targetMonth = monthName.toLowerCase();
                            const targetYear = year.toString();
                            
                            const monthVariations = {
                                'january': ['januar', 'january'],
                                'february': ['februar', 'february'],
                                'march': ['märz', 'march'],
                                'april': ['april'],
                                'may': ['mai', 'may'],
                                'june': ['juni', 'june'],
                                'july': ['juli', 'july'],
                                'august': ['august'],
                                'september': ['september'],
                                'october': ['oktober', 'october'],
                                'november': ['november'],
                                'december': ['dezember', 'december']
                            };
                            
                            const possibleNames = monthVariations[targetMonth] || [targetMonth];
                            const monthMatches = possibleNames.some(name => cleanHeader.includes(name));
                            const yearMatches = cleanHeader.includes(targetYear);
                            
                            if (monthMatches && yearMatches) {
                                
                                // Now find the day within this month
                                const dayElements = monthContainer.querySelectorAll('.day-item');
                                dayElements.forEach(dayEl => {
                                    if (dayEl.textContent.trim() === dayNumber.toString()) {
                                        foundDayElements.push(dayEl);
                                    }
                                });
                            }
                        }
                    });
                    
                    if (foundDayElements.length === 0) {
                        return;
                    }
                    
                    // Apply colors to found elements
                    foundDayElements.forEach((dayEl, index) => {
                        
                        // Categorize events
                        const bookings = events.filter(e => e.extendedProps && e.extendedProps.booking);
                        const blockedTours = events.filter(e => e.extendedProps && e.extendedProps.type && 
                            (e.extendedProps.type === 'tour_schedule' || e.extendedProps.type === 'vacation_schedule'));
                        const customEvents = events.filter(e => e.extendedProps && e.extendedProps.type === 'custom_schedule');
                        
                        // Remove ALL classes first, then apply the correct one
                        dayEl.classList.remove('booking-accepted', 'booking-pending', 'booking-rejected', 'booking-cancelled', 'custom-event', 'blocked-tour', 'tour-available', 'tour-blocked');
                        
                        // Apply priority-based coloring (these override availability colors)
                        // Priority Order: 1. Bookings, 2. Custom Events, 3. Blocked Tours, 4. Availability
                        
                        if (bookings.length > 0) {
                            // HIGHEST PRIORITY: Bookings override everything
                            const statuses = bookings.map(b => b.extendedProps.booking.status);
                            
                            if (statuses.includes('accepted')) {
                                dayEl.classList.add('booking-accepted');
                            } else if (statuses.includes('pending')) {
                                dayEl.classList.add('booking-pending');
                            } else if (statuses.includes('rejected')) {
                                dayEl.classList.add('booking-rejected');
                            } else if (statuses.includes('cancelled')) {
                                dayEl.classList.add('booking-cancelled');
                            }
                        } else if (customEvents.length > 0) {
                            // SECOND PRIORITY: Custom events override everything except bookings
                            // Force remove availability classes and add custom event
                            dayEl.classList.remove('tour-available', 'tour-blocked');
                            dayEl.classList.add('custom-event');

                        } else {
                            // Apply availability or blocked status based on tour selection and other events
                            if (currentFilters.guiding_id !== '') {
                                if (blockedTours.length > 0) {
                                    dayEl.classList.add('tour-blocked');
                                } else {
                                    // Check if this element already has availability colors from the first pass
                                    if (!dayEl.classList.contains('tour-available') && !dayEl.classList.contains('tour-blocked')) {
                                        // Apply availability logic here if needed
                                        const currentDate = new Date(dateKey);
                                        const today = new Date();
                                        today.setHours(0, 0, 0, 0);
                                        
                                        if (currentDate < today) {
                                            dayEl.classList.add('tour-blocked');
                                        } else {
                                            dayEl.classList.add('tour-available');
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Add blocked tour indicator (only if not viewing "all tours")
                        if (blockedTours.length > 0 && currentFilters.guiding_id !== '') {
                            dayEl.classList.add('blocked-tour');
                        }
                        
                        // Ensure clickability
                        dayEl.style.cursor = 'pointer';
                    });
                });
                
                // Hide loading overlay
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('show');
                }
            }, 1500);
        }

        function refreshCalendar() {
            loadCalendarEvents();
        }

        function initializeFilters() {
            // Type filter buttons
            document.querySelectorAll('[data-type]').forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all type buttons
                    document.querySelectorAll('[data-type]').forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    // Update filter
                    currentFilters.type = this.dataset.type;
                    loadCalendarEvents();
                });
            });

            // Status filter buttons
            document.querySelectorAll('[data-status]').forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all status buttons
                    document.querySelectorAll('[data-status]').forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    // Update filter
                    currentFilters.status = this.dataset.status;
                    loadCalendarEvents();
                });
            });
        }

        function initializeTourDropdown() {
            // Handle tour filter dropdown selection
            document.querySelectorAll('.tour-filter-option').forEach(option => {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all options
                    document.querySelectorAll('.tour-filter-option').forEach(opt => opt.classList.remove('active'));
                    // Add active class to clicked option
                    this.classList.add('active');
                    
                    // Update filter
                    const guidingId = this.dataset.guidingId;
                    currentFilters.guiding_id = guidingId;
                    
                    // Update dropdown button text
                    const selectedText = guidingId ? 
                        this.querySelector('.tour-filter-title-dropdown').textContent : 
                        '@lang('profile.all-tours')';
                    document.getElementById('selectedTourText').textContent = selectedText;
                    
                    // Show/hide availability legend items
                    const availableLegend = document.getElementById('availableLegend');
                    const unavailableLegend = document.getElementById('unavailableLegend');
                    
                    if (guidingId) {
                        // Show availability legend when a specific tour is selected
                        availableLegend.style.display = 'flex';
                        unavailableLegend.style.display = 'flex';
                    } else {
                        // Hide availability legend when "All Tours" is selected
                        availableLegend.style.display = 'none';
                        unavailableLegend.style.display = 'none';
                    }
                    
                    // Refresh calendar
                    loadCalendarEvents();
                    
                    // Close dropdown
                    const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('tourFilterDropdown'));
                    if (dropdown) {
                        dropdown.hide();
                    }
                });
            });
        }

        function showDayDetails(dateStr) {            
            const panel = document.getElementById('detailPanel');
            const title = document.getElementById('detailPanelTitle');
            const dateElement = document.getElementById('detailPanelDate');
            const content = document.getElementById('detailPanelContent');
            
            // Format date for display
            const date = new Date(dateStr);
            const formattedDate = date.toLocaleDateString('{{app()->getLocale()}}', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            title.textContent = 'Schedule for Date';
            dateElement.textContent = formattedDate;
            
            // Get events for this date
            const dayEvents = calendarEvents[dateStr] || [];
            
            if (dayEvents.length === 0) {
                content.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-check fa-2x mb-3"></i>
                        <p>No events scheduled for this date</p>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                    </div>
                `;
            } else {
                let contentHtml = '<div class="row">';
                
                // Group events by type
                const bookings = dayEvents.filter(e => e.extendedProps && e.extendedProps.booking);
                const blockedTours = dayEvents.filter(e => e.extendedProps && e.extendedProps.type && 
                    (e.extendedProps.type === 'tour_schedule' || e.extendedProps.type === 'vacation_schedule'));
                const customEvents = dayEvents.filter(e => e.extendedProps && e.extendedProps.type === 'custom_schedule');
                const otherEvents = dayEvents.filter(e => !e.extendedProps || (!e.extendedProps.booking && 
                    e.extendedProps.type !== 'tour_schedule' && e.extendedProps.type !== 'vacation_schedule' && 
                    e.extendedProps.type !== 'custom_schedule'));
                
                // Display bookings
                if (bookings.length > 0) {
                    contentHtml += `
                        <div class="col-md-6">
                            <div class="detail-card">
                                <h6><i class="fas fa-calendar-check"></i> Bookings (${bookings.length})</h6>
                                <div class="detail-card-content">
                    `;
                    
                    bookings.forEach(event => {
                        const booking = event.extendedProps.booking;
                        const user = event.extendedProps.user;
                        const guiding = event.extendedProps.guiding;
                        
                        contentHtml += `
                            <div class="schedule-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">${guiding ? guiding.title : 'Tour Booking'}</h6>
                                    <span class="status-badge status-${booking.status}">${booking.status}</span>
                                </div>
                                <div class="schedule-details">
                                    <small><i class="fas fa-user"></i> ${user ? user.firstname + ' ' + user.lastname : 'Guest User'}</small>
                                    <small><i class="fas fa-users"></i> ${booking.count_of_users} guests</small>
                                    <small><i class="fas fa-euro-sign"></i> ${booking.price}€</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary mt-2" onclick="showBookingDetails('${booking.id}')">
                                    View Details
                                </button>
                            </div>
                        `;
                    });
                    
                    contentHtml += '</div></div></div>';
                }
                
                // Display blocked tours
                if (blockedTours.length > 0) {
                    contentHtml += `
                        <div class="col-md-6">
                            <div class="detail-card">
                                <h6><i class="fas fa-ban"></i> Blocked Tours (${blockedTours.length})</h6>
                                <div class="detail-card-content">
                    `;
                    
                    blockedTours.forEach(event => {
                        const type = getTypeLabel(event.extendedProps.type);
                        const canDelete = event.extendedProps.canDelete;
                        const guiding = event.extendedProps.guiding;
                        
                        contentHtml += `
                            <div class="schedule-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">${event.title || (guiding ? guiding.title : 'Blocked Tour')}</h6>
                                    <span class="badge bg-warning">${type}</span>
                                </div>
                                <div class="schedule-details">
                                    <small><i class="fas fa-clock"></i> ${event.extendedProps.date || dateStr}</small>
                                    ${event.extendedProps.note ? `<small><i class="fas fa-note-sticky"></i> ${event.extendedProps.note}</small>` : ''}
                                    ${guiding ? `
                                        <small><i class="fas fa-map-marker-alt"></i> ${guiding.location}</small>
                                        <small><i class="fas fa-fishing"></i> ${guiding.title}</small>
                                        <small><i class="fas fa-users"></i> Max ${guiding.max_guests} guests</small>
                                        <small><i class="fas fa-clock"></i> ${guiding.duration} hours</small>
                                        <small><i class="fas fa-euro-sign"></i> From ${guiding.price}€</small>
                                    ` : ''}
                                </div>
                                ${canDelete ? `<button class="btn btn-sm btn-outline-danger mt-2" onclick="deleteEvent('${event.extendedProps.scheduleId}')"><i class="fas fa-trash"></i> Delete</button>` : ''}
                            </div>
                        `;
                    });
                    
                    contentHtml += '</div></div></div>';
                }
                
                // Display custom events
                if (customEvents.length > 0) {
                    contentHtml += `
                        <div class="col-md-6 mb-4">
                            <div class="detail-card">
                                <h6><i class="fas fa-calendar-plus text-info"></i> @lang('profile.custom-events') (${customEvents.length})</h6>
                                <div class="detail-card-content">
                    `;
                    
                    customEvents.forEach(event => {
                        const type = getTypeLabel(event.extendedProps.type);
                        const canDelete = event.extendedProps.canDelete;
                        
                        contentHtml += `
                            <div class="schedule-item mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">${event.title || 'Custom Event'}</h6>
                                    <span class="badge bg-info">${type}</span>
                                </div>
                                <div class="schedule-details">
                                    <small class="text-muted d-block">
                                        <i class="fas fa-clock"></i> ${event.extendedProps.date || dateStr}
                                    </small>
                                    ${event.extendedProps.note ? `<small class="text-muted d-block"><i class="fas fa-note-sticky"></i> ${event.extendedProps.note}</small>` : ''}
                                </div>
                                ${canDelete ? `<button class="btn btn-sm btn-outline-danger mt-2" onclick="deleteEvent('${event.extendedProps.scheduleId}')"><i class="fas fa-trash"></i> @lang('profile.delete')</button>` : ''}
                            </div>
                        `;
                    });
                    
                    contentHtml += '</div></div></div>';
                }
                
                // Display other events if any
                if (otherEvents.length > 0) {
                    contentHtml += `
                        <div class="col-md-6 mb-4">
                            <div class="detail-card">
                                <h6><i class="fas fa-calendar text-secondary"></i> @lang('profile.other-events') (${otherEvents.length})</h6>
                                <div class="detail-card-content">
                    `;
                    
                    otherEvents.forEach(event => {
                        const type = event.extendedProps ? getTypeLabel(event.extendedProps.type) : 'Event';
                        const canDelete = event.extendedProps ? event.extendedProps.canDelete : false;
                        
                        contentHtml += `
                            <div class="schedule-item mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-1">${event.title || 'Event'}</h6>
                                    <span class="badge bg-secondary">${type}</span>
                                </div>
                                <div class="schedule-details">
                                    <small class="text-muted d-block">
                                        <i class="fas fa-clock"></i> ${event.extendedProps ? event.extendedProps.date : dateStr}
                                    </small>
                                    ${event.extendedProps && event.extendedProps.note ? `<small class="text-muted d-block"><i class="fas fa-note-sticky"></i> ${event.extendedProps.note}</small>` : ''}
                                </div>
                                ${canDelete ? `<button class="btn btn-sm btn-outline-danger mt-2" onclick="deleteEvent('${event.extendedProps.scheduleId}')"><i class="fas fa-trash"></i> @lang('profile.delete')</button>` : ''}
                            </div>
                        `;
                    });
                    
                    contentHtml += '</div></div></div>';
                }
                
                contentHtml += '</div>';
                content.innerHTML = contentHtml;
            }
            
            // Show panel
            panel.classList.add('show');
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function showBookingDetails(bookingId) {
            // Find the booking in our current events data
            let booking = null;
            let bookingEvent = null;
            
            Object.values(calendarEvents).forEach(dayEvents => {
                dayEvents.forEach(event => {
                    if (event.extendedProps && event.extendedProps.booking && event.extendedProps.booking.id == bookingId) {
                        booking = event.extendedProps.booking;
                        bookingEvent = event;
                    }
                });
            });
            
            if (!booking) {
                showAlert('error', 'Booking details not found');
                return;
            }
            
            const user = bookingEvent.extendedProps.user;
            const guiding = bookingEvent.extendedProps.guiding;
            
            // Populate the booking detail modal
            const bookingDetailsHtml = `
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-calendar-check"></i> Booking Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong> <span class="status-badge status-${booking.status}">${booking.status.toUpperCase()}</span></p>
                                        <p><strong>Date:</strong> ${booking.book_date}</p>
                                        <p><strong>Guests:</strong> ${booking.count_of_users} ${booking.count_of_users > 1 ? 'people' : 'person'}</p>
                                        <p><strong>Price:</strong> ${booking.price}€</p>
                                        ${booking.total_extra_price ? `<p><strong>Extra Services:</strong> ${booking.total_extra_price}€</p>` : ''}
                                    </div>
                                    <div class="col-md-6">
                                        ${user ? `
                                            <p><strong>Customer:</strong> ${user.firstname} ${user.lastname}</p>
                                            <p><strong>Email:</strong> ${user.email}</p>
                                            ${user.phone ? `<p><strong>Phone:</strong> ${user.phone}</p>` : ''}
                                        ` : `
                                            <p><strong>Customer:</strong> Guest User</p>
                                            ${booking.email ? `<p><strong>Email:</strong> ${booking.email}</p>` : ''}
                                            ${booking.phone ? `<p><strong>Phone:</strong> ${booking.phone}</p>` : ''}
                                        `}
                                        <p><strong>Guest Booking:</strong> ${booking.is_guest ? 'Yes' : 'No'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${guiding ? `
                        <div class="card border-0">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-fishing"></i> Tour Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tour:</strong> ${guiding.title}</p>
                                        <p><strong>Location:</strong> ${guiding.location}</p>
                                        <p><strong>Duration:</strong> ${guiding.duration} hours</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Max Guests:</strong> ${guiding.max_guests}</p>
                                        <p><strong>Base Price:</strong> ${guiding.price}€</p>
                                        ${guiding.meeting_point ? `<p><strong>Meeting Point:</strong> ${guiding.meeting_point}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            // Set the modal content and show it
            document.getElementById('bookingDetails').innerHTML = bookingDetailsHtml;
            
            // Show the modal using Bootstrap
            const modal = new bootstrap.Modal(document.getElementById('bookingDetailModal'));
            modal.show();
        }

        function initializeForms() {
            // Auto-set end date when beginning date changes
            const beginInput = document.getElementById('addNewBeginInput');
            const endInput = document.getElementById('addNewEndInput');
            
            beginInput.addEventListener('change', function() {
                if (this.value) {
                    // Create a date object from the selected beginning date
                    const beginDate = new Date(this.value);
                    // Add one day to get the next day
                    const endDate = new Date(beginDate);
                    endDate.setDate(beginDate.getDate() + 1);
                    
                    // Format the date as YYYY-MM-DD for the input
                    const formattedEndDate = endDate.toISOString().split('T')[0];
                    
                    // Set the end date input
                    endInput.value = formattedEndDate;
                    
                    // Set minimum date for end input to prevent selecting dates before beginning
                    endInput.min = formattedEndDate;
                }
            });
            
            // Also ensure end date can't be before beginning date
            endInput.addEventListener('change', function() {
                if (beginInput.value && this.value) {
                    const beginDate = new Date(beginInput.value);
                    const endDate = new Date(this.value);
                    
                    if (endDate < beginDate) {
                        // If end date is before begin date, reset to next day
                        const nextDay = new Date(beginDate);
                        nextDay.setDate(beginDate.getDate() + 1);
                        this.value = nextDay.toISOString().split('T')[0];
                        
                        showAlert('warning', 'End date cannot be before the beginning date. Set to next day.');
                    }
                }
            });

            // Add event form
            document.getElementById('addEventForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                
                // Handle multiple checkboxes for days
                const days = formData.getAll('day[]');
                if (days.length > 0) {
                    data.day = days;
                }

                fetch('{{ route("profile.calendar.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#addEventModal').modal('hide');
                        loadCalendarEvents();
                        showAlert('success', data.message);
                        document.getElementById('addEventForm').reset();
                        // Reset the min attribute when form is reset
                        endInput.removeAttribute('min');
                    } else {
                        showAlert('error', data.message || 'Error creating event');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Error creating event');
                });
            });

            // Delete confirmation
            document.getElementById('confirmDelete').addEventListener('click', function() {
                const eventId = this.dataset.eventId;
                if (eventId) {
                    deleteEvent(eventId);
                }
            });
        }

        function closeDetailPanel() {
            const panel = document.getElementById('detailPanel');
            panel.classList.remove('show');
        }

        function showBookingDetailPanel(extendedProps) {
            const booking = extendedProps.booking;
            const user = extendedProps.user;
            const guiding = extendedProps.guiding;
            
            const title = document.getElementById('detailPanelTitle');
            const subtitle = document.getElementById('detailPanelSubtitle');
            const content = document.getElementById('detailPanelContent');
            
            title.textContent = 'Booking Details';
            subtitle.innerHTML = `<span class="status-badge status-${booking.status}">${booking.status.toUpperCase()}</span> - ${booking.book_date}`;
            
            content.innerHTML = `
                <div class="detail-card">
                    <h6>Customer Information</h6>
                    <div class="detail-item">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value">${user ? user.firstname + ' ' + user.lastname : 'Guest User'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${user ? user.email : booking.email || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${user ? user.phone || booking.phone : booking.phone || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Guest Type:</span>
                        <span class="detail-value">${booking.is_guest ? 'Guest User' : 'Registered User'}</span>
                    </div>
                </div>
                
                <div class="detail-card">
                    <h6>Booking Information</h6>
                    <div class="detail-item">
                        <span class="detail-label">Guests:</span>
                        <span class="detail-value">${booking.count_of_users} ${booking.count_of_users > 1 ? 'people' : 'person'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value">${booking.book_date}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Base Price:</span>
                        <span class="detail-value">${booking.price}€</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Extra Services:</span>
                        <span class="detail-value">${booking.total_extra_price || 0}€</span>
                    </div>
                    <div class="detail-item" style="border-top: 2px solid var(--thm-primary); margin-top: 10px; padding-top: 10px;">
                        <span class="detail-label"><strong>Total Price:</strong></span>
                        <span class="detail-value"><strong>${booking.price}€</strong></span>
                    </div>
                </div>
                
                ${guiding ? `
                <div class="detail-card">
                    <h6>Tour Information</h6>
                    <div class="detail-item">
                        <span class="detail-label">Tour Title:</span>
                        <span class="detail-value">${guiding.title}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value">${guiding.location}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Meeting Point:</span>
                        <span class="detail-value">${guiding.meeting_point || 'TBD'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Duration:</span>
                        <span class="detail-value">${guiding.duration} hours</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Max Guests:</span>
                        <span class="detail-value">${guiding.max_guests}</span>
                    </div>
                </div>
                ` : ''}
            `;
        }

        function showEventDetailPanel(extendedProps) {
            const title = document.getElementById('detailPanelTitle');
            const subtitle = document.getElementById('detailPanelSubtitle');
            const content = document.getElementById('detailPanelContent');
            
            title.textContent = 'Event Details';
            subtitle.innerHTML = `<span class="status-badge status-${extendedProps.status}">${getTypeLabel(extendedProps.type)}</span> - ${extendedProps.date}`;
            
            let actionButtons = '';
            if (extendedProps.canDelete) {
                actionButtons = `
                    <div class="detail-card">
                        <h6>Actions</h6>
                        <button class="btn btn-danger btn-sm" onclick="deleteEventFromPanel('${extendedProps.scheduleId}')">
                            <i class="fas fa-trash"></i> Delete Event
                        </button>
                    </div>
                `;
            }
            
            content.innerHTML = `
                <div class="detail-card">
                    <h6>Event Information</h6>
                    <div class="detail-item">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value">${getTypeLabel(extendedProps.type)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date:</span>
                        <span class="detail-value">${extendedProps.date}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Note:</span>
                        <span class="detail-value">${extendedProps.note || 'No additional notes'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Can Edit:</span>
                        <span class="detail-value">${extendedProps.canEdit ? 'Yes' : 'No'}</span>
                    </div>
                </div>
                
                ${extendedProps.guiding ? `
                <div class="detail-card">
                    <h6>Associated Tour</h6>
                    <div class="detail-item">
                        <span class="detail-label">Tour Title:</span>
                        <span class="detail-value">${extendedProps.guiding.title}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value">${extendedProps.guiding.location || 'N/A'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Duration:</span>
                        <span class="detail-value">${extendedProps.guiding.duration || 'N/A'} hours</span>
                    </div>
                </div>
                ` : ''}
                
                ${actionButtons}
            `;
        }
        
        function deleteEventFromPanel(eventId) {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                deleteEvent(eventId);
                closeDetailPanel();
            }
        }

        function clearAllFilters() {
            // Reset all filters
            currentFilters = {
                guiding_id: '',
                type: '',
                status: ''
            };
            
            // Reset filter buttons
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.filter-btn[data-type=""], .filter-btn[data-status=""]').forEach(btn => btn.classList.add('active'));
            
            // Reset tour cards
            document.querySelectorAll('.tour-filter-card').forEach(card => card.classList.remove('active'));
            document.querySelector('.tour-filter-card[data-guiding-id=""]').classList.add('active');
            
            // Refresh calendar
            calendar.refetchEvents();
            
            showAlert('info', 'All filters cleared');
        }

        function deleteEvent(eventId) {
            fetch(`{{ url('/profile/calendar/delete') }}/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#deleteEventModal').modal('hide');
                    loadCalendarEvents();
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.error || 'Error deleting event');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Error deleting event');
            });
        }

        function getTypeLabel(type) {
            const labels = {
                'tour_request': '@lang("profile.booking")',
                'tour_schedule': '@lang("profile.blocked")',
                'vacation_schedule': '@lang("profile.vacation")',
                'vacation_request': '@lang("profile.vacation-request")',
                'custom_schedule': '@lang("profile.custom")'
            };
            return labels[type] || type;
        }

        function showAlert(type, message) {
            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
    </script>
@endsection
