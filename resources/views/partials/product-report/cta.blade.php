@php
    $reportSourceType = $reportSourceType ?? null;
    $reportSourceId = $reportSourceId ?? null;
    $reportedUrl = $reportedUrl ?? url()->current();
    $ctaClass = $ctaClass ?? 'product-report-cta text-muted small d-inline-block mt-2';
@endphp
<a
    href="#"
    class="{{ $ctaClass }}"
    data-bs-toggle="modal"
    data-bs-target="#productReportModal"
    data-source-type="{{ $reportSourceType }}"
    data-source-id="{{ $reportSourceId }}"
    data-reported-url="{{ $reportedUrl }}"
>
    <i class="fas fa-flag me-1"></i>{{ __('notice-takedown.report_listing') }}
</a>
