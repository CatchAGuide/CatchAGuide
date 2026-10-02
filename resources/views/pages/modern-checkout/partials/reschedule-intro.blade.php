{{-- Why the visitor is here: the guide declined the original date. Guide text is escaped. --}}
<div class="tc-notice">
    <p class="tc-notice__text">
        {{ $guideName ? __('checkout.reschedule.intro', ['guide' => $guideName]) : __('checkout.reschedule.intro_no_guide') }}
    </p>
    @if ($originalDate)
        <p class="tc-notice__meta">{{ __('checkout.reschedule.original_date', ['date' => $originalDate]) }}</p>
    @endif
    @if (filled($guideMessage))
        <figure class="tc-notice__quote">
            <figcaption>{{ __('checkout.reschedule.guide_message') }}</figcaption>
            <blockquote>{{ Str::limit($guideMessage, 600) }}</blockquote>
        </figure>
    @endif
</div>
