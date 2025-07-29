@extends('pages.profile.layouts.profile')

@section('title', __('profile.calendar'))

@section('profile-content')

    <!-- Header Section -->
    <div class="calendar-header">
        <h1 class="mb-0 text-white">
            <i class="fas fa-calendar-alt"></i>
            @lang('profile.calendar')
        </h1>
        <p class="mb-0 mt-2 text-white">Manage your availability and schedule for fishing trips</p>
    </div>

    <!-- Bottom Row: Tour Filter (Left) and Details (Right) -->
    <div class="row mt-4 mb-4">
        <!-- Tour Filter Panel -->
        <div class="col-md-6">
            <div class="tour-filter-panel">
                <h3 class="mb-3">@lang('profile.select-tour-to-filter')</h3>
                
                <!-- Tour Filter Dropdown -->
                <div class="dropdown mb-3">
                    <button class="btn btn-outline-secondary dropdown-toggle w-100 tour-filter-btn" type="button" id="tourFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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

    <!-- Calendar Layout -->
    <div class="row mt-4">
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
    
    <!-- iCal Integration Section -->
    <div class="row mt-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>
                        @lang('profile.calendar_integrations')
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <p class="text-muted mb-3">Manage your calendar integrations - import external feeds and share your calendar</p>
                        
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <!-- Import Button -->
                            <button type="button" class="btn btn-outline-primary" onclick="showICalImportModal()">
                                <i class="fas fa-download me-2"></i>
                                Import External Calendar
                            </button>
                            
                            <!-- Export Button -->
                            <button type="button" class="btn btn-outline-success" onclick="showGenerateICalModal()">
                                <i class="fas fa-share-alt me-2"></i>
                                Share Your Calendar
                            </button>
                            
                            <!-- Manage Feeds Button -->
                            <button type="button" class="btn btn-outline-info" onclick="showUnifiedICalModal()">
                                <i class="fas fa-list me-2"></i>
                                Manage All Feeds
                            </button>
                            
                            <!-- Sync All Button -->
                            <button type="button" class="btn btn-primary" onclick="syncAllIntegrations()">
                                <i class="fas fa-sync me-2"></i>
                                Sync All
                            </button>
                        </div>
                    </div>
                    
                    <!-- Status Summary -->
                    <div class="row text-center">
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="text-primary">
                                    <i class="fas fa-download me-2"></i>
                                    Import Status
                                </h6>
                                <div class="d-flex justify-content-center gap-3">
                                    <span class="badge bg-success">
                                        Active: <span id="importCount">0</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <h6 class="text-success">
                                    <i class="fas fa-share-alt me-2"></i>
                                    Export Status
                                </h6>
                                <div class="d-flex justify-content-center gap-3">
                                    <span class="badge bg-info">
                                        Active: <span id="exportCount">0</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unified iCal Management Modal -->
    <div class="modal fade" id="unifiedICalModal" tabindex="-1" aria-labelledby="unifiedICalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unifiedICalModalLabel">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Manage iCal Feeds
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="iCalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="import-tab" data-bs-toggle="tab" data-bs-target="#import-tab-pane" type="button" role="tab" aria-controls="import-tab-pane" aria-selected="true">
                                <i class="fas fa-download me-2"></i>
                                Import Feeds
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="export-tab" data-bs-toggle="tab" data-bs-target="#export-tab-pane" type="button" role="tab" aria-controls="export-tab-pane" aria-selected="false">
                                <i class="fas fa-share-alt me-2"></i>
                                Export Feeds
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="iCalTabsContent">
                        <!-- Import Tab -->
                        <div class="tab-pane fade show active" id="import-tab-pane" role="tabpanel" aria-labelledby="import-tab" tabindex="0">
                            <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                                <h6 class="mb-0">Imported iCal Feeds</h6>
                                <button type="button" class="btn btn-primary btn-sm" onclick="showICalImportModal()">
                                    <i class="fas fa-plus me-1"></i> Add New Feed
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover" id="importFeedsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>URL</th>
                                            <th>Last Sync</th>
                                            <th>Events</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="importFeedsTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                                                <p>No import feeds configured yet</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Export Tab -->
                        <div class="tab-pane fade" id="export-tab-pane" role="tabpanel" aria-labelledby="export-tab" tabindex="0">
                            <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
                                <h6 class="mb-0">Generated iCal Feeds</h6>
                                <button type="button" class="btn btn-success btn-sm" onclick="showGenerateICalModal()">
                                    <i class="fas fa-plus me-1"></i> Generate New Feed
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover" id="exportFeedsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Public URL</th>
                                            <th>Current OTP</th>
                                            <th>Expires</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="exportFeedsTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                                                <p>No export feeds generated yet</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="syncAllIntegrations()">
                        <i class="fas fa-sync me-1"></i> Sync All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- iCal Import Modal -->
    <div class="modal fade" id="icalImportModal" tabindex="-1" aria-labelledby="icalImportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="icalImportModalLabel">@lang('profile.add_ical_feed')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="icalImportForm">
                        <div class="mb-3">
                            <label for="ical_feed_name" class="form-label">@lang('profile.ical_feed_name') <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ical_feed_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="ical_feed_url" class="form-label">@lang('profile.ical_feed_url') <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="url" class="form-control" id="ical_feed_url" name="feed_url" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="validateICalUrl()">
                                    <i class="fas fa-check"></i> @lang('profile.validate_url')
                                </button>
                            </div>
                            <div id="urlValidationResult" class="mt-2"></div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>@lang('profile.ical_import_title'):</strong>
                            <ul class="mb-0 mt-2">
                                <li>@lang('profile.ical_import_benefit_1')</li>
                                <li>@lang('profile.ical_import_benefit_2')</li>
                                <li>@lang('profile.ical_import_benefit_3')</li>
                            </ul>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('profile.cancel')</button>
                    <button type="button" class="btn btn-primary" id="saveICalBtn" onclick="saveICalFeed()" style="display: none;">
                        <i class="fas fa-save"></i> @lang('profile.add_feed')
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- iCal Feeds List Modal -->
    <div class="modal fade" id="icalFeedsListModal" tabindex="-1" aria-labelledby="icalFeedsListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="icalFeedsListModalLabel">@lang('profile.manage_ical_feeds')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="icalFeedsList">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                            <p class="mt-2">Loading feeds...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('profile.close')</button>
                    <button type="button" class="btn btn-primary" onclick="openICalImportModal()">
                        <i class="fas fa-plus"></i> @lang('profile.add_ical_feed')
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate iCal Feed Modal -->
    <div class="modal fade" id="generateICalModal" tabindex="-1" aria-labelledby="generateICalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateICalModalLabel">@lang('profile.generate_new_ical_feed')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="generateICalForm">
                        <div class="mb-3">
                            <label for="feed_name" class="form-label">@lang('profile.feed_name') *</label>
                            <input type="text" class="form-control" id="feed_name" name="name" required>
                            <div class="form-text">@lang('profile.feed_name_help')</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="feed_type" class="form-label">@lang('profile.feed_type') *</label>
                            <select class="form-select" id="feed_type" name="feed_type" required>
                                <option value="bookings_only">@lang('profile.bookings_only')</option>
                                <option value="all_events">@lang('profile.all_events')</option>
                                <option value="custom_schedule">@lang('profile.custom_schedule')</option>
                            </select>
                            <div class="form-text">@lang('profile.feed_type_help')</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="expires_at" class="form-label">@lang('profile.expires_at')</label>
                            <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                            <div class="form-text">@lang('profile.expires_at_help')</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('profile.cancel')</button>
                    <button type="button" class="btn btn-primary" onclick="generateICalFeed()">
                        <i class="fas fa-plus"></i> @lang('profile.generate_feed')
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User iCal Feeds List Modal -->
    <div class="modal fade" id="userICalFeedsListModal" tabindex="-1" aria-labelledby="userICalFeedsListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userICalFeedsListModalLabel">@lang('profile.manage_user_ical_feeds')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="userICalFeedsList">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('profile.close')</button>
                </div>
            </div>
        </div>
    </div>

