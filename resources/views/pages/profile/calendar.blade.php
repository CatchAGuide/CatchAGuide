@extends('pages.profile.layouts.profile')

@section('title', __('profile.calendar'))
@section('css_after')
    <style>
        .fc .fc-button-primary {
            background-color: var(--thm-primary);
            border-color: var(--thm-primary);
        }
        .fc .fc-toolbar-title {
            color: var(--thm-primary);
        }

        a:hover {
            color: var(--thm-primary);
        }
        
        .fc-daygrid-event-dot {
            margin: 0 4px;
            box-sizing: content-box;
            width: 0;
            height: 0;
            border: 4px solid #3788d8;
            border: calc(var(--fc-daygrid-event-dot-width,8px)/ 2) solid  var(--thm-primary);
            border-radius: 4px;
            border-radius: calc(var(--fc-daygrid-event-dot-width,8px)/ 2);
        }

        /* Calendar Filter Styles */
        .calendar-filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filter-group {
            margin-bottom: 10px;
        }
        
        .filter-group label {
            font-weight: 600;
            margin-right: 10px;
            color: var(--thm-primary);
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 5px 15px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .filter-btn.active {
            background: var(--thm-primary);
            color: white;
            border-color: var(--thm-primary);
        }
        
        .filter-btn:hover {
            background: var(--thm-primary);
            color: white;
        }

        /* Legend Styles */
        .calendar-legend {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .legend-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--thm-primary);
        }
        
        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
        }
        
        .legend-text {
            font-size: 14px;
        }

        /* Custom Event Styles */
        .fc-event {
            cursor: pointer;
            font-size: 11px;
            border-radius: 6px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .fc-event:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            z-index: 10;
        }

        /* Tooltip Styles */
        .event-tooltip {
            position: fixed;
            background: rgba(0,0,0,0.9);
            color: white;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 12px;
            z-index: 1000;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            max-width: 280px;
            pointer-events: none;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            display: none;
            --arrow-position: bottom;
        }

        .event-tooltip.show {
            opacity: 1;
            transform: translateY(0);
            display: block;
        }

        .event-tooltip::before {
            content: '';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
        }

        .event-tooltip[style*="--arrow-position: bottom"]::before {
            bottom: -6px;
            border-top-color: rgba(0,0,0,0.9);
            border-bottom: none;
        }

        .event-tooltip[style*="--arrow-position: top"]::before {
            top: -6px;
            border-bottom-color: rgba(0,0,0,0.9);
            border-top: none;
        }

        .tooltip-header {
            font-weight: 600;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .tooltip-row {
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
        }

        .tooltip-label {
            font-weight: 500;
            opacity: 0.8;
        }

        .tooltip-value {
            font-weight: 600;
        }

        /* Modal Styles */
        .modal-header {
            background: var(--thm-primary);
            color: white;
        }

        /* Inline Detail Panel */
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
            max-height: 600px;
            opacity: 1;
        }

        .detail-panel-header {
            background: linear-gradient(135deg, var(--thm-primary), #2c5aa0);
            color: white;
            padding: 20px;
            position: relative;
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

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .detail-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid var(--thm-primary);
        }

        .detail-card h6 {
            color: var(--thm-primary);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 4px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #6c757d;
        }

        .detail-value {
            font-weight: 600;
            color: #495057;
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
            .fc-toolbar.fc-header-toolbar {font-size: 60%}
            .calendar-filters {
                padding: 10px;
            }
            .legend-items {
                gap: 10px;
            }
            .filter-buttons {
                gap: 5px;
            }
        }
    </style>
@stop

@section('profile-content')

<div class="container" style="margin-bottom: 120px;">
    <!-- Calendar Filters -->


    <!-- Enhanced Filter Controls -->
    <div class="calendar-filters">
        <div class="row">
            <div class="col-md-6">
                <div class="filter-group">
                    <label>@lang('profile.filter-by-type'):</label>
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-type="">@lang('profile.all')</button>
                        <button class="filter-btn" data-type="tour_request">@lang('profile.bookings')</button>
                        <button class="filter-btn" data-type="tour_schedule">@lang('profile.blocked')</button>
                        <button class="filter-btn" data-type="vacation_schedule">@lang('profile.vacation')</button>
                        <button class="filter-btn" data-type="custom_schedule">@lang('profile.custom')</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="filter-group">
                    <label>@lang('profile.filter-by-status'):</label>
                    <div class="filter-buttons">
                        <button class="filter-btn active" data-status="">@lang('profile.all')</button>
                        <button class="filter-btn" data-status="accepted">@lang('profile.confirmed')</button>
                        <button class="filter-btn" data-status="pending">@lang('profile.pending')</button>
                        <button class="filter-btn" data-status="cancelled">@lang('profile.cancelled')</button>
                        <button class="filter-btn" data-status="rejected">@lang('profile.rejected')</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tours Filter Slider -->
    <div class="tours-filter-section mb-3">
        <h6 class="mb-2 text-muted">@lang('profile.select-tour-to-filter')</h6>
        <div class="tours-slider-container">
            <div class="tours-slider owl-carousel owl-theme">
                <!-- All Tours Option -->
                <div class="item">
                    <div class="tour-filter-card active" data-guiding-id="">
                        <div class="tour-filter-content">
                            <div class="tour-filter-icon">
                                <i class="fas fa-list"></i>
                            </div>
                            <div class="tour-filter-info">
                                <div class="tour-filter-title">@lang('profile.all-tours')</div>
                                <small class="tour-filter-subtitle">@lang('profile.show-all-events')</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                @foreach($userGuidings as $guiding)
                    <div class="item">
                        <div class="tour-filter-card" data-guiding-id="{{ $guiding->id }}">
                            <div class="tour-filter-content">
                                <div class="tour-filter-image">
                                    @if(get_featured_image_link($guiding))
                                        <img src="{{get_featured_image_link($guiding)}}" alt="{{ $guiding->title }}" class="tour-filter-thumbnail">
                                    @else
                                        <img src="{{asset('images/placeholder_guide.webp')}}" alt="{{ $guiding->title }}" class="tour-filter-thumbnail">
                                    @endif
                                </div>
                                <div class="tour-filter-info">
                                    <div class="tour-filter-title">{{ Str::limit($guiding->title, 20) }}</div>
                                    <small class="tour-filter-location">{{ Str::limit($guiding->location, 15) }}</small>
                                    <small class="tour-filter-price">{{ $guiding->getLowestPrice() }}€</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Calendar Legend -->
    <div class="calendar-legend">
        <div class="legend-title">@lang('profile.calendar-legend')</div>
        <div class="legend-items">
            <div class="legend-item">
                <div class="legend-color" style="background: #28a745;"></div>
                <span class="legend-text">@lang('profile.confirmed-bookings')</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #ffc107;"></div>
                <span class="legend-text">@lang('profile.pending-bookings')</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #dc3545;"></div>
                <span class="legend-text">@lang('profile.rejected-bookings')</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #6c757d;"></div>
                <span class="legend-text">@lang('profile.cancelled-bookings')</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #28a745; opacity: 0.3;"></div>
                <span class="legend-text">@lang('profile.available')</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #6f42c1;"></div>
                <span class="legend-text">@lang('profile.vacation')</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #20c997;"></div>
                <span class="legend-text">@lang('profile.custom-events')</span>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="col-md-12">
        <div id="calendar" class="mt-3"></div>
        
        <!-- Interactive Detail Panel -->
        <div id="detailPanel" class="calendar-detail-panel">
            <div class="detail-panel-header">
                <button class="close-panel-btn" onclick="closeDetailPanel()">&times;</button>
                <h4 id="detailPanelTitle">Event Details</h4>
                <p id="detailPanelSubtitle" class="mb-0"></p>
            </div>
            <div class="detail-panel-body">
                <div id="detailPanelContent" class="detail-grid">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Event Tooltip -->
    <div id="eventTooltip" class="event-tooltip">
        <div class="tooltip-content">
            <!-- Content will be populated by JavaScript -->
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
            border-color: var(--thm-primary);
            background: rgba(232, 96, 76, 0.05);
            box-shadow: 0 2px 12px rgba(232, 96, 76, 0.2);
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
            background: var(--thm-primary);
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
            color: var(--thm-primary);
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
            color: var(--thm-primary) !important;
            border-color: var(--thm-primary) !important;
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
            background: var(--thm-primary);
            width: 20px;
            border-radius: 10px;
        }
