@php $guideUser = web_guide_user(); @endphp
@if($guideUser && $guideUser->isRejectedGuide())
    @php $rejectedRequest = $guideUser->latestRejectedGuideRequest(); @endphp
    <div class="alert alert-warning border-0 mb-4 guide-rejected-banner" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-times-circle me-3 mt-1"></i>
            <div>
                <strong>{{ __('profile.guide_rejected_banner_title') }}</strong><br>
                {{ __('profile.guide_rejected_banner_body') }}
                @if($rejectedRequest?->rejection_reason)
                    <span class="d-block mt-2"><strong>{{ __('profile.guide_rejected_reason') }}</strong> {{ $rejectedRequest->rejection_reason }}</span>
                @endif
                <a href="{{ route('guide.onboarding') }}" class="alert-link d-inline-block mt-2">{{ __('profile.guide_rejected_reapply_link') }}</a>
            </div>
        </div>
    </div>
@endif
