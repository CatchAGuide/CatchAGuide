<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationFaq extends Model
{

    use HasFactory;

    protected $fillable = [
        'destination_id',
        'question',
        'answer',
        'language'
    ];

    /**
     * @return BelongsTo
     */
    public function category_country(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }
}