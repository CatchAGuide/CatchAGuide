<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_name',
        'user_id',
        'avatar'
    ];

    public function guiding()
    {
        return $this->belongsTo(Guiding::class);
    }
}
