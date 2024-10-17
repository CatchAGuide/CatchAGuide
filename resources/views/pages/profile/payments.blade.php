@extends('pages.profile.layouts.profile')
@section('title', 'Zahlungsdetails')
@section('profile-content')


    <div class="container" style=" margin-bottom: 20px;">
        <div class="row mt-3">
            <div class="col-md-12">
                @if(auth()->user()->hasDefaultPaymentMethod())
                    <div class="row">
                        <div class="col-12">
                            <h5>Dein aktuelles Guthaben: {{ two(auth()->user()->balance) }} €</h5>
                            <form action="{{ route('payments.deposit') }}" method="POST">
                                @csrf
                                <div class="row mt-3">
                                    <div class="col-10">
                                        <input type="number" class="form-control mt-2" placeholder="Einzahlungsbetrag" name="amount" min="25" max="1000">
                                    </div>
                                    <div class="col-2">
                                        <button type="submit" class="thm-btn">Einzahlen</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <hr>
                @endif
                <div class="row">
                    <div class="col-12">
                        <table class="table">
                            <thead>
                            <tr>
                                <td>Transaktions-ID</td>
                                <td>Typ</td>
                                <td>Anzahl</td>
                                <td>Datum</td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach(auth()->user()->transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->uuid }}</td>
                                    <td>
                                        @if($transaction->type === 'deposit')
                                            <span class="badge bg-secondary">Einzahlung</span>
                                        @else
                                            <span class="badge bg-secondary">Auszahlung/Bezahlung</span>
                                        @endif
                                    </td>
                                    <td>{{ two($transaction->amount) }} €</td>
                                    <td>{{ $transaction->created_at->format('H:i d.m.Y')  }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12">
                        <h5>Kreditkarten Details:</h5>
                        <form action="{{ route('payments.add-or-update-payment-method') }}" method="POST" id="addOrUpdatePaymentForm" class="mt-3">
                            @csrf
                            <input type="hidden" name="payment_method" id="paymentMethodHidden">
                        </form>

                        <input id="card-holder-name" class="form-control mb-3" type="text" placeholder="Karteninhaber" value="{{ auth()->user()->full_name }}">

                        <!-- Stripe Elements Placeholder -->
                        <div id="card-element" class="form-control"></div>

                        <button type="button" id="card-button" data-secret="{{ $intent->client_secret }}" class="thm-btn mt-3">
                            Zahlungsart aktualisieren
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_after')
    <script src="https://js.stripe.com/v3/"></script>

    <script>
        const stripe = Stripe('{{ env('STRIPE_KEY') }}');

        const elements = stripe.elements();
        const cardElement = elements.create('card');

        cardElement.mount('#card-element');

        const cardHolderName = document.getElementById('card-holder-name');
        const cardButton = document.getElementById('card-button');
        const clientSecret = cardButton.dataset.secret;

        cardButton.addEventListener('click', async (e) => {
            const { setupIntent, error } = await stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: { name: cardHolderName.value }
                    }
                }
            );

            if (error) {
                // Display "error.message" to the user...
            } else {
                $('#paymentMethodHidden').val(setupIntent.payment_method);

                $('#addOrUpdatePaymentForm').submit();
            }
        });
    </script>
@endsection
