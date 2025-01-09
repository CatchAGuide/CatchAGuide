<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VacationBoat extends Model
{
    use HasFactory;

    protected $fillable = ['vacation_id', 'title', 'description', 'capacity', 'price', 'dynamic_fields'];

    public function vacation(): BelongsTo
    {
        return $this->belongsTo(Vacation::class);
    }
}
