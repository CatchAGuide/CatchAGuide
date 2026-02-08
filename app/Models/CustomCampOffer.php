<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomCampOffer extends Model
{
    use HasFactory;

    protected $table = 'custom_camp_offers';

    protected $fillable = [
        'name',
        'recipient_type',
        'customer_id',
        'recipient_email',
        'recipient_name',
        'recipient_phone',
        'camp_ids',
        'accommodation_ids',
        'boat_ids',
        'guiding_ids',
        'date_from',
        'date_to',
        'number_of_persons',
        'price',
        'additional_info',
        'free_text',
        'offers',
        'locale',
        'created_by',
        'sent_at',
        'status',
    ];

    /** Status values for follow-up and accepting plan requests. */
    public const STATUS_SENT = 'sent';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FOLLOW_UP = 'follow_up';

    protected $casts = [
        'camp_ids' => 'array',
        'accommodation_ids' => 'array',
        'boat_ids' => 'array',
        'guiding_ids' => 'array',
        'offers' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Get resolved offers with camp, accommodations, boats, guidings loaded from offers JSON.
     */
    public function getResolvedOffersAttribute(): array
    {
        $offers = $this->offers ?? [];
        if (empty($offers)) {
            return [];
        }
        $resolved = [];
        foreach ($offers as $o) {
            $camp = !empty($o['camp_id']) ? Camp::find($o['camp_id']) : null;
            $accIds = $o['accommodation_ids'] ?? [];
            $accIds = is_array($accIds) ? $accIds : (array) $accIds;
            $accommodations = !empty($accIds) ? Accommodation::whereIn('id', array_map('intval', $accIds))->get() : collect();
            $boatIds = $o['boat_ids'] ?? [];
            $boatIds = is_array($boatIds) ? $boatIds : (array) $boatIds;
            $boats = !empty($boatIds) ? RentalBoat::whereIn('id', array_map('intval', $boatIds))->get() : collect();
            $guideIds = $o['guiding_ids'] ?? [];
            $guideIds = is_array($guideIds) ? $guideIds : (array) $guideIds;
            $guidings = !empty($guideIds) ? Guiding::whereIn('id', array_map('intval', $guideIds))->get() : collect();
            $resolved[] = [
                'camp' => $camp,
                'accommodations' => $accommodations,
                'boats' => $boats,
                'guidings' => $guidings,
                'date_from' => $o['date_from'] ?? null,
                'date_to' => $o['date_to'] ?? null,
                'number_of_persons' => $o['number_of_persons'] ?? null,
                'price' => $o['price'] ?? null,
                'additional_info' => $o['additional_info'] ?? null,
            ];
        }
        return $resolved;
    }

    /**
     * Get the customer (if recipient_type is 'customer').
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the first camp (for backward compatibility).
     */
    public function camp()
    {
        $ids = $this->camp_ids ?? [];
        if (empty($ids)) {
            return null;
        }
        return Camp::find(is_array($ids) ? $ids[0] : $ids);
    }

    /**
     * Get the admin user who created this offer.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get accommodations for this offer (accessor).
     */
    public function getAccommodationsAttribute()
    {
        if (!$this->accommodation_ids || empty($this->accommodation_ids)) {
            return collect();
        }
        return Accommodation::whereIn('id', $this->accommodation_ids)->get();
    }

    /**
     * Get rental boats for this offer (accessor).
     */
    public function getRentalBoatsAttribute()
    {
        if (!$this->boat_ids || empty($this->boat_ids)) {
            return collect();
        }
        return RentalBoat::whereIn('id', $this->boat_ids)->get();
    }

    /**
     * Get guidings for this offer (accessor).
     */
    public function getGuidingsAttribute()
    {
        if (!$this->guiding_ids || empty($this->guiding_ids)) {
            return collect();
        }
        return Guiding::whereIn('id', $this->guiding_ids)->get();
    }
}
