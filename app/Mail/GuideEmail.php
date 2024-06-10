<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuideEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstname;
    public $lastname;
    public $birthday;
    public $address;
    public $address_number;
    public $email;
    public $postal;
    public $city;
    public $phone;
    public $languages;
    public $description;
    public $favorite_fish;
    public $years;
    private $taxId;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($firstname, $lastname, $birthday, $address, $address_number, $postal, $city, $phone, $languages, $description, $favorite_fish, $years, $taxId)
    {
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->birthday = $birthday;
        $this->address = $address;
        $this->address_number = $address_number;
        $this->email = auth()->user()->email;
        $this->postal = $postal;
        $this->city = $city;
        $this->phone = $phone;
        $this->languages = $languages;
        $this->description = $description;
        $this->favorite_fish = $favorite_fish;
        $this->years = $years;
        $this->taxId = $taxId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {
        return $this->view('mails.guidemail', ['taxId' => $this->taxId])
            ->to(env('TO_MAIL','info@catchaguide.com'))
            ->subject("Anmeldung als Guide");
    }
}
