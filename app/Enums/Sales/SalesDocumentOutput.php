<?php

namespace App\Enums\Sales;

/**
 * What is sent from a sales document: the offer or the booking confirmation.
 */
enum SalesDocumentOutput: string
{
    case Offer = 'offer';
    case Confirmation = 'confirmation';
}
