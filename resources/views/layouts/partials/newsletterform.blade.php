{{-- Newsletter signup (footer) --}}
<div class="footer-widget__column footer-widget__newsletter">
    <h3 class="footer-widget__title">Newsletter</h3>
    <form id="newsletter-form" class="cag-footer__newsletter-form" action="{{ route('sendnewsletter') }}" method="POST">
        @csrf
        @method('post')
        <div class="footer-widget__newsletter-input-box">
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
            <input
                type="email"
                placeholder="@lang('message.email-address')"
                name="email"
                id="email"
                autocomplete="email"
                required
            >
            <div class="form-check cag-footer__newsletter-agree">
                <input class="form-check-input" type="checkbox" value="1" id="defaultCheck1" required>
                <label class="form-check-label" for="defaultCheck1">
                    @lang('message.agree')
                </label>
            </div>
            <x-recaptcha />
            <button type="submit" class="footer-widget__newsletter-btn">
                @lang('message.subscribe')
            </button>
        </div>
    </form>
</div>
