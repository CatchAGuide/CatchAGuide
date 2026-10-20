<?php

namespace App\Services\Voucher;

use App\Models\Guiding;
use App\Models\User;
use App\Models\VoucherOrder;
use App\Services\Checkout\TourCheckoutQuote;
use App\Services\HelperService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns a validated voucher checkout into a voucher order: snapshots tour, price, the guide's
 * voucher design and the commission (same rate as a booking of that tour), fills the standard
 * voucher PDF when the guide uses it, and emails the buyer and the guide.
 *
 * CaG takes no payment. The voucher only goes to the buyer once the guide has confirmed the
 * payment (VoucherPaymentService), so neither of these first emails carries the PDF.
 */
class VoucherOrderService
{
    public function __construct(
        private readonly VoucherCodeGenerator $codes,
        private readonly VoucherPdfGenerator $pdfs,
        private readonly HelperService $helper,
        private readonly VoucherMailer $mailer,
    ) {}

    /**
     * @param  array{recipient_names: string, sender_name: ?string, personal_message: ?string}  $voucher
     * @param  array{first_name: string, last_name: string, email: string, country_code: string, phone: string}  $contact
     */
    public function submit(
        Guiding $guiding,
        TourCheckoutQuote $quote,
        array $voucher,
        array $contact,
        ?User $currentUser,
        string $locale,
    ): VoucherOrder {
        $guide = $guiding->user;
        $price = $quote->total();
        $commission = round((float) $this->helper->calculateRates($quote->basePrice), 2);

        $order = VoucherOrder::create([
            'voucher_code' => $this->codes->generate(),
            'payment_token' => Str::random(32),
            'guiding_id' => $guiding->id,
            'guide_id' => $guiding->user_id,
            'user_id' => $currentUser?->getKey(),
            'tour_title_snapshot' => (string) $guiding->title,
            'number_of_guests' => $quote->persons,
            'price_total' => $price,
            'currency' => 'EUR',
            'recipient_names' => $voucher['recipient_names'],
            'sender_name' => $voucher['sender_name'] ?: null,
            'personal_message' => $voucher['personal_message'] ?: null,
            'buyer_first_name' => $contact['first_name'],
            'buyer_last_name' => $contact['last_name'],
            'buyer_email' => $contact['email'],
            'buyer_phone_country_code' => $contact['country_code'],
            'buyer_phone' => $contact['phone'],
            'design_type' => $guide?->voucherDesign(),
            'language' => $locale,
            'valid_until' => VoucherValidity::until(now()),
            'commission_rate' => $quote->basePrice > 0 ? round($commission / $quote->basePrice, 4) : 0,
            'commission_amount' => $commission,
        ]);

        $order->setRelation('guiding', $guiding);
        $order->setRelation('guide', $guide);

        if ($order->design_type->generatesPdf()) {
            $this->attachPdf($order);
        }

        if (! app()->environment('local')) {
            $this->mailer->orderPlaced($order);
        }

        return $order;
    }

    /**
     * A failed PDF never loses the order: VoucherPaymentService tries again when the payment is
     * confirmed, and the BCC copy of the guide email lets operations follow up.
     */
    public function attachPdf(VoucherOrder $order): void
    {
        try {
            $order->forceFill(['pdf_path' => $this->pdfs->generate($order)])->save();
        } catch (Throwable $e) {
            Log::error('Voucher PDF generation failed', ['voucher_order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}
