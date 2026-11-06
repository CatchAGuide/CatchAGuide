<?php

namespace App\Enums;

/**
 * Kind of a voucher_orders row (voucher_orders.kind).
 */
enum VoucherOrderKind: string
{
    /** A tour bought as a voucher at /checkout/vouchers/{slug}; the guide handles it. */
    case Order = 'order';

    /**
     * A request from the /guidings/voucher landing page: destination and amount, no tour yet.
     * CaG follows up; its progress is the row's open / in process / done status.
     */
    case Request = 'request';
}
