<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * Admin notification for a camp or trip checkout request (mails.admin.checkout_request): guest
 * contact, the requested product with its estimate, the guest's message and a reply deadline.
 * Rendered in the locale it is sent with (the booking's customerLocale()); replies go to the guest.
 */
abstract class CheckoutRequestAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    /** The guest is promised an answer within this many hours (see the guest confirmation). */
    public const REPLY_WITHIN_HOURS = 24;

    protected const COPY = 'emails.checkout_request_admin';

    /**
     * Product-specific variables for the shared view: productType ('camp'|'trip'), title,
     * shortTitle, location, url, image, rows, priceLines, total, subjectDate, subjectTotal.
     *
     * @return array<string, mixed>
     */
    abstract protected function productData(): array;

    /** The request this mail is about (CampVacationBooking or TripBooking). */
    abstract protected function request(): object;

    abstract protected function guestMessage(): string;

    abstract protected function requestsUrl(): string;

    public function build()
    {
        $data = $this->viewData();

        $mail = $this->view('mails.admin.checkout_request')
            ->subject($data['subject'])
            ->with($data);

        if ($data['guestEmail'] !== '') {
            $mail->replyTo($data['guestEmail'], $data['guestName'] ?: null);
        }

        return $mail;
    }

    /**
     * Variables for mails.admin.checkout_request in the current locale (also used by the admin
     * email preview).
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $request = $this->request();
        $product = $this->productData();
        $de = app()->getLocale() === 'de';

        $guestName = trim((string) $request->name);
        $guestEmail = trim((string) $request->email);
        $phone = trim(trim((string) $request->phone_country_code).' '.trim((string) $request->phone));
        $persons = (int) $request->number_of_persons;

        $deadline = ($request->created_at ?? now())->copy()
            ->addHours(self::REPLY_WITHIN_HOURS)
            ->locale(app()->getLocale());

        $subject = implode(' · ', array_filter([
            __(self::COPY.'.subject_'.$product['productType']),
            $product['shortTitle'],
            $product['subjectDate'],
            $persons > 0 ? __(self::COPY.'.persons_short', ['count' => $persons]) : null,
            $product['subjectTotal'],
        ], fn ($part) => $part !== null && $part !== ''));

        return array_merge($product, [
            'subject' => $subject,
            'badge' => __(self::COPY.'.badge_'.$product['productType']),
            'preheader' => __(self::COPY.'.preheader', [
                'deadline' => $deadline->translatedFormat($de ? 'D, d.m. H:i' : 'D, M j, H:i'),
                'name' => $guestName,
            ]),
            'deadline' => $deadline->translatedFormat($de ? 'D, d.m.Y, H:i' : 'D, M j, Y, H:i'),
            'guestName' => $guestName,
            'guestEmail' => $guestEmail,
            'guestPhone' => $phone,
            'guestPhoneHref' => $phone !== '' ? 'tel:'.preg_replace('/[^\d+]/', '', $phone) : null,
            'guestNote' => trim($this->guestMessage()),
            'requestUrl' => $this->requestsUrl(),
            'replyUrl' => $guestEmail !== ''
                ? 'mailto:'.$guestEmail.'?subject='.rawurlencode(__(self::COPY.'.reply_subject', ['title' => $product['title']]))
                : null,
        ]);
    }

    /**
     * "Mo., 12.10.2026" / "Mon, Oct 12, 2026".
     */
    protected function dayDate(?Carbon $date): ?string
    {
        if ($date === null) {
            return null;
        }

        $locale = app()->getLocale();

        return $date->copy()->locale($locale)->translatedFormat($locale === 'de' ? 'D, d.m.Y' : 'D, M j, Y');
    }

    /**
     * Short date for the subject line: "12.10." / "Oct 12".
     */
    protected function subjectDate(?Carbon $date): ?string
    {
        if ($date === null) {
            return null;
        }

        $locale = app()->getLocale();

        return $date->copy()->locale($locale)->translatedFormat($locale === 'de' ? 'd.m.' : 'M j');
    }
}
