<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OAuthToken extends Model
{
    use HasFactory;
    
    protected $table = 'oauth_tokens';

    protected $fillable = [
        'user_id',
        'type',
        'access_token',
        'refresh_token',
        'token_type',
        'expires_at',
        'provider_user_id',
        'provider_data',
        'status',
        'last_sync_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'provider_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the token is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the token is active and not expired
     */
    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Update last sync timestamp
     */
    public function updateLastSync()
    {
        $this->update(['last_sync_at' => now()]);
    }

    /**
     * Scope to get tokens by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get token for a specific user and service
     */
    public static function getTokenForUser($userId, $type)
    {
        return static::where('user_id', $userId)
                    ->where('type', $type)
                    ->where('status', 'active')
                    ->first();
    }
}
 