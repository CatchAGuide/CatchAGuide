<?php

namespace App\Jobs;

use App\Mail\Booking\BookingRescheduledGuest;
use App\Mail\Booking\BookingRescheduledGuide;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRescheduleEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $newBooking;
    protected $originalBooking;

    /**
     * Create a new job instance.
     *
     * @param Booking $newBooking
     * @param Booking $originalBooking
     * @return void
     */
    public function __construct(Booking $newBooking, Booking $originalBooking)
    {
        $this->newBooking = $newBooking;
        $this->originalBooking = $originalBooking;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Send email to guide
        Mail::send(new BookingRescheduledGuide(
            $this->newBooking,
            $this->originalBooking,
            $this->newBooking->guiding,
            $this->newBooking->guiding->user,
            $this->newBooking->user
        ));

        // Send email to guest/user
        if ($this->newBooking->is_guest) {
            Mail::send(new BookingRescheduledGuest(
                $this->newBooking,
                $this->originalBooking,
                $this->newBooking->guiding,
                $this->newBooking->guiding->user,
                $this->newBooking->user
            ));
        } else {
            Mail::send(new BookingRescheduledGuest(
                $this->newBooking,
                $this->originalBooking,
                $this->newBooking->guiding,
                $this->newBooking->guiding->user,
                $this->newBooking->user
            ));
        }
    }
} 