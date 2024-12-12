{{-- {!! ReCaptcha::htmlScriptTagJsApi() !!} --}}
<div class="footer-widget__column footer-widget__newsletter">
    <h3 class="footer-widget__title mb-md-4 mb-2">Newsletter</h3>
    <form id="newsletter-form" action="{{route('sendnewsletter')}}" method="POST">
        @csrf
        @method('post')
        <div class="footer-widget__newsletter-input-box">
            @error('email')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
            <input type="email" placeholder="@lang('message.email-address')" name="email" id="email" style="text-transform: none" required>
            <div class="form-check my-3">
                <input class="form-check-input" type="checkbox" value="" id="defaultCheck1"
                       required>
                <label class="form-check-label text-white" style="font-size: 14px" for="defaultCheck1">
                    @lang('message.agree')
                </label>
            </div>
            {!! htmlFormSnippet() !!}
            <button type="submit"
                    class="footer-widget__newsletter-btn">@lang('message.subscribe')
            </button>
        </div>
    </form>
</div>
