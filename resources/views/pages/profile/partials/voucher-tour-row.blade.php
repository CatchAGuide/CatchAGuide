{{-- One tour on Profile › Gift vouchers. Expects: $tour (Guiding with display_title), $published (bool),
     $design (the guide's saved VoucherDesign: the PDF preview is only for the CaG voucher). --}}
<li class="gv-tour">
    <label class="gv-tour__main" for="voucher_enabled_{{ $tour->id }}">
        <span class="gv-tour__title">{{ $tour->display_title }}</span>
        @unless ($published)
            <span class="gv-tour__badge">{{ __('profile.vouchers.tour_inactive') }}</span>
        @endunless
    </label>
    @if ($design->generatesPdf())
        <a class="gv-tour__preview" href="{{ route('profile.vouchers.preview', $tour->id) }}" target="_blank" rel="noopener"
            aria-label="{{ __('profile.vouchers.preview_pdf') }}: {{ $tour->display_title }}">
            <i class="fas fa-file-pdf"></i>{{ __('profile.vouchers.preview_pdf') }}
        </a>
    @endif
    @if ($published)
        <a class="gv-tour__link" href="{{ route('guidings.show', $tour->slug) }}" target="_blank" rel="noopener"
            aria-label="{{ __('profile.vouchers.view_tour') }}: {{ $tour->display_title }}" title="{{ __('profile.vouchers.view_tour') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>
    @endif
    <span class="gv-switch">
        <input class="gv-switch__input" type="checkbox" role="switch" name="voucher_enabled[]" id="voucher_enabled_{{ $tour->id }}"
            value="{{ $tour->id }}" @checked($tour->acceptsVouchers())>
        <span class="gv-switch__track" aria-hidden="true"></span>
    </span>
</li>
