<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarSchedule extends Model
{
    use HasFactory;
    
    protected $table = 'calendar_schedule';

    protected $fillable = [
        'type',
        'date',
        'note',
        'guiding_id',
        'vacation_id',
        'user_id',
        'booking_id',
    ];

    public function guiding()
    {
        return $this->belongsTo(Guiding::class);
    }

    public function vacation()
    {
        return $this->belongsTo(Vacation::class);
    }

    public function user()
    {
        // If this schedule has a booking, use the booking's user relationship
        if ($this->booking) {
            return $this->booking->user();
        }
        
        // Otherwise, default to regular user relationship
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    
    /**
     * Get the booking status for calendar display
     */
    public function getBookingStatus()
    {
        if ($this->booking) {
            return $this->booking->status;
        }
        
        // Map calendar schedule types to display status
        switch ($this->type) {
            case 'tour_request':
                return 'pending'; // Default for tour requests without booking
            case 'tour_schedule':
                return 'blocked';
            case 'vacation_schedule':
                return 'vacation';
            case 'vacation_request':
                return 'vacation_request';
            case 'custom_schedule':
                return 'custom';
            default:
                return 'unknown';
        }
    }
    
    /**
     * Get display title for the calendar event
     */
    public function getDisplayTitle()
    {
        if ($this->booking) {
            $user = $this->booking->user;
            $guestCount = $this->booking->count_of_users;
            $userName = $user ? $user->firstname . ' ' . $user->lastname : 'Guest';
            
            switch ($this->booking->status) {
                case 'accepted':
                    return $userName . " ({$guestCount} " . ($guestCount > 1 ? 'guests' : 'guest') . ")";
                case 'cancelled':
                    return 'Cancelled: ' . $userName;
                case 'rejected':
                    return 'Rejected: ' . $userName;
                case 'pending':
                    return 'Pending: ' . $userName . " ({$guestCount})";
                default:
                    return 'Booking: ' . $userName;
            }
        }
        
        switch ($this->type) {
            case 'tour_schedule':
                // Don't show individual blocked entries to reduce clutter
                return null;
            case 'vacation_schedule':
                return $this->note ?: 'Vacation';
            case 'vacation_request':
                return 'Vacation Request';
            case 'custom_schedule':
                return $this->note ?: 'Custom Event';
            default:
                return 'Event';
        }
    }
    
    /**
     * Get color for calendar event based on type and status
     */
    public function getEventColor()
    {
        if ($this->booking) {
            switch ($this->booking->status) {
                case 'accepted':
                    return '#28a745'; // Green for confirmed bookings
                case 'cancelled':
                    return '#6c757d'; // Gray for cancelled
                case 'rejected':
                    return '#dc3545'; // Red for rejected
                case 'pending':
                    return '#ffc107'; // Yellow for pending
                default:
                    return '#17a2b8'; // Teal for other bookings
            }
        }
        
        switch ($this->type) {
            case 'tour_schedule':
                return '#fd7e14'; // Orange for blocked dates
            case 'vacation_schedule':
                return '#6f42c1'; // Purple for vacation
            case 'vacation_request':
                return '#e83e8c'; // Pink for vacation requests
            case 'custom_schedule':
                return '#20c997'; // Teal for custom events
            default:
                return '#6c757d'; // Gray for unknown
        }
    }
    
    // Add virtual attributes for backward compatibility
    public function getFromAttribute()
    {
        return $this->date;
    }
    
    public function getDueAttribute()
    {
        return $this->date;
    }
}