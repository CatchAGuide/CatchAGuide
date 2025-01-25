<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $casts = [
        'translation' => 'array',
    ];

    protected $fillable = [
        'city',
        'country',
        'translation',
    ];

    public function getTranslationAttribute($value)
    {
        return json_decode($value, true);
    }

    public function scopeSearchTranslation($query, $searchString)
    {
        return $query->whereRaw('JSON_SEARCH(translation, "one", ?) IS NOT NULL', [$searchString]);
    }
}