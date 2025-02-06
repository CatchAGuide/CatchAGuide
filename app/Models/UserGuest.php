<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGuest extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'salutation',
        'title',
        'firstname',
        'lastname',
        'address',
        'postal',
        'city',
        'country',
        'phone',
        'email',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'user_id')->where('is_guest', true);
    }
}
