<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Log;
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
        $mailable = $data['mailable'] ?? null;
        
        // Extract recipient
        $to = $message->getTo();
        $recipient = array_keys($to)[0] ?? '';
        
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
}
