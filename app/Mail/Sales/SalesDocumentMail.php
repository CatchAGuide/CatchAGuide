<?php

namespace App\Mail\Sales;

use App\Enums\Sales\SalesDocumentOutput;
use App\Models\SalesDocument;
use App\Presenters\Sales\SalesDocumentPresenter;
use App\Services\Sales\SalesLinks;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Offer or booking confirmation email to the customer, in the document language. Built from
 * the saved document, so it matches the customer page the button links to.
 */
class SalesDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging (App\Listeners\LogSentEmail)
    public $type;
    public $language;
    public $target;

    public function __construct(
        public SalesDocument $document,
        public SalesDocumentOutput $output,
    ) {
        $this->type = 'sales_'.$output->value;
        $this->language = $document->locale();
        $this->target = 'sales_document_'.$document->id;
        $this->locale($document->locale());
    }

    public function build()
    {
        $doc = app(SalesDocumentPresenter::class)->forDocument($this->document, $this->output);

        $mail = $this->view('mails.sales.document')
            ->subject($doc['subject'])
            ->with(['doc' => $doc] + app(SalesLinks::class)->shell($this->document->locale()));

        $replyTo = $this->document->creator?->email;
        if (filled($replyTo)) {
            $mail->replyTo($replyTo, $this->document->creator->name);
        }

        return $mail;
    }
}
