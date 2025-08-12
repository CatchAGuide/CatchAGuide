<?php

namespace App\Models;

use App\Mail\BookingConfirmationMail;
use App\Mail\BookingConfirmationMailGuest;
use App\Mail\MailToCEO;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class Booking extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'price',
        'cag_percent',
        'count_of_users',
        'is_paid',
        'user_id',
        'guiding_id',
        'payment_id',
        'status',
        'blocked_event_id',
        'rating_id',
        'extras',
        'total_extra_price',
        'token',
        'book_date',
        'additional_information',
        'phone',
        'phone_country_code',
        'email',
        'last_employee_id',
        'expires_at',
        'is_guest',
        'created_at',
        'updated_at',
        'alternative_dates',
        'parent_id',
        'is_reviewed',
    ];
    
    public function user()
    {
        return $this->is_guest
            ? $this->belongsTo(UserGuest::class)
            : $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function guiding(): BelongsTo
    {
        return $this->belongsTo(Guiding::class);
    }

    /**
     * @return BelongsTo
     */
    public function blocked_event(): BelongsTo
    {
        return $this->belongsTo(BlockedEvent::class);
    }

    public function calendar_schedule(): BelongsTo
    {
        return $this->belongsTo(CalendarSchedule::class, 'blocked_event_id');
    }

    /**
     * @return BelongsTo
     */
    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function isBookingOver()
    {
        // Check calendar_schedule first (new system)
        if($this->calendar_schedule){
            return Carbon::parse($this->calendar_schedule->date)->addDay()->isPast();
        }
        
        // Fallback to blocked_event (old system)
        if($this->blocked_event){
            return Carbon::parse($this->blocked_event->from)->addDay()->isPast();
        }
        
        return false;
    }

    public function getGuideSalary(): float
    {
        return $this->price - $this->cag_percent;
    }

    public function getBookingDate()
    {
        if ($this->calendar_schedule) {
            return Carbon::parse($this->calendar_schedule->date);
        }
        
        if ($this->blocked_event) {
            return Carbon::parse($this->blocked_event->from);
        }
        
        if ($this->book_date) {
            return Carbon::parse($this->book_date);
        }
        
        return null;
    }

    public function getFormattedBookingDate($format = 'F j, Y')
    {
        $date = $this->getBookingDate();
        return $date ? $date->format($format) : null;
    }

    public function sendBookingConfirmationMail($phoneFromUser)
    {
        Mail::locale($this->guiding->user->language ?? app()->getLocale())->send(new BookingConfirmationMail($this->guiding, $this->guiding->user, $this->user, $this, $phoneFromUser));
    }

    public function sendBookingConfirmationMailGuest($phoneFromUser)
    {
        Mail::locale($this->user->language ?? app()->getLocale())->send(new BookingConfirmationMailGuest($this, $this->guiding, $this->guiding->user, $this->user, $phoneFromUser));
    }

    public function sendBookingMailToCEO()
    {
        Mail::send(new MailToCEO($this, $this->guiding, $this->guiding->user, $this->user));
    }

    public function getTotalExtraPrice(){
        $prices = unserialize($this->extras);

        $total = 0;
        if($this->extras){
            $prices = unserialize($this->extras);
            foreach($prices as $price){
            }
            
        }
    }

    public function employee(): BelongsTo{
        return $this->belongsTo(Employee::class, 'last_employee_id');
    }

    /**
     * Check if this booking can be reviewed by the current user
     * 
     * @return bool
     */
    public function canBeReviewed(): bool
    {
        // Check if booking is over
        if (!$this->isBookingOver()) {
            return false;
        }

        // Check if already marked as reviewed
        if ($this->is_reviewed) {
            return false;
        }

        // Check if a review already exists for this booking
        $existingReview = Review::where('booking_id', $this->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($existingReview) {
            return false;
        }

        // Check if user has already rated this guide for this booking using the old rating system
        $existingRating = Rating::where('user_id', auth()->id())
            ->where('guide_id', $this->guiding->user_id)
            ->exists();

        if ($existingRating) {
            return false;
        }

        return true;
    }

    /**
     * Check if this booking has been reviewed
     * 
     * @return bool
     */
    public function hasBeenReviewed(): bool
    {
        if ($this->is_reviewed) {
            return true;
        }

        // Also check if a review exists in the reviews table
        return Review::where('booking_id', $this->id)->exists();
    }

    /**
     * Get the full phone number with country code
     * 
     * @return string
     */
    public function getFullPhoneNumber(): string
    {
        if ($this->phone_country_code && $this->phone) {
            return $this->phone_country_code . ' ' . $this->phone;
        }
        
        return $this->phone ?? '';
    }
}
