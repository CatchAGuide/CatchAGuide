<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DDoSAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alertType;
    public $details;
    public $timestamp;

    public function __construct($alertType, $details)
    {
        $this->alertType = $alertType;
        $this->details = $details;
        $this->timestamp = now();
    }

    public function build()
    {
        return $this->subject("🚨 DDoS Alert: {$this->alertType}")
                    ->view('emails.ddos-alert')
                    ->with([
                        'alertType' => $this->alertType,
                        'details' => $this->details,
                        'timestamp' => $this->timestamp,
                    ]);
    }
}
