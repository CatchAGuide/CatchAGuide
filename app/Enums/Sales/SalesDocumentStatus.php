<?php

namespace App\Enums\Sales;

/**
 * Lifecycle of a sales document (offer / booking confirmation). See the status flow in
 * App\Services\Sales\SalesDocumentStatusFlow for which transitions are allowed.
 */
enum SalesDocumentStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Viewed = 'viewed';
    case Accepted = 'accepted';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('sales.status.'.$this->value);
    }

    /**
     * Bootstrap badge colour in the admin list.
     */
    public function badge(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Sent, self::Viewed => 'info',
            self::Accepted => 'warning',
            self::Confirmed => 'success',
            self::Declined, self::Expired, self::Cancelled => 'danger',
        };
    }

    /**
     * Open offers the customer can still accept and the expiry job may expire.
     */
    public function isAwaitingCustomer(): bool
    {
        return $this === self::Sent || $this === self::Viewed;
    }
}
