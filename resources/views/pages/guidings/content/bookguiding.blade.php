@if(!$agent->ismobile())
    <div class="contact-card mb-4 tour-details-two__book-tours">
        <h5 class="contact-card__title">{{ translate('Contact Card') }}</h5>
        <div class="contact-card__content">
            <p class="text-muted">{{ translate('Do you have questions about this vacation? Our team is here to help!') }}</p>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="contact-info">
                    <i class="fas fa-phone-alt me-2"></i>
                    <a href="tel:+49{{env('CONTACT_NUM')}}" class="text-decoration-none">+49 (0) {{env('CONTACT_NUM')}}</a>
                </div>
                <a href="{{ route('additional.contact') }}" class="btn btn-outline-orange">
                    {{ translate('Contact Form') }}
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
@endif

<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours">
            <h3 class="tour-details-two__sidebar-title d-none d-md-block">@lang('message.bookaguide')</h3>
                <div class="card-body">
                    <form action="{{ route('checkout') }}" method="POST" class="checkout-form">
                        @csrf
                        <div class="booking-form-container">
                            <div class="booking-select mb-md-3">
                                <select class="form-select" aria-label="Personenanzahl" name="person" required>
                                    <option selected disabled>{{ translate('Please select number of people') }}</option>
                                    @if($guiding->price_type == 'per_person')
                                        @foreach(json_decode($guiding->prices) as $price)
                                            <option value="{{ $price->person }}">{{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}</option>
                                        @endforeach
                                    @else
                                        @for($i = 1; $i <= $guiding->max_guests; $i++)
                                            <option value="{{ $i }}">{{ $i }} Person {{ $i == 1 ? 'Person' : 'Personen' }}</option>
                                        @endfor
                                    @endif
                                </select>
                            </div>
                            <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                            <button type="submit" class="btn btn-orange w-100">@lang('message.Booking')</button>
                        </div>
                    </form>
                    <div class="mt-3">
                        <h5>{{ translate('Price') }}</h5>
                        <ul class="list-unstyled">
                        @if($guiding->price_type == 'per_person')
                            @foreach(json_decode($guiding->prices) as $price)
                                <li class="d-flex justify-content-between align-items-center">
                                    <span class="">{{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}</span>
                                    <span class="text-right">
                                        @if($price->person > 1)
                                            <span class="text-orange">{{ $price->person > 1 ? round($price->amount / $price->person) : $price->amount }}€</span>
                                            <span class="text-black" style="font-size: 0.8em;"> p.P</span>
                                        @else
                                            <span class="text-orange me-3 pe-1">{{ $price->person > 1 ? round($price->amount / $price->person) : $price->amount }}€</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        @else
                            @for($i = 1; $i <= $guiding->max_guests; $i++)
                                <li class="d-flex justify-content-between align-items-center">
                                    <span>{{ $i }} {{ $i == 1 ? 'Person' : 'Personen' }}</span>
                                    <span class="text-right">
                                        @if($i > 1)
                                            <span class="text-orange me-3 pe-1">{{ $i > 1 ? round($guiding->price / $i) : $guiding->price }}€</span>
                                            <span class="text-black" style="font-size: 0.8em;"> p.P</span>
                                        @else
                                            <span class="text-danger">{{ round($guiding->price / $i) }}€</span>
                                        @endif
                                    </span>
                                </li>
                            @endfor
                        @endif
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalLabel">{{ translate('Checkout Options') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-orange w-100" id="guestCheckout">
                        {{ translate('Continue as Guest') }}
                    </button>
                    <a href="{{ route('login') }}" class="btn btn-outline-orange w-100">
                        {{ translate('Login to Continue') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkoutForms = document.querySelectorAll('.checkout-form');
    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    
    checkoutForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!isLoggedIn) {
                e.preventDefault();
                checkoutModal.show();
            }
        });
    });

    // Handle guest checkout
    document.getElementById('guestCheckout').addEventListener('click', function() {
        const activeForm = document.querySelector('.checkout-form');
        activeForm.submit();
    });
});
</script>
