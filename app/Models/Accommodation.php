<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accommodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'user_id',
        'title',
        'slug',
        'thumbnail_path',
        'gallery_images',
        'location',
        'city',
        'country',
        'region',
        'lat',
        'lng',
        'description',
        'accommodation_type',
        'condition_or_style',
        'living_area_sqm',
        'floor_layout',
        'max_occupancy',
        'number_of_bedrooms',
        'number_of_beds',
        'bed_types',
        'living_room',
        'dining_room_or_area',
        'terrace',
        'garden',
        'swimming_pool',
        'kitchen_type',
        'refrigerator_freezer',
        'oven',
        'stove_or_ceramic_hob',
        'microwave',
        'dishwasher',
        'coffee_machine',
        'cookware_and_dishes',
        'bathroom',
        'washing_machine',
        'dryer',
        'separate_laundry_room',
        'freezer_room',
        'filleting_house',
        'wifi_or_internet',
        'bed_linen_included',
        'utilities_included',
        'pets_allowed',
        'smoking_allowed',
        'reception_available',
        'location_description',
        'distance_to_water_m',
        'distance_to_boat_berth_m',
        'distance_to_shop_km',
        'distance_to_parking_m',
        'distance_to_nearest_town_km',
        'distance_to_airport_km',
        'distance_to_ferry_port_km',
        'changeover_day',
        'minimum_stay_nights',
        'rental_includes',
        'price_per_night',
        'price_per_week',
        'currency',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'bed_types' => 'array',
        'rental_includes' => 'array',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'living_room' => 'boolean',
        'dining_room_or_area' => 'boolean',
        'terrace' => 'boolean',
        'garden' => 'boolean',
        'swimming_pool' => 'boolean',
        'refrigerator_freezer' => 'boolean',
        'oven' => 'boolean',
        'stove_or_ceramic_hob' => 'boolean',
        'microwave' => 'boolean',
        'dishwasher' => 'boolean',
        'coffee_machine' => 'boolean',
        'cookware_and_dishes' => 'boolean',
        'washing_machine' => 'boolean',
        'dryer' => 'boolean',
        'separate_laundry_room' => 'boolean',
        'freezer_room' => 'boolean',
        'filleting_house' => 'boolean',
        'wifi_or_internet' => 'boolean',
        'bed_linen_included' => 'boolean',
        'utilities_included' => 'boolean',
        'pets_allowed' => 'boolean',
        'smoking_allowed' => 'boolean',
        'reception_available' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'active',
        'currency' => 'EUR',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
