<?php

namespace App\Http\Controllers;

use App\Domain\Voucher\ViewModels\VoucherLandingViewModel;
use App\Http\Requests\VoucherLandingRequest;
use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use App\Services\Analytics\ConversionDataLayer;
use App\Services\Voucher\VoucherConfirmationAccess;
use App\Services\Voucher\VoucherLandingCatalog;
use App\Services\Voucher\VoucherRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Gift voucher landing page (/guidings/voucher): a request form (destination, amount, contact)
 * stored as a voucher_orders request. CaG follows up; no tour, guide or payment is involved here.
 */
class VoucherLandingController extends Controller
{
    public function show(VoucherLandingCatalog $catalog, VoucherOrderPresenter $presenter): View
    {
        return view('pages.voucher-landing.index', [
            'landing' => new VoucherLandingViewModel($catalog, $presenter, auth('web')->user()),
        ]);
    }

    public function store(VoucherLandingRequest $request, VoucherRequestService $requests, VoucherConfirmationAccess $confirmations): RedirectResponse
    {
        try {
            $order = $requests->submit($request->place(), $request->amount(), $request->contact(), $request->user('web'), app()->getLocale());
        } catch (Throwable $e) {
            Log::error('Voucher landing request failed', ['error' => $e->getMessage()]);

            return redirect()->to(route('guidings.voucher').'#gutschein')
                ->withInput($request->except('g-recaptcha-response'))
                ->with('voucher_request_failed', true);
        }

        $confirmations->grant($order);

        return redirect()->to(route('guidings.voucher.thank-you', ['orderId' => $order->id]).'#gutschein');
    }

    /**
     * The landing page with the request summary in place of the form. Only the session that sent
     * the request (or its signed-in buyer) may open it; everyone else gets a 404.
     */
    public function thankYou(
        int $orderId,
        VoucherConfirmationAccess $confirmations,
        VoucherLandingCatalog $catalog,
        VoucherOrderPresenter $presenter,
        ConversionDataLayer $conversions,
    ): View {
        $order = VoucherOrder::query()->requests()->find($orderId);

        abort_unless($order && $confirmations->allows($order, auth('web')->user()), 404);

        return view('pages.voucher-landing.index', [
            'landing' => new VoucherLandingViewModel($catalog, $presenter, auth('web')->user(), $order),
            'conversion' => $conversions->forVoucherRequest($order),
        ]);
    }
}
