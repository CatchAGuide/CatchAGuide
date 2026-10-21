<?php

namespace App\Mail\Guest;

use App\Enums\VoucherDesign;
use App\Models\Guiding;
use App\Models\User;
use App\Models\UserInformation;
use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Thank-you to the buyer of a gift voucher: order summary and the next steps. The guide gets in
 * touch with payment details and sends the voucher once paid. Nothing is attached and nothing is
 * charged: CaG takes no payment.
 */
class VoucherOrderGuestMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guest_voucher_order';
    public $language;
    public $target;

    public function __construct(
        public VoucherOrder $order,
    ) {
        $this->language = $order->language;
        $this->target = 'voucher_order_'.$order->id;
    }

    /**
     * Unsaved sample order (with recipient, sender and message) for the admin email preview.
     */
    public static function sample(VoucherDesign $design = VoucherDesign::CagStandard): self
    {
        $de = app()->getLocale() === 'de';

        $guide = (new User)->forceFill([
            'firstname' => 'Thomas',
            'lastname' => 'Berger',
            'email' => 'thomas.berger@example.com',
            'phone_country_code' => '+49',
            'phone' => '170 1234567',
        ]);
        $guide->setRelation('information', (new UserInformation)->forceFill(['company_name' => 'Berger Angelguiding']));

        $guiding = (new Guiding)->forceFill([
            'title' => $de ? 'Zanderangeln am Rhein' : 'Zander fishing on the Rhine',
            'city' => 'Köln',
            'duration' => 6,
            'duration_type' => 'hours',
            'slug' => 'zanderangeln-am-rhein',
            'is_boat' => 1,
        ]);

        $order = (new VoucherOrder)->forceFill([
            'voucher_code' => 'CAG-V-7KQ3PX',
            'tour_title_snapshot' => $guiding->title,
            'number_of_guests' => 2,
            'price_total' => 380,
            'currency' => 'EUR',
            'recipient_names' => 'Max & Anna',
            'sender_name' => 'Lena',
            'personal_message' => $de ? 'Alles Gute zum Geburtstag! Petri Heil!' : 'Happy birthday! Tight lines!',
            'buyer_first_name' => 'Lena',
            'buyer_last_name' => 'Schmidt',
            'buyer_email' => 'lena.schmidt@example.com',
            'buyer_phone_country_code' => '+49',
            'buyer_phone' => '151 2345678',
            'design_type' => $design,
            'language' => app()->getLocale(),
            'voucher_language' => app()->getLocale(),
            'valid_until' => now()->addYears(3)->endOfYear()->startOfDay(),
            'created_at' => now(),
        ]);
        $order->setRelation('guiding', $guiding);
        $order->setRelation('guide', $guide);

        return new self($order);
    }

    public function build()
    {
        $data = $this->viewData();

        return $this->view('mails.guest.voucher_order')
            ->subject(__('emails.voucher_order_guest.subject', ['tour' => $data['tourTitle']]))
            ->with($data);
    }

    /**
     * Variables for mails.guest.voucher_order in the current locale (also used by the admin
     * email preview).
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        return app(VoucherOrderPresenter::class)->details($this->order) + [
            'ownDesign' => $this->order->design_type === VoucherDesign::GuideOwn,
            'contactEmail' => (string) config('mail.admin_email'),
            'site' => preg_replace('/^www\./', '', (string) parse_url(url('/'), PHP_URL_HOST)),
        ];
    }
}
