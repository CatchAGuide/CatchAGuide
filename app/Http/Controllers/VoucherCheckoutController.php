<?php

namespace App\Http\Controllers;

use App\Domain\Checkout\ViewModels\VoucherCheckoutViewModel;
use App\Http\Requests\VoucherCheckoutRequest;
use App\Models\VoucherOrder;
use App\Presenters\Offers\TourCardPresenter;
use App\Presenters\Voucher\VoucherOrderPresenter;
use App\Services\Voucher\VoucherConfirmationAccess;
use App\Services\Voucher\VoucherOrderService;
use App\Services\Voucher\VoucherTourFinder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Gift voucher checkout: a tour bought without a date. The order goes to the guide, who
 * collects payment and sends the voucher; CaG processes no payment.
 */
class VoucherCheckoutController extends Controller
{
    public function __construct(
        private readonly VoucherTourFinder $tours,
    ) {}

    /**
     * "Buy as gift voucher" on the tour page posts the booking widget's form here; carry the
     * guest count over to the voucher checkout's own URL.
     */
    public function start(Request $request): RedirectResponse
    {
        $guiding = $this->tours->findById((int) $request->input('guiding_id'));

        abort_if($guiding === null, 404);

        return redirect()->route('checkout.voucher.show', [
            'slug' => $guiding->slug,
            'persons' => max(1, (int) $request->input('person', 1)),
        ]);
    }

    public function show(Request $request, string $slug, TourCardPresenter $presenter)
    {
        $guiding = $this->tours->findBySlug($slug);

        abort_if($guiding === null, 404);

        return view('pages.voucher-checkout.index', [
            'checkout' => new VoucherCheckoutViewModel(
                $guiding,
                $presenter,
                auth()->user(),
                (int) $request->query('persons', 1),
            ),
        ]);
    }

    public function store(
        VoucherCheckoutRequest $request,
        VoucherOrderService $orders,
        VoucherConfirmationAccess $confirmations,
    ): JsonResponse {
        $guiding = $request->guiding();

        try {
            $order = $orders->submit(
                $guiding,
                $request->quote(),
                $request->voucher(),
                $request->contact(),
                $request->user(),
                app()->getLocale(),
            );
        } catch (Throwable $e) {
            Log::error('Voucher checkout submission failed', ['guiding_id' => $guiding->id, 'error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => __('checkout.voucher.order_failed')], 500);
        }

        $confirmations->grant($order);

        return response()->json([
            'success' => true,
            'redirect_url' => route('checkout.voucher.thank-you', ['slug' => $guiding->slug, 'orderId' => $order->id]),
        ]);
    }

    /**
     * Only the session that placed the order or its signed-in buyer may see it; everyone else
     * gets the same 404 as for a missing id.
     */
    public function thankYou(string $slug, int $orderId, VoucherConfirmationAccess $confirmations, VoucherOrderPresenter $presenter)
    {
        $order = VoucherOrder::with(['guiding', 'guide.information'])->find($orderId);

        abort_unless($order && $order->guiding?->slug === $slug && $confirmations->allows($order, auth()->user()), 404);

        return view('pages.voucher-checkout.thank-you', [
            'order' => $order,
            'voucher' => $presenter->details($order),
        ]);
    }
}
