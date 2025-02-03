<div class="col-md-12 tour-details-two__sticky sticky-lg-top {{$agent->ismobile() ? 'text-center' : ''}}">
    <div class="tour-details-two__sidebar">
        <div class="tour-details-two__book-tours">
            <div class="card-body">
            <form action="{{ route('checkout') }}" method="POST" class="checkout-form">
                @csrf
                <div class="booking-form-container">
                    <div class="booking-select position-relative">
                        <div style="display: flex;">
                            <select class="form-select" id="personSelect" aria-label="Personenanzahl" name="person" required>
                                <option selected disabled>Bitte wählen</option>
                                @foreach(json_decode($guiding->prices) as $price)
                                    <option value="{{ $price->person }}" data-price="{{ round($price->amount / $price->person) }}">
                                        {{ $price->person }} {{ $price->person == 1 ? 'Person' : 'Personen' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="clearSelect" class="btn btn-link text-danger" style="display: none;">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        <div class="booking-price">
                            <span id="priceLabel" data-from-text="@lang('message.From')" data-price-text="{{ translate('Price:') }}">
                            @lang('message.From')
                            </span>
                            <span id="priceDisplay" class="text-orange">{{ $guiding->getLowestPrice() }}€ p.P.</span>
                        </div>
                    </div>
                    <div class="booking-price-container">
                        <button type="submit" class="btn btn-orange w-100">{{ translate('Book now') }}</button>
                    </div>
                    <input type="hidden" name="guiding_id" value="{{ $guiding->id }}">
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModalMobile" tabindex="-1" aria-labelledby="checkoutModalMobileLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkoutModalMobileLabel">{{ translate('Checkout Options') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-orange w-100" id="guestCheckoutMobile">
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
  document.addEventListener('DOMContentLoaded', function () {
    const personSelect = document.getElementById('personSelect');
    const priceLabel = document.getElementById('priceLabel');
    const priceDisplay = document.getElementById('priceDisplay');
    const clearSelect = document.getElementById('clearSelect');

    const fromText = priceLabel.getAttribute('data-from-text');
    const priceText = priceLabel.getAttribute('data-price-text');

    // Update price and label when a new option is selected
    personSelect.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const personCount = selectedOption.value;
        const price = selectedOption.getAttribute('data-price');

        if (price) {
            priceLabel.textContent = priceText;
            if (personCount === '1') {
                priceDisplay.textContent = `${price}€`;
            } else {
                priceDisplay.textContent = `${price}€ p.P.`;
            }
            clearSelect.style.display = "block"; // Show the clear button
        }
    });

    // Clear selection and reset to default
    clearSelect.addEventListener('click', function () {
        personSelect.selectedIndex = 0;
        priceLabel.textContent = fromText;
        priceDisplay.textContent = "{{ $guiding->getLowestPrice() }}€ p.P.";
        clearSelect.style.display = "none"; // Hide the clear button
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const checkoutForms = document.querySelectorAll('.checkout-form');
    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    const checkoutModalMobile = new bootstrap.Modal(document.getElementById('checkoutModalMobile'));
    
    checkoutForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!isLoggedIn) {
                e.preventDefault();
                checkoutModalMobile.show();
            }
        });
    });

    // Handle guest checkout
    document.getElementById('guestCheckoutMobile').addEventListener('click', function() {
        const activeForm = document.querySelector('.checkout-form');
        activeForm.submit();
    });
});

</script>
