<?php

namespace App\Http\Controllers;

use App\Enums\VoucherDesign;
use App\Http\Requests\Guide\UpdateVoucherSettingsRequest;
use App\Mail\Guest\VoucherOrderGuestMail;
use App\Services\Guide\GuideVoucherSettingsService;
use App\Services\Voucher\VoucherPdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * Profile › Gift vouchers: the guide switches voucher sales per tour and picks the voucher design.
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
        ]);
    }

    /**
     * Sample of the standard voucher with the guide's own name and contact details.
     */
    public function sample(VoucherPdfGenerator $pdfs): Response
    {
        $guide = auth()->user();
        abort_unless($guide->canViewGuideTools(), 403);

        $order = VoucherOrderGuestMail::sample()->order;
        $order->setRelation('guide', $guide->loadMissing('information'));
        $order->language = app()->getLocale();

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
