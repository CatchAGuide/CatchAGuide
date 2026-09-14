<?php

namespace Tests\Feature\Vacation;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class CampAttachmentCardChipsTest extends TestCase
{
    public function test_accommodation_card_uses_the_shared_persons_icon_not_an_emoji(): void
    {
        app()->setLocale('de');
        $html = $this->renderAccommodationCard();

        $this->assertStringContainsString('user-new.svg', $html);
        $this->assertStringContainsString('4 '.__('vacations.pers_short'), $html);
        $this->assertStringNotContainsString('👥', $html);
        $this->assertStringContainsString('attachment-chip--persons', $html);
        $this->assertStringContainsString('attachment-chip--bath', $html);
        $this->assertStringContainsString('attachment-chip--area', $html);
        $this->assertStringContainsString('attachment-chip--bedrooms', $html);
        $this->assertStringContainsString('attachment-chip--bed"', $html);
        $this->assertGreaterThanOrEqual(4, substr_count($html, 'attachment-chip--bed"'));
        $this->assertStringContainsString('(5) Einzelbett', $html);
        $this->assertStringContainsString('(1) Sofabett', $html);
        $this->assertStringContainsString('(1) Kinderbett', $html);
        $this->assertStringContainsString('(1) Klappbett', $html);
        $this->assertStringNotContainsString(__('accommodations.bedrooms').':', $html);
        $this->assertStringNotContainsString('Schlafzimmer:', $html);
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
        $this->assertStringContainsString('attachment-chip--water-type', $html);
        $this->assertStringContainsString('See', $html);
        $this->assertStringContainsString(__('vacations.chip_water_type'), $html);
        $this->assertStringContainsString('attachment-chip__tooltip', $html);
        $this->assertStringContainsString(__('vacations.max_persons'), $html);
        $this->assertStringContainsString('attachment-expand-btn', $html);
    }

    public function test_guiding_card_shows_shore_or_boat_instead_of_private(): void
    {
        $html = View::make('components.guiding.card', [
            'guiding' => [
                'id' => 22,
                'title' => 'Pike tour',
                'description' => 'Morning session',
                'thumbnail_path' => '/images/placeholder.jpg',
                'gallery_images' => [],
                'duration_label' => '8 hours',
                'max_persons' => 3,
                'type' => 'private',
                'inclusives' => [],
                'guiding_info' => [],
                'target_fish' => [],
                'methods' => [],
                'start_times' => [],
                'price' => ['amount' => 250, 'display_type' => 'Per tour'],
            ],
        ])->render();

        $this->assertStringNotContainsString('Private', $html);
        $this->assertStringNotContainsString('private', $html);
        $this->assertStringNotContainsString('attachment-chip--tour', $html);
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
        app()->setLocale('de');
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
                    'number_of_bathrooms' => 1,
                    'number_of_bedrooms' => 2,
                    'bed_summary' => '(1) double',
                    'bed_items' => [
                        ['count' => 1, 'name' => 'Doppelbett', 'name_en' => 'Double Bed'],
                    ],
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
                    'water_types' => [
                        ['id' => 2, 'name' => 'See', 'name_en' => 'Lake'],
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
        $this->assertStringNotContainsString(__('accommodations.bedrooms').':', $html);
        $this->assertStringContainsString('attachment-chip--bath', $html);
        $this->assertStringContainsString('attachment-chip--bed"', $html);
        $this->assertStringContainsString('(1) Doppelbett', $html);
        $this->assertStringContainsString('attachment-chip--water-type', $html);
        $this->assertStringContainsString('See', $html);
        $this->assertStringNotContainsString('Schlafzimmer:', $html);
        $this->assertStringContainsString('attachment-chip__tooltip', $html);
        $this->assertStringContainsString(__('vacations.max_persons'), $html);
        $this->assertStringContainsString('attachment-expand-btn', $html);
    }

    public function test_accommodation_extras_use_a_red_x_instead_of_the_included_check(): void
    {
        app()->setLocale('de');
        $html = $this->renderAccommodationCard([
            'extras_inclusives' => [
                'inclusives' => ['WLAN'],
                'extras' => ['Endreinigung'],
            ],
        ]);

        $this->assertStringContainsString(__('vacations.excluded'), $html);
        $this->assertStringContainsString('accommodation-card__panel-title">'.__('vacations.excluded'), $html);
        $this->assertStringContainsString('accommodation-card__check-icon', $html);
        $this->assertStringContainsString('WLAN', $html);
        $this->assertStringContainsString('accommodation-card__inclusive-chip--extra', $html);
        $this->assertStringContainsString('accommodation-card__extra-icon', $html);
        $this->assertStringContainsString(__('vacations.extra_addon_hint'), $html);
        $this->assertStringContainsString('Endreinigung', $html);
        $this->assertStringNotContainsString('✅ Endreinigung', $html);
        $this->assertStringNotContainsString('✅ WLAN', $html);
    }

    private function renderAccommodationCard(array $overrides = []): string
    {
        return View::make('components.accommodation.card', [
            'accommodation' => array_merge([
                'id' => 11,
                'title' => 'Apartment 3',
                'accommodation_type' => 'Apartment',
                'thumbnail_path' => '/images/placeholder.jpg',
                'gallery_images' => [],
                'max_occupancy' => 4,
                'number_of_bathrooms' => 1,
                'living_area_sqm' => 80,
                'number_of_bedrooms' => 3,
                'bed_summary' => '(5) Einzelbett, (1) Sofabett, (1) Kinderbett, (1) Klappbett',
                'bed_items' => [
                    ['count' => 5, 'name' => 'Einzelbett', 'name_en' => 'Single Bed'],
                    ['count' => 1, 'name' => 'Sofabett', 'name_en' => 'Sofa Bed'],
                    ['count' => 1, 'name' => 'Kinderbett', 'name_en' => 'Cot'],
                    ['count' => 1, 'name' => 'Klappbett', 'name_en' => 'Folding Bed'],
                ],
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
            ], $overrides),
        ])->render();
    }

    public function test_guiding_card_location_schedule_shows_desc_meeting_point_not_legacy_column(): void
    {
        $html = View::make('components.guiding.card', [
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
                'meeting_point' => 'Bucht Nord - Riba Roja',
                'desc_meeting_point' => 'Meet at the harbour office, gate 3.',
                'start_times' => ['06:00'],
                'price' => ['amount' => 250, 'display_type' => 'Per tour'],
            ],
        ])->render();

        $this->assertStringContainsString(e(__('vacations.location_schedule')), $html);
        $this->assertStringContainsString(__('guidings.Meeting_Point'), $html);
        $this->assertStringContainsString('Meet at the harbour office, gate 3.', $html);
        $this->assertStringNotContainsString('Bucht Nord - Riba Roja', $html);
        $this->assertStringContainsString('06:00', $html);
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
                'water_types' => [
                    ['id' => 2, 'name' => 'See', 'name_en' => 'Lake'],
                ],
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
