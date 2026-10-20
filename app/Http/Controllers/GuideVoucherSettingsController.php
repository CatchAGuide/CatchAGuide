<?php

namespace App\Http\Controllers;

use App\Enums\VoucherDesign;
use App\Http\Requests\Guide\UpdateVoucherSettingsRequest;
use App\Models\Guiding;
use App\Models\VoucherOrder;
use App\Services\Guide\GuideVoucherSettingsService;
use App\Services\Voucher\VoucherPaymentService;
use App\Services\Voucher\VoucherPdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Profile › Gift vouchers: the guide switches voucher sales per tour, picks the voucher design,
 * previews the voucher per tour and confirms the payments of their voucher orders.
 */
class GuideVoucherSettingsController extends Controller
{
    public function __construct(
        private readonly GuideVoucherSettingsService $settings,
    ) {}

    public function edit()
    {
        $guide = auth()->user();

        if (! $guide->canViewGuideTools()) {
            return redirect()->route('profile.becomeguide');
        }

        return view('pages.profile.vouchers', [
            'tours' => $this->settings->tours($guide),
            'design' => $guide->voucherDesign(),
            'designs' => VoucherDesign::cases(),
            'orders' => $this->settings->orders($guide),
        ]);
    }

    /**
     * Sample of the standard voucher with the guide's own name and contact details.
     */
    public function sample(VoucherPdfGenerator $pdfs): Response
    {
        $guide = auth()->user();
        abort_unless($guide->canViewGuideTools(), 403);

        return $this->pdf($pdfs, $this->settings->sampleOrder($guide));
    }

    /**
     * The standard voucher as buyers of this tour would get it (sample recipient and message).
     */
    public function preview(Guiding $guiding, VoucherPdfGenerator $pdfs): Response
    {
        $guide = auth()->user();
        abort_unless($guide->canViewGuideTools() && (int) $guiding->user_id === (int) $guide->id, 404);

        return $this->pdf($pdfs, $this->settings->sampleOrder($guide, $guiding));
    }

    public function confirmPayment(VoucherOrder $voucherOrder, VoucherPaymentService $payments): RedirectResponse
    {
        abort_unless((int) $voucherOrder->guide_id === (int) auth()->id(), 404);

        $confirmed = $payments->confirm($voucherOrder);

        return redirect()->route('profile.vouchers')->with('message', __(
            'profile.vouchers.'.($confirmed ? 'payment_confirmed' : 'payment_already'),
            ['code' => $voucherOrder->voucher_code],
        ));
    }

    private function pdf(VoucherPdfGenerator $pdfs, VoucherOrder $order): Response
    {
        return response($pdfs->render($order), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$pdfs->fileName($order).'"',
        ]);
    }

    public function update(UpdateVoucherSettingsRequest $request): RedirectResponse
    {
        $this->settings->update($request->user(), $request->design(), $request->enabledGuidingIds());

        return redirect()->route('profile.vouchers')->with('message', __('profile.vouchers.saved'));
    }
}
