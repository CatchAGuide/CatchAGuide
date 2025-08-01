<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ICalFeed extends Model
{
    use HasFactory;

    protected $table = 'ical_feeds';

    protected $fillable = [
        'user_id',
        'name',
        'feed_url',
        'sync_type',
        'is_active',
        'last_sync_at',
        'last_successful_sync_at',
        'sync_frequency_hours',
        'sync_settings',
        'last_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_successful_sync_at' => 'datetime',
        'sync_settings' => 'array',
    ];

    /**
     * Get the user that owns the iCal feed
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the feed needs to be synced based on frequency
     */
    public function needsSync(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->last_sync_at) {
            return true;
        }

        $nextSyncTime = $this->last_sync_at->addHours($this->sync_frequency_hours);
        return Carbon::now()->gte($nextSyncTime);
    }

    /**
     * Update last sync timestamp
     */
    public function updateLastSync(bool $successful = true): void
    {
        $this->last_sync_at = Carbon::now();
        
        if ($successful) {
            $this->last_successful_sync_at = Carbon::now();
            $this->last_error = null;
        }
        
        $this->save();
    }

    /**
     * Set sync error
     */
    public function setError(string $error): void
    {
        $this->last_error = $error;
        $this->last_sync_at = Carbon::now();
        $this->save();
    }

    /**
     * Scope for active feeds
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for feeds that need syncing
     */
    public function scopeNeedsSync($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('last_sync_at')
                  ->orWhere('last_sync_at', '<', Carbon::now()->subHours($this->sync_frequency_hours));
            });
    }

    /**
     * Get sync type display name
     */
    public function getSyncTypeDisplayAttribute(): string
    {
        return match($this->sync_type) {
            'bookings_only' => 'Bookings Only',
            'all_events' => 'All Events',
            default => 'Unknown'
        };
    }

    /**
     * Get status display
     */
    public function getStatusDisplayAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if (!$this->last_sync_at) {
            return 'Never Synced';
        }

        if ($this->last_error) {
            return 'Error';
        }

        return 'Active';
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status_display) {
            'Inactive' => 'secondary',
            'Never Synced' => 'warning',
            'Error' => 'danger',
            'Active' => 'success',
            default => 'info'
        };
    }
}
