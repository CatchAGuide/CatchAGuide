<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SearchRequestUserMail extends Mailable
{
    use Queueable, SerializesModels;
    public $mailData = [];
    public $language;
    public $target;
    public $type = 'search_request_user_mail';


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
        $this->language = $mailData->user->language;
        $this->target = 'search_request_user_mail';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this->view('mails.searchrequestusermail',[
            'name' => $this->mailData['name'],
        ])->subject("Guiding Request – Catch A Guide");
    }
}
