<?php

namespace App\Models;

use Auth;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'password',
        'is_active',
        'is_guide',
        'user_information_id',
        'bar_allowed',
        'banktransfer_allowed',
        'paypal_allowed',
        'banktransferdetails',
        'paypaldetails',
        'merchant_id',
        'language',
        'taxId',
        'is_temp_password',
        'profil_image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function information(): BelongsTo
    {
        return $this->belongsTo(UserInformation::class, 'user_information_id');
    }

    /**
     * @return mixed
     */
    public function chats(): mixed
    {
        return Chat::where('user_id', $this->id)->orWhere('user_two_id', $this->id)->orderByDesc('created_at')->orderByDesc('last_message_at')->get();
    }

    /**
     * @return HasMany
     */
    public function chat_messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);

    }

    public function countunreadmessages()
    {
        $count = 0;
        foreach($this->chats() as $chat) {
            $messages = ChatMessage::where('read_at', NULL)->where('user_id', '!=', Auth::user()->id)->where('chat_id', $chat->id)->get();
            $count += count($messages);
        }
        return $count;
    }

    /**
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * @return HasMany
     */
    public function given_ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function received_ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'guide_id')->take(4);
    }

    public function hasratet($guideid)
    {
        $rating = Rating::where('user_id', auth::user()->id)->where('guide_id', $guideid)->first();
        if($rating){
            return true;
        }
        return false;
    }


    public function average_rating()
    {
        $reviews = Review::where('guide_id', $this->id)->with('booking', 'booking.user')->get();
        $reviews_count = $reviews->count();

        // Calculate average scores
        $average_overall_score = $reviews_count > 0 ? $reviews->avg('overall_score') : 0;
        $average_guide_score = $reviews_count > 0 ? $reviews->avg('guide_score') : 0;
        $average_region_water_score = $reviews_count > 0 ? $reviews->avg('region_water_score') : 0;
        $average_grandtotal_score = $reviews_count > 0 ? $reviews->avg('grandtotal_score') : 0;

        // $count = 0;
        // $amount = 0;
        // $return = 0;
        // if(count($this->received_ratings) > 0) {
        //     foreach($this->received_ratings as $rating) {
        //         $amount += $rating->rating;
        //         $count++;
        //     }
        //     $return = $amount / $count;
        // }


        return $average_grandtotal_score;
    }

    public function reviews(){
        return $this->hasMany(Review::class,'guide_id','id');
    }

    /**
     * @return HasMany
     */
    public function guidings(): HasMany
    {
        return $this->hasMany(Guiding::class);
    }

    /**
     * @return HasMany
     */
    public function blocked_events(): HasMany
    {
        return $this->hasMany(BlockedEvent::class);
    }

    /**
     * @return HasMany
     */
    public function wishlist_items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function isWishItem($id)
    {
        return $this->wishlist_items()->where('guiding_id', $id)->count() > 0;
    }

    /**
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function ifuserisblocked($day)
    {
        $day = date('Y-m-d H:i:s', strtotime($day));
        $blockings = BlockedEvent::whereDate('from', '<=' , $day)->whereDate('due', '>=' , $day)->where('user_id', $this->id)->get();

        if(count($blockings) > 0) {
            return false;
        }
        return true;
    }
}
