<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class AutomaticRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $tempPassword;
    public $language;   
    public $target;
    public $type = 'automatic_registration_mail';

    public function __construct($user,$tempPassword)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;
        $this->language = app()->getLocale();
        $this->target = 'automatic_registration_mail';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.guest.automatic_guest_registration')
        ->with([
            'user' => $this->user,
            'tempPassword' => $this->tempPassword,
        ])
        ->subject(__('profile.br-confirmation')." – Catch A Guide");
    }
}
