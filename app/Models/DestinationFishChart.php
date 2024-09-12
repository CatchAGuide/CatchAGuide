<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationFishChart extends Model
{

    use HasFactory;

    protected $fillable = [
        'destination_id',
        'fish',
        'jan',
        'feb',
        'mar',
        'apr',
        'may',
        'jun',
        'jul',
        'aug',
        'sep',
        'oct',
        'nov',
        'dec'
    ];

    /**
     * @return BelongsTo
     */
    public function category_country(): BelongsTo
    {
        return $this->belongsTo(Destination::class, 'destination_id');
    }

    public static function bg_color($value)
    {
        if ($value == 2) {
            return '#a7d08c';
        } elseif ($value == 3) {
            return '#558134';
        }

        return '#e1f0d9';
    }
}
