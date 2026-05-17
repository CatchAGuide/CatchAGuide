{{-- Admin in-place booking reschedule modal --}}
<div class="modal fade" id="rescheduleBookingModal" tabindex="-1" aria-labelledby="rescheduleBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="rescheduleBookingModalLabel">Reschedule booking</h5>
                    <div class="text-muted small">Booking <span id="reschedule-header-id">#—</span></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">
                    Updates this booking in place: releases the old calendar slot, blocks the new date, and sets status to
                    <strong>pending</strong>. Emails are optional below.
                </p>
                <div id="reschedule-loading" class="text-center py-4 d-none">
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                    <div class="small text-muted mt-2">Loading booking details…</div>
                </div>
                <div id="reschedule-form-wrap" class="d-none">
                    <div class="reschedule-summary card border-0 shadow-sm mb-4">
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="reschedule-summary__label">Customer</div>
                                    <div class="reschedule-summary__value" id="reschedule-guest-name">—</div>
                                    <div class="reschedule-summary__meta" id="reschedule-guest-email">—</div>
                                    <div class="reschedule-summary__meta" id="reschedule-guest-phone"></div>
                                    <span class="badge bg-light text-dark border mt-1" id="reschedule-guest-type">—</span>
                                </div>
                                <div class="col-md-6">
                                    <div class="reschedule-summary__label">Guide &amp; tour</div>
                                    <div class="reschedule-summary__value" id="reschedule-guide-name">—</div>
                                    <div class="reschedule-summary__meta" id="reschedule-guiding-title">—</div>
                                    <div class="reschedule-summary__meta" id="reschedule-guiding-location"></div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="reschedule-summary__label">Status</div>
                                    <div><span class="badge" id="reschedule-status-badge">—</span></div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="reschedule-summary__label">Guests / total</div>
                                    <div class="reschedule-summary__value" id="reschedule-guests-price">—</div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="reschedule-summary__label">Requested</div>
                                    <div class="reschedule-summary__value" id="reschedule-requested-at">—</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Current tour date</label>
                            <div id="reschedule-current-date" class="fw-semibold fs-6">—</div>
                        </div>
                        <div class="col-md-6">
                            <label for="reschedule-selected-date" class="form-label">New tour date</label>
                            <input type="date" class="form-control" id="reschedule-selected-date" min="{{ now()->toDateString() }}">
                            <div id="reschedule-date-error" class="text-danger small mt-1 d-none"></div>
                        </div>
                    </div>

                    <div class="mb-3 mt-2" id="reschedule-alternative-dates-wrap" style="display:none;">
                        <label class="form-label text-muted small mb-1">Suggested dates from rejection</label>
                        <div id="reschedule-alternative-dates" class="d-flex flex-wrap gap-2"></div>
                    </div>

                    <div class="border rounded p-3 bg-light">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" id="reschedule-send-emails" checked>
                            <label class="form-check-label fw-semibold" for="reschedule-send-emails">
                                Send new booking request emails to guest and guide
                            </label>
                        </div>
                        <p class="text-muted small mb-0" id="reschedule-email-hint">
                            When checked, guest, guide, and CEO booking-request emails are sent after rescheduling.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="reschedule-submit-btn" onclick="submitRescheduleBooking()" disabled>
                    Reschedule &amp; notify
                </button>
            </div>
        </div>
    </div>
</div>