</style>
@endsection

@section('js_after')
    <script>
        document.querySelector('style').textContent += "@media screen and (max-width:767px) { .fc-toolbar.fc-header-toolbar {flex-direction:column;} .fc-toolbar-chunk { display: table-row; text-align:center; padding:5px 0; } }";
        
        let calendar;
        let currentFilters = {
            guiding_id: '',
            type: '',
            status: ''
        };

        document.addEventListener('DOMContentLoaded', function() {
            @if(app()->getLocale() == 'de')
                var locale = 'de';
            @elseif(app()->getLocale() == 'en')
                var locale = 'en';
            @endif

            var calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                windowResize: true,
                initialView: 'dayGridMonth',
                height: 650,
                locale: locale,
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch('/events?' + new URLSearchParams({
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                        ...currentFilters
                    }))
                    .then(response => response.json())
                    .then(data => successCallback(data))
                    .catch(error => failureCallback(error));
                },
                eventClick: function (info) {
                    handleEventClick(info);
                },
                eventMouseEnter: function(info) {
                    showEventTooltip(info);
                },
                eventMouseLeave: function(info) {
                    hideEventTooltip();
                },
                eventDisplay: 'auto',
                displayEventTime: false,
                customButtons: {
                    addEvent: {
                        text: '@lang('profile.add-event')',
                        click: function () {
                            $('#addEventModal').modal('show');
                        },
                    },
                    refreshEvents: {
                        text: '@lang('profile.refresh')',
                        click: function () {
                            calendar.refetchEvents();
                        },
                    }
                },
                headerToolbar: {
                    @if(!$agent->ismobile())
                        center: 'title',
                        left: 'dayGridMonth,timeGridWeek,timeGridDay',
                        right: 'addEvent,refreshEvents,prev,next',
                    @else
                        right: 'prev,next',
                    @endif
                },
                @if($agent->ismobile())
                footerToolbar: {
                    center: 'addEvent,refreshEvents',
                }
                @endif
            });
            calendar.render();

            // Initialize filters
            initializeFilters();
            
            // Initialize forms
            initializeForms();
            
            // Initialize tours slider
            initializeToursSlider();
        });

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
                    calendar.refetchEvents();
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
                    calendar.refetchEvents();
                });
            });
        }

        function initializeToursSlider() {
            // Initialize Owl Carousel for tours with minimal settings
            $('.tours-slider').owlCarousel({
                items: 4,
                margin: 8,
                nav: true,
                dots: true,
                autoWidth: false,
                responsive: {
                    0: { items: 1 },
                    576: { items: 2 },
                    768: { items: 3 },
                    992: { items: 4 },
                    1200: { items: 5 }
                }
            });

            // Handle tour card clicks
            document.querySelectorAll('.tour-filter-card').forEach(card => {
                card.addEventListener('click', function() {
                    // Remove active class from all tour cards
                    document.querySelectorAll('.tour-filter-card').forEach(c => c.classList.remove('active'));
                    // Add active class to clicked card
                    this.classList.add('active');
                    
                    // Update filter
                    const guidingId = this.dataset.guidingId;
                    currentFilters.guiding_id = guidingId;
                    
                    // Refresh calendar
                    calendar.refetchEvents();
                    
                    // Show success message
                    if (guidingId) {
                        const tourTitle = this.querySelector('.tour-filter-title').textContent;
                        showAlert('success', `Filtering events for: ${tourTitle}`);
                    } else {
                        showAlert('info', 'Showing all tours');
                    }
                });
            });
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
                        calendar.refetchEvents();
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

        function handleEventClick(info) {
            const event = info.event;
            const extendedProps = event.extendedProps;
            hideEventTooltip(); // Hide tooltip when showing detail panel
            showDetailPanel(extendedProps);
        }
        
        function showEventTooltip(info) {
            const event = info.event;
            const extendedProps = event.extendedProps;
            const tooltip = document.getElementById('eventTooltip');
            
            let content = `<div class="tooltip-header">${event.title}</div>`;
            
            if (extendedProps.booking) {
                const booking = extendedProps.booking;
                const user = extendedProps.user;
                
                content += `
                    <div class="tooltip-row">
                        <span class="tooltip-label">Status:</span>
                        <span class="tooltip-value">${booking.status}</span>
                    </div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">Guests:</span>
                        <span class="tooltip-value">${booking.count_of_users}</span>
                    </div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">Price:</span>
                        <span class="tooltip-value">${booking.price}€</span>
                    </div>
                `;
                
                if (user && user.email) {
                    content += `
                        <div class="tooltip-row">
                            <span class="tooltip-label">Email:</span>
                            <span class="tooltip-value">${user.email}</span>
                        </div>
                    `;
                }
            } else {
                content += `
                    <div class="tooltip-row">
                        <span class="tooltip-label">Type:</span>
                        <span class="tooltip-value">${getTypeLabel(extendedProps.type)}</span>
                    </div>
                    <div class="tooltip-row">
                        <span class="tooltip-label">Date:</span>
                        <span class="tooltip-value">${extendedProps.date}</span>
                    </div>
                `;
            }
            
            content += '<div style="margin-top: 8px; font-size: 11px; opacity: 0.7;">Click for details</div>';
            
            tooltip.querySelector('.tooltip-content').innerHTML = content;
            
            // Position tooltip properly relative to the event element
            const rect = info.el.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            // Calculate initial position (centered above the event)
            let left = rect.left + (rect.width / 2);
            let top = rect.top - 15; // 15px gap above the event
            
            // Make tooltip visible to get accurate dimensions
            tooltip.style.opacity = '0';
            tooltip.style.display = 'block';
            tooltip.classList.add('show');
            
            // Get actual tooltip dimensions after content is set
            const actualTooltipRect = tooltip.getBoundingClientRect();
            
            // Adjust horizontal position to center tooltip
            left = left - (actualTooltipRect.width / 2);
            
            // Ensure tooltip doesn't go off-screen horizontally
            if (left < 10) {
                left = 10;
            } else if (left + actualTooltipRect.width > viewportWidth - 10) {
                left = viewportWidth - actualTooltipRect.width - 10;
            }
            
            // Adjust vertical position if tooltip would go off-screen
            if (top < 10) {
                // Show below the event instead
                top = rect.bottom + 10;
                // Flip the arrow by adjusting CSS
                tooltip.style.setProperty('--arrow-position', 'top');
            } else {
                top = top - actualTooltipRect.height;
                tooltip.style.setProperty('--arrow-position', 'bottom');
            }
            
            // Apply final position
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
            tooltip.style.opacity = '1';
        }
        
        function hideEventTooltip() {
            const tooltip = document.getElementById('eventTooltip');
            tooltip.classList.remove('show');
            // Reset all inline styles set by JavaScript
            tooltip.style.display = 'none';
            tooltip.style.opacity = '0';
            tooltip.style.left = '';
            tooltip.style.top = '';
        }
        
        function showDetailPanel(extendedProps) {
            const panel = document.getElementById('detailPanel');
            const title = document.getElementById('detailPanelTitle');
            const subtitle = document.getElementById('detailPanelSubtitle');
            const content = document.getElementById('detailPanelContent');
            
            if (extendedProps.booking) {
                showBookingDetailPanel(extendedProps);
            } else {
                showEventDetailPanel(extendedProps);
            }
            
            panel.classList.add('show');
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
                    calendar.refetchEvents();
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
