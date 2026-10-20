<?php

namespace App\Http\Controllers;

use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use App\Services\Voucher\VoucherPaymentService;
use Illuminate\Http\RedirectResponse;

/**
 * The "Confirm payment received" link in the guide's voucher order email (no login; the
 * 32-character payment token is the secret). GET only shows the order: mail scanners open links,
 * so the confirmation itself is a POST from the button on that page.
 */
class VoucherPaymentController extends Controller
{
    public function __construct(
        private readonly VoucherPaymentService $payments,
        private readonly VoucherOrderPresenter $presenter,
    ) {}

    public function show(string $token)
    {
        $order = $this->order($token);

        return response()
            ->view('pages.voucher-payment.show', [
                'order' => $order,
                'voucher' => $this->presenter->details($order),
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function confirm(string $token): RedirectResponse
    {
        $confirmed = $this->payments->confirm($this->order($token));

        return redirect()
            ->route('voucher-payment.show', ['token' => $token])
            ->with('voucher_payment', $confirmed ? 'confirmed' : 'already');
    }

    private function order(string $token): VoucherOrder
    {
        return $this->payments->findByToken($token) ?? abort(404);
    }
}
