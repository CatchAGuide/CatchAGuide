<?php

namespace App\Mail\Sales;

use App\Models\SalesDocument;
use App\Services\Sales\SalesFormat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Internal notice to the document's creator and the sales inbox after a customer accepted.
 */
class SalesOfferAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging (App\Listeners\LogSentEmail)
    public $type = 'sales_offer_accepted_internal';
    public $language;
    public $target;

    public function __construct(public SalesDocument $document)
    {
        $this->language = app()->getLocale();
        $this->target = 'sales_document_'.$document->id;
    }

    public function build()
    {
        $document = $this->document;
        $locale = app()->getLocale();
        $period = $document->travel_from
            ? SalesFormat::date($document->travel_from->toDateString(), $locale)
                .($document->travel_to && ! $document->travel_to->eq($document->travel_from) ? ' – '.SalesFormat::date($document->travel_to->toDateString(), $locale) : '')
            : null;

        return $this->view('mails.sales.accepted-internal')
            ->subject(__('sales.internal.subject', ['number' => $document->number, 'customer' => $document->fullName() ?: $document->email]))
            ->with([
                'number' => $document->number,
                'customer' => $document->fullName() ?: (string) $document->email,
                'email' => (string) $document->email,
                'total' => SalesFormat::money($document->total_amount, $locale),
                'period' => $period,
                'builderUrl' => route('admin.sales.offers.edit', $document),
            ]);
    }
}
