<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserICalFeed extends Model
{
    use HasFactory;

    protected $table = 'user_ical_feeds';

    protected $fillable = [
        'user_id',
        'name',
        'feed_token',
        'otp_secret',
        'feed_type',
        'feed_settings',
        'is_active',
        'last_accessed_at',
        'access_count',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime',
        'feed_settings' => 'array',
    ];

    /**
     * Get the user that owns the iCal feed
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new feed token
     */
    public static function generateFeedToken(): string
    {
        return Str::random(64);
    }

    /**
     * Generate OTP secret
     */
    public static function generateOTPSecret(): string
    {
        return Str::random(32);
    }

    /**
     * Generate OTP for current time
     */
    public function generateOTP(): string
    {
        $timeSlice = floor(time() / 30); // 30-second window
        $hash = hash_hmac('sha1', $timeSlice, $this->otp_secret, true);
        $offset = ord($hash[19]) & 0xf;
        
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(string $otp): bool
    {
        $currentOTP = $this->generateOTP();
        $previousOTP = $this->generateOTPForTime(time() - 30);
        $nextOTP = $this->generateOTPForTime(time() + 30);
        
        return in_array($otp, [$currentOTP, $previousOTP, $nextOTP]);
    }

    /**
     * Generate OTP for specific time
     */
    private function generateOTPForTime(int $timestamp): string
    {
        $timeSlice = floor($timestamp / 30);
        $hash = hash_hmac('sha1', $timeSlice, $this->otp_secret, true);
        $offset = ord($hash[19]) & 0xf;
        
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get the public feed URL
     */
    public function getFeedUrl(): string
    {
        return url("/ical/feed/{$this->feed_token}");
    }

    /**
     * Get the secure feed URL with OTP
     */
    public function getSecureFeedUrl(): string
    {
        $otp = $this->generateOTP();
        return url("/ical/feed/{$this->feed_token}/{$otp}");
    }

    /**
     * Update access statistics
     */
    public function updateAccessStats(): void
    {
        $this->increment('access_count');
        $this->update(['last_accessed_at' => Carbon::now()]);
    }

    /**
     * Check if feed is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        
        return Carbon::now()->gt($this->expires_at);
    }

    /**
     * Check if feed is accessible
     */
    public function isAccessible(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Get feed type display name
     */
    public function getFeedTypeDisplayAttribute(): string
    {
        return match($this->feed_type) {
            'bookings_only' => 'Bookings Only',
            'all_events' => 'All Events',
            'custom_schedule' => 'Custom Schedule',
            default => 'Unknown'
        };
    }

    /**
     * Scope for active feeds
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', Carbon::now());
                    });
    }

    /**
     * Scope for feeds by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Find feed by token
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('feed_token', $token)->first();
    }
} 