{{-- Profile › Bookings: the guide's gift voucher orders. CaG's part ends with the order emails;
     the guide collects the payment and sends the voucher (the CaG PDF, downloadable here). --}}
@inject('money', 'App\Presenters\Vacation\CampQuotePresenter')
@php($dateFormat = app()->getLocale() === 'de' ? 'd.m.Y' : 'M j, Y')
<section class="gv-card gv-card--bookings" id="voucher-orders" aria-labelledby="gv-orders-title">
    <header class="gv-card__head gv-card__head--split">
        <div>
            <h2 class="gv-card__title" id="gv-orders-title"><i class="fas fa-gift"></i> {{ __('profile.vouchers.bookings_title') }}</h2>
            <p class="gv-card__text">{{ __('profile.vouchers.bookings_text') }}</p>
        </div>
    </header>
    <ul class="gv-orders">
        @foreach ($voucherOrders as $order)
            <li class="gv-order">
                <div class="gv-order__main">
                    <p class="gv-order__title">{{ $order->tour_title_snapshot }}</p>
                    <p class="gv-order__meta">
                        <span class="gv-order__code">{{ $order->voucher_code }}</span>
                        · {{ __('profile.vouchers.order_for') }}: {{ $order->recipient_names }}
                        · {{ __('profile.vouchers.order_guests') }}: {{ $order->number_of_guests }}
                        · {{ __('profile.vouchers.order_price') }}: {{ $money->money((float) $order->price_total) }}
                    </p>
                    <p class="gv-order__meta">
                        {{ __('profile.vouchers.order_buyer') }}: {{ $order->buyerName() }}
                        · <a href="mailto:{{ $order->buyer_email }}">{{ $order->buyer_email }}</a>
                        @if (trim((string) $order->buyer_phone) !== '')
                            · <a href="tel:{{ preg_replace('/[^0-9+]/', '', $order->buyerPhone()) }}">{{ $order->buyerPhone() }}</a>
                        @endif
                    </p>
                    <p class="gv-order__meta">
                        {{ __('profile.vouchers.order_date') }}: {{ $order->created_at->format($dateFormat) }}
                        @if ($order->valid_until)
                            · {{ __('profile.vouchers.order_valid_until') }}: {{ $order->valid_until->format($dateFormat) }}
                        @endif
                    </p>
                </div>
                <span class="gv-order__status">
                    {{ __('profile.vouchers.order_design_'.($order->design_type?->generatesPdf() ? 'pdf' : 'own')) }}
                </span>
                <div class="gv-order__actions">
                    @if ($order->design_type?->generatesPdf())
                        <a class="gv-order__btn" href="{{ route('profile.vouchers.orders.pdf', $order) }}">
                            <i class="fas fa-file-pdf"></i>{{ __('profile.vouchers.order_pdf') }}
                        </a>
                    @endif
                    <a class="gv-order__btn gv-order__btn--ghost"
                        href="mailto:{{ $order->buyer_email }}?subject={{ rawurlencode(__('emails.voucher_order_guide.cta_subject', ['code' => $order->voucher_code])) }}">
                        <i class="fas fa-envelope"></i>{{ __('profile.vouchers.order_email') }}
                    </a>
                </div>
            </li>
        @endforeach
    </ul>
    @if ($voucherOrders->hasPages())
        <div class="gv-orders__pages">{{ $voucherOrders->fragment('voucher-orders')->links() }}</div>
    @endif
</section>
