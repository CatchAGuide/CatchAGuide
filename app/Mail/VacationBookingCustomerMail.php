<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VacationBookingCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $description;
    public $contact_message;
    public $phone;
    public $phone_country_code;
    public $preferred_date;
    public $number_of_persons;
    public $source_type;
    public $source_id;
    public $camp_id;
    public $source_title;
    public $language;
    public $target;
    public $copyNamespace = 'emails.vacation_booking_customer';
    public $type = 'vacation_booking_customer_mail';

    /**
     * @param  array<string, mixed>|null  $extra
     */
    public function __construct(
        $name,
        $email,
        $description,
        $phone = null,
        $phone_country_code = null,
        ?array $extra = null
    ) {
        $extra = $extra ?? [];

        $this->name = $name;
        $this->email = $email;
        $this->description = $description;
        $this->contact_message = $extra['contact_message'] ?? $description;
        $this->phone = $phone;
        $this->phone_country_code = $phone_country_code;
        $this->preferred_date = $extra['preferred_date'] ?? null;
        $this->number_of_persons = $extra['number_of_persons'] ?? null;
        $this->source_type = $extra['source_type'] ?? null;
        $this->source_id = $extra['source_id'] ?? null;
        $this->camp_id = $extra['camp_id'] ?? null;
        $this->source_title = $extra['source_title'] ?? null;
        $this->language = app()->getLocale();
        $this->target = 'vacation_booking_customer_mail';
    }

    public function build()
    {
        return $this->view('mails.customercontactmail')
            ->subject(__($this->copyNamespace . '.subject'));
    }
}
