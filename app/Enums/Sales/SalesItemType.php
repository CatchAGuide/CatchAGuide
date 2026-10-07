<?php

namespace App\Enums\Sales;

/**
 * One row in sales_document_items. Tour, camp, trip and custom are product cards; the
 * others are children of a card (parent_item_id).
 */
enum SalesItemType: string
{
    case Tour = 'tour';
    case TourExtra = 'tour_extra';
    case Camp = 'camp';
    case CampAccommodation = 'camp_accommodation';
    case CampBoat = 'camp_boat';
    case CampGuiding = 'camp_guiding';
    case Trip = 'trip';
    case Custom = 'custom';

    public function isCard(): bool
    {
        return in_array($this, [self::Tour, self::Camp, self::Trip, self::Custom], true);
    }

    /**
     * Customer-facing type label ("Angeltour" / "Fishing tour").
     */
    public function kindLabel(): string
    {
        return __('sales.kind.'.$this->value);
    }
}
