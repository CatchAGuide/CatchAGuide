<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelImage extends Model
{
    use HasFactory;
      protected $fillable = [
        'model_id',
        'model_type',
        'image_name',
        'image_size',
        'image_url',
        'image_exists'
    ];
}
