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
        'kitchen_type',
        'bathroom',
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
        'price_type',
        'price_per_night',
        'price_per_week',
        'currency',
        'amenities',
        'kitchen_equipment',
        'bathroom_amenities',
        'policies',
        'rental_conditions',
        'bed_types',
        'per_person_pricing',
        'extras',
        'inclusives',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'amenities' => 'array',
        'kitchen_equipment' => 'array',
        'bathroom_amenities' => 'array',
        'policies' => 'array',
        'rental_conditions' => 'array',
        'bed_types' => 'array',
        'per_person_pricing' => 'array',
        'extras' => 'array',
        'inclusives' => 'array',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
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
