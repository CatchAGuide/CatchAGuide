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
        'max_persons',
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
        'requirements' => 'array',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    /**
     * Get the boat extras attribute with proper JSON handling
     */
    public function getBoatExtrasAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            // If not valid JSON, try comma-separated
            return explode(',', $value);
        }
        return $value;
    }

    /**
     * Set the boat extras attribute with proper JSON encoding
     */
    public function setBoatExtrasAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['boat_extras'] = json_encode($value);
        } else {
            $this->attributes['boat_extras'] = $value;
        }
    }

    /**
     * Get the inclusions attribute with proper JSON handling
     */
    public function getInclusionsAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            // If not valid JSON, try comma-separated
            return explode(',', $value);
        }
        return $value;
    }

    /**
     * Set the inclusions attribute with proper JSON encoding
     */
    public function setInclusionsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['inclusions'] = json_encode($value);
        } else {
            $this->attributes['inclusions'] = $value;
        }
    }

    /**
     * Get the requirements attribute with proper JSON handling
     */
    public function getRequirementsAttribute($value)
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            // If not valid JSON, try comma-separated
            return explode(',', $value);
        }
        return $value;
    }

    /**
     * Set the requirements attribute with proper JSON encoding
     */
    public function setRequirementsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['requirements'] = json_encode($value);
        } else {
            $this->attributes['requirements'] = $value;
        }
    }

    /**
     * Get the boat information attribute with proper JSON handling
     */
    public function getBoatInformationAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }

        $configurations = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($configurations) || empty($configurations)) {
            return [];
        }

        $configIds = collect($configurations)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $definitions = GuidingBoatDescription::whereIn('id', $configIds)
            ->get()
            ->keyBy('id');

        return collect($configurations)
            ->filter(fn ($item) => is_array($item) && !empty($item['id']))
            ->map(function ($item) use ($definitions) {
                $id = (int) $item['id'];
                $definition = $definitions->get($id);

                return [
                    'id' => $id,
                    'value' => $item['value'] ?? null,
                    'name' => $definition?->name
                ];
            })
            ->values()
            ->all();

        // if (is_string($value)) {
        //     $decoded = json_decode($value, true);
        //     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        //         return $decoded;
        //     }
        //     // If not valid JSON, try comma-separated
        //     return explode(',', $value);
        // }
        // return $value;
    }

    /**
     * Set the boat information attribute with proper JSON encoding
     */
    public function setBoatInformationAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['boat_information'] = json_encode($value);
        } else {
            $this->attributes['boat_information'] = $value;
        }
    }

    protected $attributes = [
        'status' => 'active',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boatType(): BelongsTo
    {
        return $this->belongsTo(GuidingBoatType::class, 'boat_type', 'id');
    }
}