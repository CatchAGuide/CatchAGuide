<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\EmailLog;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\TextPart;

class LogSentEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Illuminate\Mail\Events\MessageSent  $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        $message = $event->message;
        $data = $event->data;

        // Symfony Mailer: getTo() is Address[] with numeric keys (not email-keyed).
        $to = $message->getTo() ?? [];
        $recipient = $this->firstRecipientAddress($to);

        // Extract subject
        $subject = $message->getSubject();
        $type = $data['type'] ?? 'Unknown';
        $language = EmailLog::normalizeLanguage($data['language'] ?? app()->getLocale());

        // Determine target from mailable or data
        $target = null;
        if (isset($data['target'])) {
            $target = $data['target'];
        } elseif (isset($data['booking'])) {
            $booking = $data['booking'];
            $bookingId = is_object($booking) ? ($booking->id ?? null) : (is_array($booking) ? ($booking['id'] ?? null) : null);
            if ($bookingId) {
                $target = 'booking_' . $bookingId;
            }
        }

        $bodyHtml = $this->extractBodyHtml($message);

        // Prefer a clean array snapshot of mailable data; never lose body_html if model JSON fails.
        $dataSnapshot = json_decode(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR) ?: '{}',
            true
        );
        if (! is_array($dataSnapshot)) {
            $dataSnapshot = [];
        }

        // Log the email — store exact rendered HTML for later verification
        EmailLog::create([
            'email' => $recipient,
            'language' => $language,
            'subject' => $subject,
            'type' => $type,
            'status' => 1, // Assuming 1 means sent successfully
            'target' => $target,
            'additional_info' => json_encode([
                'data' => $dataSnapshot,
                'body_html' => $bodyHtml,
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        ]);
    }

    /**
     * @param  array<int|string, Address|string>  $to
     */
    private function firstRecipientAddress(array $to): string
    {
        if ($to === []) {
            return '';
        }

        $first = reset($to);
        if ($first instanceof Address) {
            return $first->getAddress();
        }

        $key = array_key_first($to);
        if (is_string($key) && $key !== '') {
            return $key;
        }

        return is_string($first) ? $first : '';
    }

    private function extractBodyHtml(mixed $message): ?string
    {
        if ($message instanceof Email) {
            $html = $message->getHtmlBody();
            if (is_string($html) && trim($html) !== '') {
                return $html;
            }

            $text = $message->getTextBody();
            if (is_string($text) && trim($text) !== '') {
                return nl2br(e($text));
            }

            $fromParts = $this->htmlFromParts($message);
            if ($fromParts !== null) {
                return $fromParts;
            }
        }

        return null;
    }

    private function htmlFromParts(Email $message): ?string
    {
        try {
            foreach ($message->getBody() instanceof AbstractPart ? [$message->getBody()] : [] as $part) {
                $found = $this->findHtmlPart($part);
                if ($found !== null) {
                    return $found;
                }
            }
        } catch (\Throwable) {
            // ignore — body extraction is best-effort
        }

        return null;
    }

    private function findHtmlPart(AbstractPart $part): ?string
    {
        if ($part instanceof TextPart
            && $part->getMediaType() === 'text'
            && $part->getMediaSubtype() === 'html'
        ) {
            $body = $part->getBody();
            return is_string($body) && trim($body) !== '' ? $body : null;
        }

        if (method_exists($part, 'getParts')) {
            foreach ($part->getParts() as $child) {
                if ($child instanceof AbstractPart) {
                    $found = $this->findHtmlPart($child);
                    if ($found !== null) {
                        return $found;
                    }
                }
            }
        }

        return null;
    }
}
