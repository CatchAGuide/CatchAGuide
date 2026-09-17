<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QueueStalledAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $staleCount;
    public int $oldestAgeMinutes;
    public int $staleThresholdMinutes;
    public $timestamp;

    public function __construct(int $staleCount, int $oldestAgeMinutes, int $staleThresholdMinutes)
    {
        $this->staleCount = $staleCount;
        $this->oldestAgeMinutes = $oldestAgeMinutes;
        $this->staleThresholdMinutes = $staleThresholdMinutes;
        $this->timestamp = now();
    }

    public function build()
    {
        return $this->subject('⚠️ Some booking emails haven\'t gone out yet')
                    ->view('emails.queue-stalled-alert')
                    ->with([
                        'staleCount' => $this->staleCount,
                        'oldestAgeMinutes' => $this->oldestAgeMinutes,
                        'staleThresholdMinutes' => $this->staleThresholdMinutes,
                        'timestamp' => $this->timestamp,
                    ]);
    }
}
