<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpecialOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'location',
        'latitude',
        'longitude',
        'country',
        'city',
        'region',
        'thumbnail_path',
        'gallery_images',
        'whats_included',
        'pricing',
        'price_type',
        'currency',
        'status',
        'user_id',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'whats_included' => 'array',
        'pricing' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $attributes = [
        'status' => 'draft',
        'currency' => 'USD',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function accommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class, 'special_offer_accommodation');
    }

    public function rentalBoats(): BelongsToMany
    {
        return $this->belongsToMany(RentalBoat::class, 'special_offer_rental_boat');
    }

    public function guidings(): BelongsToMany
    {
        return $this->belongsToMany(Guiding::class, 'special_offer_guiding');
    }

    public function camps(): BelongsToMany
    {
        return $this->belongsToMany(Camp::class, 'camp_special_offer');
    }

    /**
     * Get the lowest price from pricing data
     */
    public function getLowestPrice(): float
    {
        $prices = [];
        $pricing = $this->pricing;

        if (is_string($pricing)) {
            $pricing = json_decode($pricing, true);
        }

        if (is_array($pricing) && !empty($pricing)) {
            foreach ($pricing as $tier) {
                if (!is_array($tier)) {
                    continue;
                }

                // Check price_per_night
                if (isset($tier['price_per_night']) && $tier['price_per_night'] > 0) {
                    $prices[] = (float) $tier['price_per_night'];
                }

                // Check price_per_week and convert to per-night
                if (isset($tier['price_per_week']) && $tier['price_per_week'] > 0) {
                    $prices[] = (float) $tier['price_per_week'] / 7;
                }

                // Check amount (generic pricing)
                if (isset($tier['amount']) && $tier['amount'] > 0) {
                    $prices[] = (float) $tier['amount'];
                }
            }
        }

        return !empty($prices) ? (float) min($prices) : 0.0;
    }
}
