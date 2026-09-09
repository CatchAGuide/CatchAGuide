<div class="form-actions guide-wizard-nav">
    <div class="d-flex justify-content-between flex-wrap gap-3">
        @if($showBack ?? true)
            <button type="button" class="btn btn-outline-secondary btn-lg wizard-prev">
                <i class="fas fa-arrow-left me-2"></i>{{ __('accommodations.previous') }}
            </button>
        @else
            <span></span>
        @endif
        <button type="button" class="btn btn-primary btn-lg wizard-next">
            {{ __('accommodations.next') }}<i class="fas fa-arrow-right ms-2"></i>
        </button>
    </div>
</div>