@endsection

<style>
/* Essential Calendar Styles - Inline to avoid css_after conflicts */

/* Calendar Header Styles */
.calendar-header {
    background: linear-gradient(135deg, #313041, #252238);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    color: white;
    position: relative;
    overflow: hidden;
}

.calendar-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
    opacity: 0.5;
    animation: calendar-float 20s infinite linear;
}

@keyframes calendar-float {
    0% { transform: translateX(-100px) translateY(-100px); }
    100% { transform: translateX(100px) translateY(100px); }
}

.calendar-header h1 {
    font-size: 2rem;
    font-weight: 700;
    position: relative;
    z-index: 2;
}

.calendar-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    position: relative;
    z-index: 2;
}

.calendar-header i {
    margin-right: 10px;
}

/* Integration Cards Styling */
.integration-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.integration-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 16px;
    height: 100%;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.integration-section:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.integration-header {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
    gap: 12px;
}

.integration-logo {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #f8f9fa;
}

.integration-info {
    flex: 1;
    min-width: 0;
}

.integration-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 4px;
    color: #212529;
}

.integration-description {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0;
}

.integration-status {
    flex-shrink: 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge.connected {
    background: #d1e7dd;
    color: #0f5132;
}

.status-badge.disconnected {
    background: #f8d7da;
    color: #721c24;
}

.integration-content {
    margin-bottom: 16px;
}

.integration-benefits {
    font-size: 0.875rem;
    color: #495057;
    margin-bottom: 8px;
}

.integration-benefits:last-child {
    margin-bottom: 0;
}

.integration-actions {
    margin-top: auto;
}

.btn-group .btn {
    flex: 1;
}

/* Tour Filter Button Styles */
.tour-filter-btn {
    background: white !important;
    border: 2px solid #e9ecef !important;
    color: #495057 !important;
    font-weight: 500;
    transition: all 0.3s ease;
}

.tour-filter-btn:hover,
.tour-filter-btn:focus,
.tour-filter-btn:active,
.tour-filter-btn.show {
    background: #f8f9fa !important;
    border-color: #313041 !important;
    color: #313041 !important;
    box-shadow: 0 0 0 0.2rem rgba(49, 48, 65, 0.1) !important;
}

/* Calendar Panel Styles */
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

/* Side Detail Panel */
.side-detail-panel {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    max-height: 500px;
}

.detail-panel-header {
    background: linear-gradient(135deg, #313041, #2c5aa0);
    color: white !important;
    padding: 12px 15px;
    position: relative;
}

.detail-panel-header h5,
.detail-panel-header p {
    color: white !important;
    margin: 0;
}

.detail-panel-header h5 {
    font-size: 16px;
    font-weight: 600;
}

.detail-panel-body {
    padding: 15px;
    max-height: 400px;
    overflow-y: auto;
}

.close-panel-btn {
    position: absolute;
    top: 8px; right: 12px;
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.close-panel-btn:hover {
    transform: scale(1.2);
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .calendar-panel, .tour-filter-panel {
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .calendar-container {
        min-height: 400px;
    }
    
    .integration-section {
        margin-bottom: 20px;
    }
    
    .side-detail-panel {
        max-height: 300px;
    }
    
    .detail-panel-body {
        max-height: 250px;
        padding: 10px;
    }
}

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

.litepicker .day-item.is-today {
    border: 3px solid #fd7e14 !important;
    box-shadow: 0 0 0 2px rgba(253, 126, 20, 0.2) !important;
    font-weight: bold !important;
    position: relative !important;
}

.litepicker .day-item.is-today::before {
    content: '';
    position: absolute !important;
    top: -2px !important;
    left: -2px !important;
    right: -2px !important;
    bottom: -2px !important;
    border: 2px solid #fd7e14 !important;
    border-radius: 8px !important;
    opacity: 0.5 !important;
    z-index: -1 !important;
    animation: pulse-today 2s infinite !important;
}

@keyframes pulse-today {
    0% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 0.8; transform: scale(1.02); }
    100% { opacity: 0.5; transform: scale(1); }
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

/* Ensure selected state takes priority over today */
.litepicker .day-item.is-today.is-selected,
.litepicker .day-item.is-today.is-start-date,
.litepicker .day-item.is-today.is-end-date {
    border: 3px solid #000 !important;
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

/* Scrollable Dropdown Menu */
.dropdown-menu {
    max-height: 300px !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    border: 1px solid #e9ecef !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    padding: 8px 0 !important;
}

/* Custom Scrollbar for Dropdown */
.dropdown-menu::-webkit-scrollbar {
    width: 6px;
}

.dropdown-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.dropdown-menu::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Dropdown Item Styling */
.dropdown-menu .dropdown-item {
    padding: 8px 16px !important;
    transition: all 0.2s ease !important;
    border-radius: 6px !important;
    margin: 2px 8px !important;
    border: none !important;
}

.dropdown-menu .dropdown-item:hover,
.dropdown-menu .dropdown-item:focus {
    background-color: #f8f9fa !important;
    color: #313041 !important;
}

.dropdown-menu .dropdown-item.active {
    background-color: #313041 !important;
    color: white !important;
}

/* Integration Icons */
.integration-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

/* Unified Integration Card */
.card-body .row .col-md-6:first-child {
    border-right: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .card-body .row .col-md-6:first-child {
        border-right: none;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
}
</style>

@section('js_after')
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
    <script>
        // iCal Integration Functions
        function openICalImportModal() {
            const modal = new bootstrap.Modal(document.getElementById('icalImportModal'));
            modal.show();
        }

        // Validate iCal URL
        function validateICalUrl() {
            const url = document.getElementById('ical_feed_url').value.trim();
            const button = document.getElementById('validateUrlBtn');
            const resultDiv = document.getElementById('urlValidationResult');
            
            if (!url) {
                showAlert('error', 'Please enter a URL to validate');
                return;
            }
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validating...';
            
            fetch('{{ route("ical-feeds.validate-url") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ url: url })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ${data.message}
                        </div>
                    `;
                    resultDiv.style.display = 'block';
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> ${data.message}
                        </div>
                    `;
                    resultDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Failed to validate URL. Please try again.
                    </div>
                `;
                resultDiv.style.display = 'block';
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-check"></i> Validate URL';
            });
        }

        // Add iCal Feed
        function addICalFeed() {
            const name = document.getElementById('ical_feed_name').value.trim();
            const url = document.getElementById('ical_feed_url').value.trim();
            
            if (!name || !url) {
                showAlert('error', 'Please fill in all required fields');
                return;
            }
            
            const button = document.getElementById('addFeedBtn');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            fetch('{{ route("ical-feeds.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: name,
                    url: url
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    $('#icalImportModal').modal('hide');
                    loadICalFeeds();
                    // Reset form
                    document.getElementById('ical_feed_name').value = '';
                    document.getElementById('ical_feed_url').value = '';
                    document.getElementById('urlValidationResult').style.display = 'none';
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to add iCal feed. Please try again.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-plus"></i> Add Feed';
            });
        }

        // Sync all iCal feeds
        function syncAllICalFeeds() {
            const button = document.getElementById('syncAllFeedsBtn');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
            
            fetch('{{ route("ical-feeds.sync-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    loadICalFeeds();
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to sync feeds. Please try again.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-sync"></i> Sync All';
            });
        }

        // Load iCal feeds
        function loadICalFeeds() {
            // Show the modal first
            $('#icalFeedsListModal').modal('show');
            
            // Then load the feeds
            fetch('{{ route("ical-feeds.index") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                const feedsList = document.getElementById('icalFeedsList');
                if (data.feeds && data.feeds.length > 0) {
                    feedsList.innerHTML = data.feeds.map(feed => `
                        <div class="feed-item border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${feed.name}</h6>
                                    <small class="text-muted d-block mb-2">${feed.url}</small>
                                    <div class="d-flex gap-2">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> Last sync: ${feed.last_sync || 'Never'}
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> Events: ${feed.events_count || 0}
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="syncFeed(${feed.id})">
                                        <i class="fas fa-sync"></i> Sync
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteFeed(${feed.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    feedsList.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <p>No iCal feeds configured yet</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('icalFeedsList').innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Failed to load feeds</p>
                    </div>
                `;
            });
        }

        // Generate new iCal feed
        function generateNewICalFeed() {
            const name = document.getElementById('generate_feed_name').value.trim();
            const type = document.getElementById('generate_feed_type').value;
            const expiresAt = document.getElementById('generate_expires_at').value;
            
            if (!name) {
                showAlert('error', 'Please enter a feed name');
                return;
            }
            
            const button = document.getElementById('generateFeedBtn');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            
            fetch('{{ route("user-ical-feeds.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: name,
                    type: type,
                    expires_at: expiresAt || null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    $('#generateICalModal').modal('hide');
                    loadUserICalFeeds();
                    // Reset form
                    document.getElementById('generate_feed_name').value = '';
                    document.getElementById('generate_feed_type').value = 'all_events';
                    document.getElementById('generate_expires_at').value = '';
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to generate iCal feed. Please try again.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-plus"></i> Generate Feed';
            });
        }

        // Load user iCal feeds
        function loadUserICalFeeds() {
            fetch('{{ route("user-ical-feeds.index") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                const feedsList = document.getElementById('userICalFeedsList');
                if (data.feeds && data.feeds.length > 0) {
                    feedsList.innerHTML = data.feeds.map(feed => `
                        <div class="feed-item border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${feed.name}</h6>
                                    <small class="text-muted d-block mb-2">
                                        <strong>Type:</strong> ${feed.type === 'bookings_only' ? 'Bookings Only' : 'All Events'}
                                    </small>
                                    <div class="d-flex gap-3 mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> Created: ${feed.created_at}
                                        </small>
                                        ${feed.expires_at ? `
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-times"></i> Expires: ${feed.expires_at}
                                            </small>
                                        ` : ''}
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted d-block">
                                            <strong>Public URL:</strong> 
                                            <code class="bg-light px-2 py-1 rounded">${feed.public_url}</code>
                                        </small>
                                        <small class="text-muted d-block">
                                            <strong>Secure URL:</strong> 
                                            <code class="bg-light px-2 py-1 rounded">${feed.secure_url}</code>
                                        </small>
                                        <small class="text-muted d-block">
                                            <strong>Current OTP:</strong> 
                                            <code class="bg-light px-2 py-1 rounded">${feed.current_otp}</code>
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" onclick="regenerateOTP(${feed.id})">
                                        <i class="fas fa-key"></i> New OTP
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUserFeed(${feed.id})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    feedsList.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <p>No iCal feeds generated yet</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('userICalFeedsList').innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <p>Failed to load feeds</p>
                    </div>
                `;
            });
        }

        function saveICalFeed() {
            const form = document.getElementById('icalImportForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // Set default sync type to 'all_events' for import
            data.sync_type = 'all_events';

            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            button.disabled = true;
            
            fetch('{{ route("ical-feeds.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('icalImportModal'));
                    modal.hide();
                    form.reset();
                    document.getElementById('saveICalBtn').style.display = 'none';
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to save iCal feed. Please try again.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function syncICalFeed(feedId) {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            fetch(`{{ url('ical/feeds') }}/${feedId}/sync`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    loadICalFeeds(); // Reload the list
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to sync feed. Please try again.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function deleteICalFeed(feedId) {
            if (!confirm('Are you sure you want to delete this iCal feed? This action cannot be undone.')) {
                return;
            }

            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            fetch(`{{ url('ical/feeds') }}/${feedId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    loadICalFeeds(); // Reload the list
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to delete feed. Please try again.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        // Generate iCal Feed Functions
        function openGenerateICalModal() {
            const modal = new bootstrap.Modal(document.getElementById('generateICalModal'));
            modal.show();
        }

        function generateICalFeed() {
            const form = document.getElementById('generateICalForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // Remove empty expires_at if not set
            if (!data.expires_at) {
                delete data.expires_at;
            }

            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            button.disabled = true;
            
            fetch('{{ route("user-ical-feeds.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    const modal = bootstrap.Modal.getInstance(document.getElementById('generateICalModal'));
                    modal.hide();
                    form.reset();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to generate iCal feed. Please try again.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function regenerateUserICalToken(feedId) {
            if (!confirm('Are you sure you want to regenerate the token? This will invalidate the current URL.')) {
                return;
            }

            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            fetch(`{{ url('user-ical/feeds') }}/${feedId}/regenerate-token`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    loadUserICalFeeds(); // Reload the list
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to regenerate token. Please try again.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function deleteUserICalFeed(feedId) {
            if (!confirm('Are you sure you want to delete this iCal feed? This action cannot be undone.')) {
                return;
            }

            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            fetch(`{{ url('user-ical/feeds') }}/${feedId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    loadUserICalFeeds(); // Reload the list
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to delete feed. Please try again.');
            })
            .finally(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showAlert('success', 'Copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showAlert('success', 'Copied to clipboard!');
            });
        }

        function showAlert(type, message) {
            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.maxWidth = '400px';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }

        // Calendar initialization and other functions would go here...
        // (I'm keeping this focused on the iCal integration functions)

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
            
            // Load actual events from API
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
            if (currentFilters.guiding_id && currentFilters.guiding_id !== '' && currentFilters.guiding_id !== null) {
                const tourEventsPromise = fetch('/events?' + new URLSearchParams({
                    ...baseParams,
                    ...currentFilters
                }))
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                });
                
                const customEventsPromise = fetch('/events?' + new URLSearchParams({
                    ...baseParams
                }))
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                });
                
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
                    
                    // Process events and organize by date
                    allEvents.forEach(event => {
                        if (event && event !== null) {
                            const dateKey = event.start ? event.start.split('T')[0] : (event.end ? event.end.split('T')[0] : null);
                            if (dateKey) {
                                if (!calendarEvents[dateKey]) {
                                    calendarEvents[dateKey] = [];
                                }
                                calendarEvents[dateKey].push(event);
                            }
                        }
                    });
                    
                    console.log('Loaded calendar events:', calendarEvents);
                    updateCalendarDisplay();
                })
                .catch(error => {
                    console.error('Error loading calendar events:', error);
                    showAlert('error', 'Failed to load calendar events. Please check your connection.');
                });
            } else {
                // When showing all tours (default), load all events
                fetch('/events?' + new URLSearchParams({
                    ...baseParams,
                    ...currentFilters
                }))
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    calendarEvents = {};
                    
                    console.log('API Response:', data);
                    
                    if (Array.isArray(data)) {
                        data.forEach(event => {
                            if (event && event !== null) {
                                const dateKey = event.start ? event.start.split('T')[0] : (event.end ? event.end.split('T')[0] : null);
                                if (dateKey) {
                                    if (!calendarEvents[dateKey]) {
                                        calendarEvents[dateKey] = [];
                                    }
                                    calendarEvents[dateKey].push(event);
                                }
                            }
                        });
                    } else {
                        console.error('Events data is not an array:', data);
                    }
                    
                    console.log('Processed calendar events:', calendarEvents);
                    updateCalendarDisplay();
                })
                .catch(error => {
                    console.error('Error loading calendar events:', error);
                    showAlert('error', 'Failed to load calendar events. Please check your connection.');
                });
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
                if (currentFilters.guiding_id !== '' && currentFilters.guiding_id !== null && currentFilters.guiding_id !== undefined) {
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

                                if (currentFilters.guiding_id !== '' && currentFilters.guiding_id !== null && currentFilters.guiding_id !== undefined) {
                                    dayEl.classList.add('booking-rejected');
                                }
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
                            if (currentFilters.guiding_id !== '' && currentFilters.guiding_id !== null && currentFilters.guiding_id !== undefined) {
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
                        
                        if (blockedTours.length > 0 && currentFilters.guiding_id !== '' && currentFilters.guiding_id !== null && currentFilters.guiding_id !== undefined) {
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
            
            if (currentFilters.guiding_id !== '' && currentFilters.guiding_id !== null && currentFilters.guiding_id !== undefined) {
                // When a specific tour is selected, show availability states
                if (availableLegend) availableLegend.style.display = 'flex';
                if (unavailableLegend) unavailableLegend.style.display = 'flex';
                if (confirmedLegend) confirmedLegend.style.display = 'none';
                if (pendingLegend) pendingLegend.style.display = 'flex';
                if (rejectedLegend) rejectedLegend.style.display = 'none';
                if (customLegend) customLegend.style.display = 'flex';
            } else {
                // When showing all tours (default), show booking states
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
                weekday: 'short',
                year: 'numeric',
                month: 'short',
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
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <small class="fw-bold me-2">${guiding ? guiding.title : 'Tour Booking'}</small>
                                <div class="booking-id-tag me-2">
                                    <i class="fas fa-hashtag"></i>
                                    <strong>ID: ${booking.id}</strong>
                                </div>
                            </div>
                            <span class="status-badge status-${booking.status} text-uppercase">${booking.status}</span>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">
                                <i class="fas fa-user me-1"></i> ${user ? user.firstname + ' ' + user.lastname : 'Guest User'}
                            </small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-users me-1"></i> ${booking.count_of_users} guests
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-euro-sign me-1"></i> ${booking.price}€
                            </small>
                        </div>
                        ${booking.status === 'pending' && {{ auth()->user()->is_guide }} ? `
                            <div class="d-flex justify-content-end mt-2">
                                <button class="btn btn-sm btn-success me-2" onclick="window.location.href='/profile/guidebookings/accept/${booking.id}'">
                                    <i class="fas fa-check"></i> Accept
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="window.location.href='/profile/guidebookings/reject/${booking.id}'">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        ` : ''}
                    </div>
                `;
                    });
                    contentHtml += '</div>';
                }
                
                if (customEvents.length > 0) {
                    contentHtml += `<div class="mb-3"><h6><i class="fas fa-calendar-plus text-info"></i> Custom Events (${customEvents.length})</h6>`;
                    customEvents.forEach(event => {
                        const eventId = event.id || event.extendedProps?.id;
                        contentHtml += `
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <small class="fw-bold">${event.title || 'Custom Event'}</small><br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> ${event.extendedProps.date || dateStr}<br>
                                            ${event.extendedProps.note ? `<i class="fas fa-note-sticky"></i> ${event.extendedProps.note}` : ''}
                                        </small>
                                    </div>
                                </div>
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

        // Show iCal Import Modal
        function showICalImportModal() {
            $('#icalImportModal').modal('show');
        }

        // Show Generate iCal Modal
        function showGenerateICalModal() {
            $('#generateICalModal').modal('show');
        }

        // Sync All Integrations
        function syncAllIntegrations() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Syncing...';
            
            // Sync import feeds
            fetch('{{ route("ical-feeds.sync-all") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'All integrations synced successfully');
                    updateIntegrationCounts();
                } else {
                    showAlert('error', data.message || 'Failed to sync integrations');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to sync integrations. Please try again.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        // Update Integration Counts
        function updateIntegrationCounts() {
            // Update import count
            fetch('{{ route("ical-feeds.index") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Import count data:', data); // Debug log
                const feeds = data.feeds || data.data || data || [];
                const importCount = Array.isArray(feeds) ? feeds.length : 0;
                document.getElementById('importCount').textContent = importCount;
            })
            .catch(error => {
                console.error('Error loading import count:', error);
                document.getElementById('importCount').textContent = '0';
            });

            // Update export count
            fetch('{{ route("user-ical-feeds.index") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Export count data:', data); // Debug log
                const feeds = data.feeds || data.data || data || [];
                const exportCount = Array.isArray(feeds) ? feeds.length : 0;
                document.getElementById('exportCount').textContent = exportCount;
            })
            .catch(error => {
                console.error('Error loading export count:', error);
                document.getElementById('exportCount').textContent = '0';
            });
        }

        // Initialize integration counts on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateIntegrationCounts();
        });

        // Sync individual feed
        function syncFeed(feedId) {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch(`/ical-feeds/${feedId}/sync`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    loadImportFeedsTable(); // Refresh the table
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to sync feed. Please try again.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        // Delete feed
        function deleteFeed(feedId) {
            if (!confirm('Are you sure you want to delete this feed?')) {
                return;
            }
            
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch(`/ical-feeds/${feedId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    loadImportFeedsTable(); // Refresh the table
                    updateIntegrationCounts(); // Update counts
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to delete feed. Please try again.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        // Regenerate OTP for user feed
        function regenerateOTP(feedId) {
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch(`/user-ical-feeds/${feedId}/regenerate-otp`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    loadExportFeedsTable(); // Refresh the table
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to regenerate OTP. Please try again.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        // Delete user feed
        function deleteUserFeed(feedId) {
            if (!confirm('Are you sure you want to delete this feed?')) {
                return;
            }
            
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            fetch(`/user-ical-feeds/${feedId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    loadExportFeedsTable(); // Refresh the table
                    updateIntegrationCounts(); // Update counts
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to delete feed. Please try again.');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        // Show Unified iCal Management Modal
        function showUnifiedICalModal() {
            $('#unifiedICalModal').modal('show');
            loadImportFeedsTable();
            loadExportFeedsTable();
        }

        // Load Import Feeds Table
        function loadImportFeedsTable() {
            fetch('{{ route("ical-feeds.index") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Import feeds data:', data); // Debug log
                const tbody = document.getElementById('importFeedsTableBody');
                
                // Handle different response formats
                const feeds = data.feeds || data.data || data || [];
                
                if (feeds && feeds.length > 0) {
                    tbody.innerHTML = feeds.map(feed => {
                        // Format the last sync date
                        let lastSync = 'Never';
                        if (feed.last_sync || feed.last_sync_at) {
                            const syncDate = new Date(feed.last_sync || feed.last_sync_at);
                            if (!isNaN(syncDate.getTime())) {
                                lastSync = syncDate.toLocaleDateString() + ' ' + syncDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            }
                        }
                        
                        return `
                            <tr>
                                <td><strong>${feed.name || feed.feed_name || 'Unnamed Feed'}</strong></td>
                                <td><code class="small">${feed.url || feed.feed_url || 'No URL'}</code></td>
                                <td>${lastSync}</td>
                                <td><span class="badge bg-info">${feed.events_count || 0}</span></td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" onclick="syncFeed(${feed.id})" title="Sync">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteFeed(${feed.id})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                                <p>No import feeds configured yet</p>
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading import feeds:', error);
                document.getElementById('importFeedsTableBody').innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger py-4">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <p>Failed to load feeds: ${error.message}</p>
                        </td>
                    </tr>
                `;
            });
        }

        // Load Export Feeds Table
        function loadExportFeedsTable() {
            fetch('{{ route("user-ical-feeds.index") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Export feeds data:', data); // Debug log
                const tbody = document.getElementById('exportFeedsTableBody');
                
                // Handle different response formats
                const feeds = data.feeds || data.data || data || [];
                
                if (feeds && feeds.length > 0) {
                    tbody.innerHTML = feeds.map(feed => {
                        // Format the expires date
                        let expiresAt = 'Never';
                        if (feed.expires_at) {
                            const expireDate = new Date(feed.expires_at);
                            if (!isNaN(expireDate.getTime())) {
                                expiresAt = expireDate.toLocaleDateString() + ' ' + expireDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            }
                        }
                        
                        return `
                            <tr>
                                <td><strong>${feed.name || feed.feed_name || 'Unnamed Feed'}</strong></td>
                                <td><span class="badge bg-secondary">${feed.type === 'bookings_only' ? 'Bookings Only' : 'All Events'}</span></td>
                                <td><code class="small">${feed.public_url || feed.feed_url || 'No URL'}</code></td>
                                <td><code class="small">${feed.current_otp || 'No OTP'}</code></td>
                                <td>${expiresAt}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-warning" onclick="regenerateOTP(${feed.id})" title="New OTP">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteUserFeed(${feed.id})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                                <p>No export feeds generated yet</p>
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading export feeds:', error);
                document.getElementById('exportFeedsTableBody').innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-danger py-4">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <p>Failed to load feeds: ${error.message}</p>
                        </td>
                    </tr>
                `;
            });
        }
    </script>
@endsection



