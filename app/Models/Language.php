<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;
    protected $fillable = [
        'source_id', 
        'language', 
        'title', 
        'sub_title', 
        'introduction', 
        'content', 
        'faq_title'
    ];

    public function categoryPage()
    {
        return $this->belongsTo(CategoryPage::class, 'source_id', 'id');
    }
    
    public function language($languageCode = null)
    {
        // If no language code is provided, return the current language
        if (!$languageCode) {
            return $this;
        }
        
        // Otherwise, find the language record with the same source_id and the specified language code
        return self::where('source_id', $this->source_id)
                  ->where('language', $languageCode)
                  ->first();
    }
}
