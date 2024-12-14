<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Booking;

class BlockedEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'from',
        'due',
        'type',
        'user_id',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(){
        return $this->belongsTo(Booking::class,'id','blocked_event_id');
    }

    public function getBookingStatus()
    {
        // Check if a booking is associated with this BlockedEvent
        if ($this->booking) {
            if($this->booking->status){
                $status = $this->booking->status;
                if($status == 'accepted'){
                    return 'Booked';
                }
                if($status == 'storniert'){
                    return 'Cancelled';
                }

                return $status;
            }
        }
    }
}
