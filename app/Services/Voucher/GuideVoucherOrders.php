<?php

namespace App\Services\Voucher;

use App\Models\User;
use App\Models\VoucherOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * A guide's gift voucher orders for Profile › Bookings, newest first, and the voucher PDF they
 * send to the buyer (re-created when it's missing, e.g. after a failed first attempt).
 */
class GuideVoucherOrders
{
    private const PER_PAGE = 10;

    public const PAGE_NAME = 'voucher_orders';

    public function __construct(
        private readonly VoucherOrderService $orders,
        private readonly VoucherPdfGenerator $pdfs,
    ) {}

    public function forGuide(User $guide): LengthAwarePaginator
    {
        return VoucherOrder::where('guide_id', $guide->id)
            ->latest()
            ->paginate(self::PER_PAGE, ['id', 'voucher_code', 'tour_title_snapshot', 'number_of_guests', 'price_total',
                'recipient_names', 'buyer_first_name', 'buyer_last_name', 'buyer_email', 'buyer_phone_country_code',
                'buyer_phone', 'design_type', 'valid_until', 'created_at'], self::PAGE_NAME)
            ->withQueryString();
    }

    /**
     * The stored voucher PDF, or null when the order uses the guide's own design or the PDF
     * can't be created.
     */
    public function pdf(VoucherOrder $order): ?string
    {
        if (! $order->design_type?->generatesPdf()) {
            return null;
        }

        $order->loadMissing(['guiding', 'guide.information']);

        if (($pdf = $this->pdfs->contents($order)) === null) {
            $this->orders->attachPdf($order);
            $pdf = $this->pdfs->contents($order);
        }

        return $pdf;
    }
}
