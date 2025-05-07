<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\SearchRequest;

class GuidingRequestMail extends Mailable
{
    use Queueable, SerializesModels;
    public $request;
    public $language;   
    public $target;
    public $type = 'guiding_request_mail';


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SearchRequest $request)
    {
        $this->request = $request;
        $this->language = $request->user->language;
        $this->target = 'guiding_request_mail';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this->view('mails.guidingrequestmail', [
            'request' => $this->request,
        ])->subject("Guiding Request – Catch A Guide");
    }
}
