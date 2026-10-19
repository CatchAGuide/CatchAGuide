{{-- One tour on Profile › Gift vouchers. Expects: $tour (Guiding with display_title), $published (bool). --}}
<li class="gv-tour">
    <label class="gv-tour__main" for="voucher_enabled_{{ $tour->id }}">
        <span class="gv-tour__title">{{ $tour->display_title }}</span>
        @unless ($published)
            <span class="gv-tour__badge">{{ __('profile.vouchers.tour_inactive') }}</span>
        @endunless
    </label>
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
