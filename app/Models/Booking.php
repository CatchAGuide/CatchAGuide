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
        if($this->blocked_event){
            if($this->blocked_event->from < Carbon::now())
            {
                return true;
            }
        }
      
    }

    public function getGuideSalary(): float
    {
        return $this->price - $this->cag_percent;
    }

    public function sendBookingConfirmationMail($phoneFromUser)
    {
        Mail::send(new BookingConfirmationMail($this->guiding, $this->guiding->user, $this->user, $this, $phoneFromUser));
    }

    public function sendBookingConfirmationMailGuest($phoneFromUser)
    {
        Mail::send(new BookingConfirmationMailGuest($this, $this->guiding, $this->guiding->user, $this->user, $phoneFromUser));
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
}
