<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserInformation extends Model
{
    protected $table = 'user_information';

    protected $fillable = [
        'birthday',
        'phone',
        'phone_country_code',
        'address',
        'address_number',
        'postal',
        'city',
        'country',
        'about_me',
        'languages',
        'favorite_fish',
        'fishing_start_year',
        'request_as_guide',
        'user_id',
        'company_name',
        'legal_form',
        'founded_year',
        'contact_position',
        'trade_register_number',
        'trade_register_court',
        'tax_number',
        'company_profile',
    ];

    protected $casts = [
        'birthday' => 'date',
        'company_profile' => 'array',
    ];

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
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
