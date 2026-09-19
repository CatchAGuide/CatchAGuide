<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        @include('pages.guidings.partials.booking-widget', ['instance' => 'desktop'])

        @if($guiding->min_guests)
            <p class="guidings-book-card__note">* {{ str_replace('[Min Guest]', $guiding->min_guests, __('booking.min_guest')) }}</p>
        @endif
    </div>

    @if(!$agent->ismobile())
    <div class="contact-card mb-4 mt-4 tour-details-two__book-tours">
        <h5 class="contact-card__title">{{ __('booking.contact_us') }}</h5>
        <div class="contact-card__content">
            <p class="">{{ __('booking.do_you_have_questions') }}</p>
            <div class="">
                <div class="contact-info">
                    <i class="fas fa-phone-alt me-2"></i>
                    <a href="tel:+49{{config('cag.contact_num')}}" class="text-decoration-none">+49 (0) {{config('cag.contact_num')}}</a>
                </div>
                <a href="#" id="contact-product" class="btn btn-outline-orange" data-bs-toggle="modal" data-bs-target="#contactModal">
                    {{ __('booking.contact_form') }}
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
                @include('partials.product-report.cta', [
                    'reportSourceType' => 'guiding',
                    'reportSourceId' => $guiding->id,
                    'reportedUrl' => url()->current(),
                ])
            </div>
        </div>
    </div>
    @endif
</div>
