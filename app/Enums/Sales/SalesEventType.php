<?php

namespace App\Enums\Sales;

enum SalesEventType: string
{
    case Created = 'created';
    case OfferSent = 'offer_sent';
    case ConfirmationSent = 'confirmation_sent';
    case Viewed = 'viewed';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case PriceAdjusted = 'price_adjusted';
    case EmailBounced = 'email_bounced';
    case Imported = 'imported';

    public function label(): string
    {
        return __('sales.event.'.$this->value);
    }
}
