<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalBoat extends Model
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
        'boat_type',
        'desc_of_boat',
        'requirements',
        'boat_information',
        'boat_extras',
        'price_type',
        'prices',
        'pricing_extra',
        'inclusions',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'boat_information' => 'array',
        'boat_extras' => 'array',
        'prices' => 'array',
        'pricing_extra' => 'array',
        'inclusions' => 'array',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
