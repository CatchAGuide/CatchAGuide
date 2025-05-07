<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Lang;

class RegistrationVerification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $language;
    public $target;
    public $type = 'registration_verification';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->language = app()->getLocale();
        $this->target = 'registration_verification';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $user = $this->user;
        // $data = compact('user');
        // return $this->view('mails.registration-verification', $data)
        //     ->to($user->email)
        //     //->cc(env('CC_MAIL','info@catchaguide.com'))
        //     ->subject(__('registration-verification.subject'));
    }
}
