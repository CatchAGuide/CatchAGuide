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

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        return $this->view('mails.searchrequestusermail',[
            'country' => $this->mailData['country'],
            'city' => $this->mailData['city'],
            'days_of_tour' => $this->mailData['days_of_tour'],
            'specific_number_of_days' => $this->mailData['specific_number_of_days'],
            'accomodation' => $this->mailData['accomodation'],
            'targets' => $this->mailData['targets'],
            'methods' => $this->mailData['methods'],
            'fishing_from' => $this->mailData['fishing_from'],
            'boat_info' => $this->mailData['boat_info'],
            'guiding_equipment' => $this->mailData['guiding_equipment'],
            'number_of_guest' => $this->mailData['number_of_guest'],
            'date_of_tour' => date("Y-m-d", strtotime($this->mailData['date_of_tour'])),
            'name' => $this->mailData['name'],
            'phone' => $this->mailData['phone'],
            'email' => $this->mailData['email'],
        ])->subject("Guiding Request – Catch A Guide");
    }
}
