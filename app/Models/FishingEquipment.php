<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class FishingEquipment extends Model
{
    use HasFactory;

    public function guiding(): BelongsTo
    {
        return $this->belongsTo(Guiding::class);
    }
}
