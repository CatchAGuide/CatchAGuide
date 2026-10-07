<?php

namespace App\Enums\Sales;

/**
 * How a line's unit price was multiplied (stored on each item for the record).
 */
enum SalesPriceUnit: string
{
    case GroupPrice = 'group_price';
    case PerPerson = 'per_person';
    case PerBooking = 'per_booking';
    case PerItem = 'per_item';
    case PerNight = 'per_night';
    case PerPersonNight = 'per_person_night';
    case PerDay = 'per_day';
    case Custom = 'custom';
}
