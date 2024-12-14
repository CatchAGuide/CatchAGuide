<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtrasPrice extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'name_en'];
    protected $table = 'extras_prices';

    public function getNameAttribute()
    {
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }   
}
