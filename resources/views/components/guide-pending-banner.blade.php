@php $guideUser = web_guide_user(); @endphp
@if($guideUser && $guideUser->isPendingGuide())
    <div class="alert alert-info border-0 mb-4 guide-pending-banner" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-hourglass-half me-3 mt-1"></i>
            <div>
                <strong>{{ __('profile.guide_pending_banner_title') }}</strong><br>
                {{ __('profile.guide_pending_banner_body') }}
            </div>
        </div>
    </div>
@endif
