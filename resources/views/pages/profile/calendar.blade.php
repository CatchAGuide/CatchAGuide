@extends('pages.profile.layouts.profile')

@section('title', __('profile.calendar'))







@section('profile-content')

    <!-- Calendar Layout -->
    <div class="row">
        <!-- Calendar Panel -->
        <div class="col-12">
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
    </div>

    <!-- Bottom Row: Tour Filter (Left) and Details (Right) -->
    <div class="row mt-4">
        <!-- Tour Filter Panel -->
        <div class="col-md-6">
            <div class="tour-filter-panel">
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
        </div>
        
        <!-- Details Panel -->
        <div class="col-md-6">
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



@endsection

<style>
/* Essential Calendar Styles - Inline to avoid css_after conflicts */
.calendar-panel, .tour-filter-panel {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    height: fit-content;
}

.calendar-container {
    min-height: 450px;
    position: relative;
}

.calendar-loading-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    border-radius: 12px;
}

.calendar-loading-overlay.show { display: flex; }

.spinner {
    width: 40px; height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #313041;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.calendar-legend {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
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
    width: 14px; height: 14px;
    border-radius: 3px;
    border: 1px solid;
}

.legend-accepted { background: #28a745; border-color: #28a745; }
.legend-pending { background: #ffc107; border-color: #ffc107; }
.legend-rejected { background: #dc3545; border-color: #dc3545; }
.legend-custom { background: #17a2b8; border-color: #17a2b8; }
.legend-available { background: #28a745; border-color: #28a745; }
.legend-unavailable { background: #dc3545; border-color: #dc3545; }

/* Litepicker Calendar Day Styling */
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

/* Custom Event Color */
.litepicker .day-item.custom-event {
    background-color: #17a2b8 !important;
    border: 2px solid #17a2b8 !important;
    color: white !important;
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

/* Status Badge Styles */
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

.side-detail-panel {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
}

.detail-panel-header {
    background: linear-gradient(135deg, #313041, #2c5aa0);
    color: white;
    padding: 12px 15px;
    position: relative;
}

.close-panel-btn {
    position: absolute;
    top: 8px; right: 12px;
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

/* Tour Filter Dropdown Styles */
.tour-filter-card-dropdown {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
}

.tour-filter-image-dropdown, .tour-filter-icon-dropdown {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.tour-filter-icon-dropdown {
    background: #313041;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.tour-filter-thumbnail-dropdown {
    width: 100%;
    height: 100%;
    object-fit: cover;
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

.tour-filter-location-dropdown, .tour-filter-price-dropdown, .tour-filter-subtitle-dropdown {
    font-size: 12px;
    color: #6c757d;
    display: block;
    margin-bottom: 2px;
}

.tour-filter-option.active {
    background-color: rgba(49, 48, 65, 0.1);
}
</style>

@section('js_after')
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
            initializeCalendar();
            initializeTourDropdown();
            loadCalendarEvents();
            updateLegendDisplay();
        });

        function initializeCalendar() {
            // Get blocked events for calendar
            const blockedEvents = @json($blocked_events ?? []);
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
                    calendarContainer.addEventListener('click', function(event) {
                        const dayElement = event.target.closest('.day-item');
                        if (dayElement && !dayElement.classList.contains('is-locked')) {
                            const dayText = dayElement.textContent.trim();
                            if (dayText && /^\d+$/.test(dayText)) {
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
                    });
                    
                    // Monitor DOM changes to reapply colors
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
            startDate.setMonth(startDate.getMonth() - 6);
            const endDate = new Date();
            endDate.setMonth(endDate.getMonth() + 6);
            
            const baseParams = {
                start: startDate.toISOString().split('T')[0],
                end: endDate.toISOString().split('T')[0]
            };
            
            // If a specific tour is selected, make two requests
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
                    
                    const allEvents = [];
                    
                    if (Array.isArray(tourEvents)) {
                        allEvents.push(...tourEvents);
                    }
                    
                    if (Array.isArray(customEvents)) {
                        const existingEventIds = new Set(allEvents.map(e => e.id));
                        const uniqueCustomEvents = customEvents.filter(e => !existingEventIds.has(e.id));
                        allEvents.push(...uniqueCustomEvents);
                    }
                    
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
                // Mock events for demonstration (replace with actual API call)
                calendarEvents = {};
                updateCalendarDisplay();
            }
        }

        function updateCalendarDisplay() {
            const loadingOverlay = document.getElementById('calendarLoadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('show');
            }
            
            setTimeout(() => {
                const allDayElements = document.querySelectorAll('.day-item');
                
                // Reset all day elements
                allDayElements.forEach(dayEl => {
                    dayEl.classList.remove('booking-accepted', 'booking-pending', 'booking-rejected', 'booking-cancelled', 'custom-event', 'blocked-tour', 'tour-available', 'tour-blocked');
                    dayEl.style.backgroundColor = '';
                    dayEl.style.border = '';
                    dayEl.style.color = '';
                    dayEl.style.cursor = 'pointer';
                });
                
                // Apply default availability colors if a specific tour is selected
                if (currentFilters.guiding_id !== '') {
                    allDayElements.forEach(dayEl => {
                        if (dayEl.textContent.trim() && /^\d+$/.test(dayEl.textContent.trim())) {
                            const dayNumber = parseInt(dayEl.textContent.trim());
                            const monthContainer = dayEl.closest('.month-item');
                            
                            if (monthContainer) {
                                const monthHeader = monthContainer.querySelector('.month-item-header div');
                                if (monthHeader) {
                                    const headerText = monthHeader.textContent.trim();
                                    
                                    const yearMatch = headerText.match(/\d{4}/);
                                    const year = yearMatch ? parseInt(yearMatch[0]) : new Date().getFullYear();
                                    
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
                
                // Apply event colors
                const monthContainers = document.querySelectorAll('.month-item');
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
                    
                    const isMonthVisible = visibleMonths.some(visibleMonth => {
                        const cleanVisible = visibleMonth.toLowerCase().replace(/\s+/g, '');
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
                        
                        return possibleNames.some(name => 
                            cleanVisible.includes(name) && cleanVisible.includes(targetYear)
                        );
                    });
                    
                    if (!isMonthVisible) {
                        return;
                    }
                    
                    let foundDayElements = [];
                    
                    monthContainers.forEach(monthContainer => {
                        const monthHeader = monthContainer.querySelector('.month-item-header div');
                        if (monthHeader) {
                            const headerText = monthHeader.textContent.trim();
                            
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
                    
                    foundDayElements.forEach((dayEl) => {
                        const bookings = events.filter(e => e.extendedProps && e.extendedProps.booking);
                        const blockedTours = events.filter(e => e.extendedProps && e.extendedProps.type && 
                            (e.extendedProps.type === 'tour_schedule' || e.extendedProps.type === 'vacation_schedule'));
                        const customEvents = events.filter(e => e.extendedProps && e.extendedProps.type === 'custom_schedule');
                        
                        dayEl.classList.remove('booking-accepted', 'booking-pending', 'booking-rejected', 'booking-cancelled', 'custom-event', 'blocked-tour', 'tour-available', 'tour-blocked');
                        
                        if (bookings.length > 0) {
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
                            dayEl.classList.remove('tour-available', 'tour-blocked');
                            dayEl.classList.add('custom-event');
                        } else {
                            if (currentFilters.guiding_id !== '') {
                                if (blockedTours.length > 0) {
                                    dayEl.classList.add('tour-blocked');
                                } else {
                                    if (!dayEl.classList.contains('tour-available') && !dayEl.classList.contains('tour-blocked')) {
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
                        
                        if (blockedTours.length > 0 && currentFilters.guiding_id !== '') {
                            dayEl.classList.add('blocked-tour');
                        }
                        
                        dayEl.style.cursor = 'pointer';
                    });
                });
                
                if (loadingOverlay) {
                    loadingOverlay.classList.remove('show');
                }
            }, 1500);
        }

        function initializeTourDropdown() {
            document.querySelectorAll('.tour-filter-option').forEach(option => {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    document.querySelectorAll('.tour-filter-option').forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    
                    const guidingId = this.dataset.guidingId;
                    currentFilters.guiding_id = guidingId;
                    
                    const selectedText = guidingId ? 
                        this.querySelector('.tour-filter-title-dropdown').textContent : 
                        'All Tours';
                    document.getElementById('selectedTourText').textContent = selectedText;
                    
                    // Show/hide legend items based on filter context
                    updateLegendDisplay();
                    
                    // Refresh calendar
                    loadCalendarEvents();
                    
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
                // When a specific tour is selected, show availability states
                if (availableLegend) availableLegend.style.display = 'flex';
                if (unavailableLegend) unavailableLegend.style.display = 'flex';
                if (confirmedLegend) confirmedLegend.style.display = 'none';
                if (pendingLegend) pendingLegend.style.display = 'flex';
                if (rejectedLegend) rejectedLegend.style.display = 'none';
                if (customLegend) customLegend.style.display = 'flex';
            } else {
                // When showing all tours, show booking states
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
            
            const date = new Date(dateStr);
            const formattedDate = date.toLocaleDateString('{{app()->getLocale()}}', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            title.textContent = 'Schedule for Date';
            dateElement.textContent = formattedDate;
            
            const dayEvents = calendarEvents[dateStr] || [];
            
            if (dayEvents.length === 0) {
                content.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-calendar-check fa-2x mb-2"></i>
                        <p class="small">No events scheduled for this date</p>
                        <p class="small">Selected: ${dateStr}</p>
                    </div>
                `;
            } else {
                let contentHtml = '<div class="side-detail-cards">';
                
                const bookings = dayEvents.filter(e => e.extendedProps && e.extendedProps.booking);
                const customEvents = dayEvents.filter(e => e.extendedProps && e.extendedProps.type === 'custom_schedule');
                const otherEvents = dayEvents.filter(e => !e.extendedProps || (!e.extendedProps.booking && 
                    e.extendedProps.type !== 'tour_schedule' && e.extendedProps.type !== 'vacation_schedule' && 
                    e.extendedProps.type !== 'custom_schedule'));
                
                if (bookings.length > 0) {
                    contentHtml += `<div class="mb-3"><h6><i class="fas fa-calendar-check"></i> Bookings (${bookings.length})</h6>`;
                    bookings.forEach(event => {
                        const booking = event.extendedProps.booking;
                        const user = event.extendedProps.user;
                        const guiding = event.extendedProps.guiding;
                        
                        contentHtml += `
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <small class="fw-bold">${guiding ? guiding.title : 'Tour Booking'}</small>
                                    <span class="status-badge status-${booking.status}">${booking.status}</span>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="fas fa-user"></i> ${user ? user.firstname + ' ' + user.lastname : 'Guest User'}<br>
                                    <i class="fas fa-users"></i> ${booking.count_of_users} guests<br>
                                    <i class="fas fa-euro-sign"></i> ${booking.price}€
                                </small>
                            </div>
                        `;
                    });
                    contentHtml += '</div>';
                }
                
                if (customEvents.length > 0) {
                    contentHtml += `<div class="mb-3"><h6><i class="fas fa-calendar-plus text-info"></i> Custom Events (${customEvents.length})</h6>`;
                    customEvents.forEach(event => {
                        contentHtml += `
                            <div class="border rounded p-2 mb-2">
                                <small class="fw-bold">${event.title || 'Custom Event'}</small><br>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i> ${event.extendedProps.date || dateStr}<br>
                                    ${event.extendedProps.note ? `<i class="fas fa-note-sticky"></i> ${event.extendedProps.note}` : ''}
                                </small>
                            </div>
                        `;
                    });
                    contentHtml += '</div>';
                }
                
                contentHtml += '</div>';
                content.innerHTML = contentHtml;
            }
            
            panel.style.display = 'block';
        }

        function closeSideDetailPanel() {
            document.getElementById('sideDetailPanel').style.display = 'none';
        }

        function getTypeLabel(type) {
            const labels = {
                'tour_request': 'Booking',
                'tour_schedule': 'Blocked',
                'vacation_schedule': 'Vacation',
                'vacation_request': 'Vacation Request',
                'custom_schedule': 'Custom'
            };
            return labels[type] || type;
        }

        function showAlert(type, message) {
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
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
    </script>
@endsection


