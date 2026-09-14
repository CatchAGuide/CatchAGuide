<?php

namespace Tests\Feature\Vacation;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class CampAttachmentCardChipsTest extends TestCase
{
    public function test_accommodation_card_uses_the_shared_persons_icon_not_an_emoji(): void
    {
        $html = $this->renderAccommodationCard();

        $this->assertStringContainsString('user-new.svg', $html);
        $this->assertStringContainsString('4 '.__('vacations.pers_short'), $html);
        $this->assertStringNotContainsString('👥', $html);
        $this->assertStringContainsString('attachment-chip--persons', $html);
        $this->assertStringContainsString('attachment-chip--bath', $html);
        $this->assertStringContainsString('attachment-chip--area', $html);
        $this->assertStringContainsString('attachment-chip--water', $html);
        $this->assertStringContainsString('attachment-chip__tooltip', $html);
        $this->assertStringContainsString(__('vacations.max_persons'), $html);
        $this->assertStringContainsString('attachment-expand-btn', $html);
    }

    public function test_guiding_card_uses_the_same_persons_icon_as_other_attachments(): void
    {
        $html = $this->renderGuidingCard();

        $this->assertStringContainsString('user-new.svg', $html);
        $this->assertStringContainsString('3 '.__('vacations.pers_short'), $html);
        $this->assertStringContainsString('attachment-chip--persons', $html);
        $this->assertStringContainsString('attachment-chip--duration', $html);
        $this->assertStringContainsString('clock-new.svg', $html);
        $this->assertStringContainsString('attachment-chip__tooltip', $html);
        $this->assertStringContainsString(__('vacations.max_persons'), $html);
        $this->assertStringContainsString('attachment-expand-btn', $html);
    }

    public function test_rental_boat_card_shows_capacity_as_the_shared_persons_chip(): void
    {
        $html = $this->renderRentalBoatCard();

        $this->assertStringContainsString('user-new.svg', $html);
        $this->assertStringContainsString('5 '.__('vacations.pers_short'), $html);
        $this->assertStringContainsString('attachment-chip--persons', $html);
        $this->assertStringContainsString('attachment-chip--engine', $html);
        $this->assertStringNotContainsString(__('rental_boats.capacity').':', $html);
        $this->assertStringContainsString('attachment-chip__tooltip', $html);
        $this->assertStringContainsString(__('vacations.max_persons'), $html);
        $this->assertStringContainsString('attachment-expand-btn', $html);
    }

    public function test_special_offer_nested_cards_reuse_the_same_persons_chip(): void
    {
        $html = View::make('components.special-offer.card', [
            'specialOffer' => [
                'id' => 9,
                'title' => 'Camp package',
                'thumbnail_path' => '/images/placeholder.jpg',
                'gallery_images' => [],
                'whats_included' => [],
                'pricing_extras' => [],
                'accommodations' => [],
                'rental_boats' => [],
                'guidings' => [],
                'price' => ['amount' => 199, 'currency' => 'EUR'],
                'accommodations_full' => [[
                    'id' => 1,
                    'title' => 'Lakeside cabin',
                    'accommodation_type' => 'Cabin',
                    'max_occupancy' => 6,
                    'living_area_sqm' => 70,
                    'bed_summary' => '1 double',
                    'distances' => ['to_water_m' => 20, 'to_parking_m' => 40],
                ]],
                'rental_boats_full' => [[
                    'id' => 2,
                    'title' => 'Bass boat',
                    'type' => 'Boat',
                    'specs' => [
                        ['key' => 'capacity', 'label' => __('rental_boats.capacity'), 'value' => 2],
                    ],
                ]],
                'guidings_full' => [[
                    'id' => 3,
                    'title' => 'Dawn tour',
                    'guiding_info' => [
                        'art' => 'Boat',
                        'dauer' => '8 hours',
                        'max_personen' => 4,
                    ],
                ]],
            ],
        ])->render();

        $this->assertSame(3, substr_count($html, 'user-new.svg'));
        $this->assertStringContainsString('6 '.__('vacations.pers_short'), $html);
        $this->assertStringContainsString('2 '.__('vacations.pers_short'), $html);
        $this->assertStringContainsString('4 '.__('vacations.pers_short'), $html);
        $this->assertStringNotContainsString(__('rental_boats.capacity').':', $html);
        $this->assertStringNotContainsString(__('vacations.max_persons').':', $html);
        $this->assertStringContainsString('attachment-chip__tooltip', $html);
        $this->assertStringContainsString(__('vacations.max_persons'), $html);
        $this->assertStringContainsString('attachment-expand-btn', $html);
    }

    private function renderAccommodationCard(): string
    {
        return View::make('components.accommodation.card', [
            'accommodation' => [
                'id' => 11,
                'title' => 'Apartment 3',
                'accommodation_type' => 'Apartment',
                'thumbnail_path' => '/images/placeholder.jpg',
                'gallery_images' => [],
                'max_occupancy' => 4,
                'number_of_bathrooms' => 1,
                'living_area_sqm' => 80,
                'bed_summary' => '2 bedrooms',
                'distances' => [
                    'to_water_m' => 15,
                    'to_berth_m' => 40,
                    'to_parking_m' => 10,
                ],
                'accommodation_details' => [],
                'policies' => [],
                'amenities' => [],
                'kitchen' => [],
                'bathroom_laundry' => [],
                'extras_inclusives' => [],
                'price' => ['amount' => 120, 'type' => 'per_night'],
            ],
        ])->render();
    }

    private function renderGuidingCard(): string
    {
        return View::make('components.guiding.card', [
            'guiding' => [
                'id' => 22,
                'title' => 'Pike tour',
                'description' => 'Morning session',
                'thumbnail_path' => '/images/placeholder.jpg',
                'gallery_images' => [],
                'duration_label' => '8 hours',
                'max_persons' => 3,
                'type' => 'Boat',
                'inclusives' => [],
                'guiding_info' => [],
                'target_fish' => [],
                'methods' => [],
                'meeting_point' => null,
                'start_times' => [],
                'price' => ['amount' => 250, 'display_type' => 'Per tour'],
            ],
        ])->render();
    }

    private function renderRentalBoatCard(): string
    {
        return View::make('components.rental-boat.card', [
            'boat' => [
                'id' => 33,
                'title' => 'Tracker',
                'type' => 'Fishing boat',
                'thumbnail_path' => '/images/placeholder.jpg',
                'gallery_images' => [],
                'inclusives' => [],
                'extras' => [],
                'requirements' => [],
                'boat_info' => [],
                'specs' => [
                    ['key' => 'capacity', 'label' => __('rental_boats.capacity'), 'value' => 5],
                    ['key' => 'engine', 'label' => __('rental_boats.engine'), 'value' => '40 HP'],
                ],
                'price' => ['amount' => 90, 'display_type' => 'Per day'],
            ],
        ])->render();
    }
}
