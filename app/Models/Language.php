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
}
