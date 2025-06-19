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

        /* Side Detail Panel Styles */
        .side-detail-panel {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            max-height: 500px;
        }
        
        .side-detail-panel .detail-panel-header {
            background: linear-gradient(135deg, #313041, #2c5aa0);
            color: white !important;
            padding: 12px 15px;
            position: relative;
        }
        
        .side-detail-panel .detail-panel-header h5,
        .side-detail-panel .detail-panel-header p {
            color: white !important;
            margin: 0;
        }
        
        .side-detail-panel .detail-panel-header h5 {
            font-size: 16px;
            font-weight: 600;
        }
        
        .side-detail-panel .detail-panel-header .close-panel-btn {
            position: absolute;
            top: 8px;
            right: 12px;
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .side-detail-panel .detail-panel-header .close-panel-btn:hover {
            transform: scale(1.2);
        }
        
        .side-detail-panel .detail-panel-body {
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .side-detail-panel .detail-content {
            min-height: auto;
        }
        
        /* Horizontal Card Layout */
        .side-detail-cards {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .side-detail-card {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 12px;
            border-left: 3px solid #313041;
            display: flex;
            flex-direction: column;
        }
        
        .side-detail-card h6 {
            color: #313041;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
        }
        
        .side-detail-card-content {
            max-height: 150px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .side-detail-card-content::-webkit-scrollbar {
            width: 4px;
        }
        
        .side-detail-card-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 2px;
        }
        
        .side-detail-card-content::-webkit-scrollbar-thumb {
            background: #313041;
            border-radius: 2px;
        }
        
        .side-schedule-item {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }
        
        .side-schedule-item:hover {
            background: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .side-schedule-item:last-child {
            margin-bottom: 0;
        }
        
        .side-schedule-details {
            margin-top: 8px;
            line-height: 1.4;
        }
        
        .side-schedule-details small {
            display: block;
            margin-bottom: 3px;
            color: #495057 !important;
            font-weight: 500;
            font-size: 11px;
        }
        
        .side-schedule-details i {
            width: 12px;
            text-align: center;
            margin-right: 6px;
            color: #313041;
        }
        
        .side-status-badge {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        /* Mobile adjustments for side panel */
        @media screen and (max-width:767px) {
            .side-detail-panel {
                max-height: 300px;
            }
            
            .side-detail-panel .detail-panel-body {
                max-height: 250px;
                padding: 10px;
            }
            
            .side-detail-card {
                padding: 8px;
            }
            
            .side-detail-card-content {
                max-height: 100px;
            }
            
            .side-schedule-item {
                padding: 8px;
            }
        }
        
        /* Status Badge Styles (still needed) */
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
                        <div class="legend-item" id="confirmedLegend">
                            <div class="legend-color legend-accepted"></div>
                            <span class="legend-text">@lang('profile.confirmed')</span>
                        </div>
                        <div class="legend-item" id="pendingLegend">
                            <div class="legend-color legend-pending"></div>
                            <span class="legend-text">@lang('profile.pending')</span>
                        </div>
                        <div class="legend-item" id="rejectedLegend">
                            <div class="legend-color legend-rejected"></div>
                            <span class="legend-text">@lang('profile.rejected')</span>
                        </div>
                        <div class="legend-item" id="customLegend">
                            <div class="legend-color legend-custom"></div>
                            <span class="legend-text">@lang('profile.custom')</span>
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
        
        <!-- Top Right Panel: Tour Filter & Details -->
        <div class="col-md-4">
            <div class="tour-filter-panel d-flex flex-column">
                <!-- Tour Filter Section -->
                <div class="filter-section mb-3">
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
                    
                    <!-- Quick Actions removed - Add Event button moved to detail panel -->
                </div>
                
                <!-- Details Section -->
                <div class="details-section flex-grow-1">
                    <div id="sideDetailPanel" class="side-detail-panel" style="display: none;">
                        <div class="detail-panel-header">
                            <button class="close-panel-btn" onclick="closeSideDetailPanel()">&times;</button>
                            <h5 id="sideDetailPanelTitle">Schedule for Date</h5>
                            <p id="sideDetailPanelDate" class="mb-0 small"></p>
                        </div>
                        <div class="detail-panel-body">
                            <div id="sideDetailPanelContent" class="detail-content">
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                    <p class="small">Click on a date to view details</p>
                                </div>
                            </div>
                            <div class="detail-panel-actions mt-3 pt-2 border-top">
                                <button class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#addEventModal">
                                    <i class="fas fa-plus"></i> @lang('profile.add-event')
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

</div>

<!-- Add Custom Schedule Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content schedule-modal">
            <div class="modal-header schedule-modal-header">
                <div class="modal-header-content">
                    <h4 class="modal-title" id="addEventModalLabel">
                        <i class="fas fa-calendar-plus me-2"></i>
                        @lang('profile.block-calendar-dates')
                    </h4>
                    <p class="modal-subtitle mb-0">@lang('profile.create-custom-blocked-periods')</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEventForm">
                @csrf
                <div class="modal-body schedule-modal-body">
                    <!-- Instructions -->
                    <div class="instructions-card mb-4">
                        <div class="d-flex align-items-start">
                            <div class="instruction-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="instruction-content">
                                <h6>@lang('profile.how-to-block-dates')</h6>
                                <ul class="instruction-list">
                                    <li>@lang('profile.choose-single-day-or-range')</li>
                                    <li>@lang('profile.select-future-dates-only')</li>
                                    <li>@lang('profile.add-notes-to-explain')</li>
                                    <li>@lang('profile.blocked-dates-appear-teal')</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Block Type Selection -->
                    <div class="form-section mb-4">
                        <label class="form-label-main">@lang('profile.block-type')</label>
                        <p class="form-help-text">@lang('profile.choose-how-to-block')</p>
                        
                        <div class="custom-radio-group">
                            <div class="custom-radio-option">
                                <input type="radio" name="blockType" id="singleDay" value="single" checked>
                                <label for="singleDay" class="custom-radio-label">
                                    <div class="radio-icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="radio-content">
                                        <span class="radio-title">@lang('profile.single-day')</span>
                                        <span class="radio-description">@lang('profile.block-one-specific-date')</span>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="custom-radio-option">
                                <input type="radio" name="blockType" id="dateRange" value="range">
                                <label for="dateRange" class="custom-radio-label">
                                    <div class="radio-icon">
                                        <i class="fas fa-calendar-week"></i>
                                    </div>
                                    <div class="radio-content">
                                        <span class="radio-title">@lang('profile.date-range')</span>
                                        <span class="radio-description">@lang('profile.block-multiple-consecutive-dates')</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Single Day Selection -->
                    <div id="singleDaySection" class="form-section mb-4">
                        <label for="singleDateInput" class="form-label-main">@lang('profile.select-date')</label>
                        <p class="form-help-text">@lang('profile.choose-date-to-block')</p>
                        <input type="date" id="singleDateInput" class="form-control form-control-modern" name="single_date" required>
                    </div>
                    
                    <!-- Date Range Selection -->
                    <div id="dateRangeSection" class="form-section mb-4" style="display: none;">
                        <label class="form-label-main">@lang('profile.date-range')</label>
                        <p class="form-help-text">@lang('profile.choose-start-end-dates')</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="addNewBeginInput" class="form-label-secondary">@lang('profile.start-date')</label>
                                <input type="date" id="addNewBeginInput" class="form-control form-control-modern" name="start">
                            </div>
                            <div class="col-md-6">
                                <label for="addNewEndInput" class="form-label-secondary">@lang('profile.end-date')</label>
                                <input type="date" id="addNewEndInput" class="form-control form-control-modern" name="end">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notes Input -->
                    <div class="form-section mb-4">
                        <label for="eventNote" class="form-label-main">@lang('profile.notes')</label>
                        <p class="form-help-text">@lang('profile.add-description-why-blocked')</p>
                        <textarea 
                            id="eventNote" 
                            class="form-control form-control-modern" 
                            name="note" 
                            rows="4" 
                            placeholder="@lang('profile.notes-placeholder')"
                        ></textarea>
                    </div>
                </div>
                <div class="modal-footer schedule-modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>@lang('profile.cancel')
                    </button>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save me-2"></i>@lang('profile.save-schedule')
                    </button>
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

        /* Professional Modal Styles */
        .schedule-modal .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .schedule-modal-header {
            background: linear-gradient(135deg, #313041 0%, #2c5aa0 100%);
            color: white;
            border: none;
            padding: 24px 30px;
            position: relative;
        }

        .modal-header-content h4 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .modal-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 4px;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: 0.8;
        }

        .btn-close-white:hover {
            opacity: 1;
        }

        .schedule-modal-body {
            padding: 30px;
            background: #fafbfc;
        }

        /* Instructions Card */
        .instructions-card {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border: 1px solid #e1f5fe;
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .instructions-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2196f3, #9c27b0);
        }

        .instruction-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2196f3, #9c27b0);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            margin-right: 16px;
            flex-shrink: 0;
        }

        .instruction-content h6 {
            color: #1976d2;
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 16px;
        }

        .instruction-list {
            margin: 0;
            padding-left: 16px;
            color: #424242;
            line-height: 1.6;
        }

        .instruction-list li {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .instruction-list li:last-child {
            margin-bottom: 0;
        }

        /* Form Sections */
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .form-section:hover {
            border-color: #d1d5db;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .form-label-main {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
            display: block;
        }

        .form-label-secondary {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            display: block;
        }

        .form-help-text {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .form-control-modern {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-control-modern:focus {
            border-color: #313041;
            box-shadow: 0 0 0 3px rgba(49, 48, 65, 0.1);
            background: white;
        }

        /* Custom Radio Buttons */
        .custom-radio-group {
            display: flex;
            flex-direction: row;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .custom-radio-group {
                flex-direction: column;
                gap: 12px;
            }
        }

        .custom-radio-option {
            position: relative;
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .custom-radio-option {
                flex: none;
            }
        }

        .custom-radio-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .custom-radio-label {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            min-height: 80px;
            height: 80px;
        }

        .custom-radio-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #313041, #2c5aa0);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .custom-radio-label > * {
            position: relative;
            z-index: 2;
        }

        .custom-radio-option:hover .custom-radio-label {
            border-color: #313041;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(49, 48, 65, 0.15);
        }

        .custom-radio-option input[type="radio"]:checked + .custom-radio-label {
            border-color: #313041;
            background: #313041;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(49, 48, 65, 0.25);
        }

        .custom-radio-option input[type="radio"]:checked + .custom-radio-label::before {
            opacity: 1;
        }

        .radio-icon {
            width: 50px;
            height: 50px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 18px;
            color: #313041;
            transition: all 0.3s ease;
        }

        .custom-radio-option input[type="radio"]:checked + .custom-radio-label .radio-icon {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .radio-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .radio-title {
            display: block;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
            color: #1f2937;
            transition: color 0.3s ease;
        }

        .custom-radio-option input[type="radio"]:checked + .custom-radio-label .radio-title {
            color: white;
        }

        .radio-description {
            display: block;
            font-size: 13px;
            color: #6b7280;
            transition: color 0.3s ease;
        }

        .custom-radio-option input[type="radio"]:checked + .custom-radio-label .radio-description {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Modal Footer */
        .schedule-modal-footer {
            background: white;
            border: none;
            padding: 20px 30px;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .btn-cancel {
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            color: #374151;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #e5e7eb;
            border-color: #d1d5db;
            color: #1f2937;
            transform: translateY(-1px);
        }

        .btn-save {
            background: linear-gradient(135deg, #313041, #2c5aa0);
            border: 2px solid #313041;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(49, 48, 65, 0.3);
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #2a2938, #254a8a);
            border-color: #2a2938;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(49, 48, 65, 0.4);
        }

        /* Mobile Responsiveness for Modal */
        @media (max-width: 768px) {
            .schedule-modal-header {
                padding: 20px;
            }

            .schedule-modal-body {
                padding: 20px;
            }

            .form-section {
                padding: 20px;
            }

            .custom-radio-group {
                gap: 10px;
            }

            .custom-radio-label {
                padding: 14px 16px;
                min-height: 70px;
                height: 70px;
            }

            .radio-icon {
                width: 45px;
                height: 45px;
                font-size: 16px;
                margin-right: 12px;
            }

            .instructions-card {
                padding: 16px;
            }

            .instruction-icon {
                width: 35px;
                height: 35px;
                font-size: 14px;
            }

            .schedule-modal-footer {
                padding: 16px 20px;
                flex-direction: column;
            }

            .btn-cancel,
            .btn-save {
                width: 100%;
                justify-content: center;
            }
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
            
            // Initialize legend display
            updateLegendDisplay();
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
            
            const baseParams = {
                start: startDate.toISOString().split('T')[0],
                end: endDate.toISOString().split('T')[0]
            };
            
            // If a specific tour is selected, we need to make two requests:
            // 1. Get events for that specific tour
            // 2. Get all custom schedule events (they should always be visible)
            if (currentFilters.guiding_id) {
                const tourEventsPromise = fetch('/events?' + new URLSearchParams({
                    ...baseParams,
                    ...currentFilters
                }))
                .then(response => response.json());
                
                const customEventsPromise = fetch('/events?' + new URLSearchParams({
                    ...baseParams,
                    type: 'custom_schedule'
                }))
                .then(response => response.json());
                
                Promise.all([tourEventsPromise, customEventsPromise])
                .then(([tourEvents, customEvents]) => {
                    calendarEvents = {};
                    
                    // Combine events from both requests
                    const allEvents = [];
                    
                    // Add tour-specific events
                    if (Array.isArray(tourEvents)) {
                        allEvents.push(...tourEvents);
                    }
                    
                    // Add custom events (always included)
                    if (Array.isArray(customEvents)) {
                        // Filter out duplicates (in case custom events are associated with the selected tour)
                        const existingEventIds = new Set(allEvents.map(e => e.id));
                        const uniqueCustomEvents = customEvents.filter(e => !existingEventIds.has(e.id));
                        allEvents.push(...uniqueCustomEvents);
                    }
                    
                    // Process all events
                    allEvents.forEach(event => {
                        const dateKey = event.start ? event.start.split('T')[0] : event.date;
                        if (dateKey) {
                            if (!calendarEvents[dateKey]) {
                                calendarEvents[dateKey] = [];
                            }
                            calendarEvents[dateKey].push(event);
                        }
                    });
                    
                    updateCalendarDisplay();
                })
                .catch(error => {
                    console.error('Error loading calendar events:', error);
                });
            } else {
                // When showing all tours, make a single request with all filters
                fetch('/events?' + new URLSearchParams({
                    ...baseParams,
                    ...currentFilters
                }))
                .then(response => response.json())
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
                    
                    // Show/hide legend items based on filter context
                    const availableLegend = document.getElementById('availableLegend');
                    const unavailableLegend = document.getElementById('unavailableLegend');
                    const confirmedLegend = document.getElementById('confirmedLegend');
                    const pendingLegend = document.getElementById('pendingLegend');
                    const rejectedLegend = document.getElementById('rejectedLegend');
                    const customLegend = document.getElementById('customLegend');
                    
                    if (guidingId) {
                        // When a specific tour is selected, show availability states and hide booking states
                        if (availableLegend) availableLegend.style.display = 'flex';
                        if (unavailableLegend) unavailableLegend.style.display = 'flex';
                        if (confirmedLegend) confirmedLegend.style.display = 'none';
                        if (pendingLegend) pendingLegend.style.display = 'flex'; // Keep pending always visible
                        if (rejectedLegend) rejectedLegend.style.display = 'none';
                        if (customLegend) customLegend.style.display = 'flex'; // Keep custom as it can apply to tours
                    } else {
                        // When showing all tours, show booking states and hide availability states
                        if (availableLegend) availableLegend.style.display = 'none';
                        if (unavailableLegend) unavailableLegend.style.display = 'none';
                        if (confirmedLegend) confirmedLegend.style.display = 'flex';
                        if (pendingLegend) pendingLegend.style.display = 'flex';
                        if (rejectedLegend) rejectedLegend.style.display = 'flex';
                        if (customLegend) customLegend.style.display = 'flex';
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
        
        function updateLegendDisplay() {
            const availableLegend = document.getElementById('availableLegend');
            const unavailableLegend = document.getElementById('unavailableLegend');
            const confirmedLegend = document.getElementById('confirmedLegend');
            const pendingLegend = document.getElementById('pendingLegend');
            const rejectedLegend = document.getElementById('rejectedLegend');
            const customLegend = document.getElementById('customLegend');
            
            if (currentFilters.guiding_id !== '') {
                // When a specific tour is selected, show availability states and hide booking states
                if (availableLegend) availableLegend.style.display = 'flex';
                if (unavailableLegend) unavailableLegend.style.display = 'flex';
                if (confirmedLegend) confirmedLegend.style.display = 'none';
                if (pendingLegend) pendingLegend.style.display = 'flex'; // Keep pending always visible
                if (rejectedLegend) rejectedLegend.style.display = 'none';
                if (customLegend) customLegend.style.display = 'flex'; // Keep custom as it can apply to tours
            } else {
                // When showing all tours, show booking states and hide availability states
                if (availableLegend) availableLegend.style.display = 'none';
                if (unavailableLegend) unavailableLegend.style.display = 'none';
                if (confirmedLegend) confirmedLegend.style.display = 'flex';
                if (pendingLegend) pendingLegend.style.display = 'flex';
                if (rejectedLegend) rejectedLegend.style.display = 'flex';
                if (customLegend) customLegend.style.display = 'flex';
            }
        }

        function showDayDetails(dateStr) {            
            const panel = document.getElementById('sideDetailPanel');
            const title = document.getElementById('sideDetailPanelTitle');
            const dateElement = document.getElementById('sideDetailPanelDate');
            const content = document.getElementById('sideDetailPanelContent');
            
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
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                        <p class="small">No events scheduled for this date</p>
                    </div>
                `;
            } else {
                let contentHtml = '<div class="side-detail-cards">';
                
                // Group events by type
                const bookings = dayEvents.filter(e => e.extendedProps && e.extendedProps.booking);
                const customEvents = dayEvents.filter(e => e.extendedProps && e.extendedProps.type === 'custom_schedule');
                const otherEvents = dayEvents.filter(e => !e.extendedProps || (!e.extendedProps.booking && 
                    e.extendedProps.type !== 'tour_schedule' && e.extendedProps.type !== 'vacation_schedule' && 
                    e.extendedProps.type !== 'custom_schedule'));
                
                // Display bookings
                if (bookings.length > 0) {
                    contentHtml += `
                        <div class="side-detail-card">
                            <h6><i class="fas fa-calendar-check"></i> Bookings (${bookings.length})</h6>
                            <div class="side-detail-card-content">
                    `;
                    
                    bookings.forEach(event => {
                        const booking = event.extendedProps.booking;
                        const user = event.extendedProps.user;
                        const guiding = event.extendedProps.guiding;
                        
                        contentHtml += `
                            <div class="side-schedule-item">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-1 small">${guiding ? guiding.title : 'Tour Booking'}</h6>
                                    <span class="side-status-badge status-${booking.status}">${booking.status}</span>
                                </div>
                                <div class="side-schedule-details">
                                    <small><i class="fas fa-user"></i> ${user ? user.firstname + ' ' + user.lastname : 'Guest User'}</small>
                                    <small><i class="fas fa-users"></i> ${booking.count_of_users} guests</small>
                                    <small><i class="fas fa-euro-sign"></i> ${booking.price}€</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary mt-1" onclick="showBookingDetails('${booking.id}')">
                                    View Details
                                </button>
                            </div>
                        `;
                    });
                    
                    contentHtml += '</div></div>';
                }
                
                // Display custom events
                if (customEvents.length > 0) {
                    contentHtml += `
                        <div class="side-detail-card">
                            <h6><i class="fas fa-calendar-plus text-info"></i> @lang('profile.custom-events') (${customEvents.length})</h6>
                            <div class="side-detail-card-content">
                    `;
                    
                    customEvents.forEach(event => {
                        const type = getTypeLabel(event.extendedProps.type);
                        const canDelete = event.extendedProps.canDelete;
                        
                        contentHtml += `
                            <div class="side-schedule-item">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-1 small">${event.title || 'Custom Event'}</h6>
                                    <span class="badge bg-info small">${type}</span>
                                </div>
                                <div class="side-schedule-details">
                                    <small><i class="fas fa-clock"></i> ${event.extendedProps.date || dateStr}</small>
                                    ${event.extendedProps.note ? `<small><i class="fas fa-note-sticky"></i> ${event.extendedProps.note}</small>` : ''}
                                </div>
                                ${canDelete ? `<button class="btn btn-sm btn-outline-danger mt-1" onclick="deleteEvent('${event.extendedProps.scheduleId}')"><i class="fas fa-trash"></i> Delete</button>` : ''}
                            </div>
                        `;
                    });
                    
                    contentHtml += '</div></div>';
                }
                
                // Display other events if any
                if (otherEvents.length > 0) {
                    contentHtml += `
                        <div class="side-detail-card">
                            <h6><i class="fas fa-calendar text-secondary"></i> @lang('profile.other-events') (${otherEvents.length})</h6>
                            <div class="side-detail-card-content">
                    `;
                    
                    otherEvents.forEach(event => {
                        const type = event.extendedProps ? getTypeLabel(event.extendedProps.type) : 'Event';
                        const canDelete = event.extendedProps ? event.extendedProps.canDelete : false;
                        
                        contentHtml += `
                            <div class="side-schedule-item">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="mb-1 small">${event.title || 'Event'}</h6>
                                    <span class="badge bg-secondary small">${type}</span>
                                </div>
                                <div class="side-schedule-details">
                                    <small><i class="fas fa-clock"></i> ${event.extendedProps ? event.extendedProps.date : dateStr}</small>
                                    ${event.extendedProps && event.extendedProps.note ? `<small><i class="fas fa-note-sticky"></i> ${event.extendedProps.note}</small>` : ''}
                                </div>
                                ${canDelete ? `<button class="btn btn-sm btn-outline-danger mt-1" onclick="deleteEvent('${event.extendedProps.scheduleId}')"><i class="fas fa-trash"></i> Delete</button>` : ''}
                            </div>
                        `;
                    });
                    
                    contentHtml += '</div></div>';
                }
                
                contentHtml += '</div>';
                content.innerHTML = contentHtml;
            }
            
            // Show panel
            panel.style.display = 'block';
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
            // Get tomorrow's date as minimum date (disable past dates)
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            
            // Set minimum date for all date inputs
            const singleDateInput = document.getElementById('singleDateInput');
            const beginInput = document.getElementById('addNewBeginInput');
            const endInput = document.getElementById('addNewEndInput');
            
            singleDateInput.min = tomorrowStr;
            beginInput.min = tomorrowStr;
            endInput.min = tomorrowStr;
            
            // Handle radio button toggle
            const singleDayRadio = document.getElementById('singleDay');
            const dateRangeRadio = document.getElementById('dateRange');
            const singleDaySection = document.getElementById('singleDaySection');
            const dateRangeSection = document.getElementById('dateRangeSection');
            
            singleDayRadio.addEventListener('change', function() {
                if (this.checked) {
                    singleDaySection.style.display = 'block';
                    dateRangeSection.style.display = 'none';
                    singleDateInput.required = true;
                    beginInput.required = false;
                    endInput.required = false;
                }
            });
            
            dateRangeRadio.addEventListener('change', function() {
                if (this.checked) {
                    singleDaySection.style.display = 'none';
                    dateRangeSection.style.display = 'block';
                    singleDateInput.required = false;
                    beginInput.required = true;
                    endInput.required = true;
                }
            });
            
            // Auto-set end date when beginning date changes (for range mode)
            beginInput.addEventListener('change', function() {
                if (this.value) {
                    // Set minimum date for end input to prevent selecting dates before beginning
                    endInput.min = this.value;
                    
                    // If end date is not set or is before begin date, set it to begin date
                    if (!endInput.value || endInput.value < this.value) {
                        endInput.value = this.value;
                    }
                }
            });
            
            // Ensure end date can't be before beginning date
            endInput.addEventListener('change', function() {
                if (beginInput.value && this.value && this.value < beginInput.value) {
                    this.value = beginInput.value;
                    showAlert('warning', 'End date cannot be before the start date.');
                }
            });

            // Add event form submission
            document.getElementById('addEventForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const data = {};
                
                // Get the block type
                const blockType = formData.get('blockType');
                
                if (blockType === 'single') {
                    data.start = formData.get('single_date');
                    data.end = formData.get('single_date');
                } else {
                    data.start = formData.get('start');
                    data.end = formData.get('end');
                }
                
                data.type = 'custom_schedule';
                data.note = formData.get('note') || 'Custom blocked date';

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
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addEventModal'));
                        modal.hide();
                        loadCalendarEvents();
                        showAlert('success', data.message || 'Schedule created successfully!');
                        document.getElementById('addEventForm').reset();
                        // Reset to single day mode
                        singleDayRadio.checked = true;
                        singleDaySection.style.display = 'block';
                        dateRangeSection.style.display = 'none';
                        // Reset min attributes
                        endInput.removeAttribute('min');
                    } else {
                        showAlert('error', data.message || 'Error creating schedule');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('error', 'Error creating schedule');
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

        function closeSideDetailPanel() {
            const panel = document.getElementById('sideDetailPanel');
            panel.style.display = 'none';
        }
        
        // Keep for backward compatibility
        function closeDetailPanel() {
            closeSideDetailPanel();
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
