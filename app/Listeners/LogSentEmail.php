<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\EmailLog;
use Symfony\Component\Mime\Address;

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
     * @param  \Illuminate\\Mail\\Events\\MessageSent  $event
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
        $language = $data['language'] ?? app()->getLocale();
        
        // Determine target from mailable or data
        $target = null;
        if (isset($data['target'])) {
            $target = $data['target'];
        } elseif (isset($data['booking'])) {
            $target = 'booking_' . $data['booking']->id;
        }
        
        // Log the email
        EmailLog::create([
            'email' => $recipient,
            'language' => $language,
            'subject' => $subject,
            'type' => $type,
            'status' => 1, // Assuming 1 means sent successfully
            'target' => $target,
            'additional_info' => json_encode([
                'data' => $data,
            ]),
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
}
