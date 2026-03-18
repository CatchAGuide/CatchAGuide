<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Booking $booking;
    public bool $sendEmails;
    public string $createdSource;

    public function __construct(Booking $booking, bool $sendEmails = true, string $createdSource = 'frontend')
    {
        $this->booking = $booking;
        $this->sendEmails = $sendEmails;
        $this->createdSource = $createdSource;
    }
}

