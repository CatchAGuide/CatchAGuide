<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Cacheable;

class Method extends Model
{
    use HasFactory, Cacheable;

    public function guidings()
    {
        return $this->belongsToMany(Guiding::class, 'guiding_method')->withTimestamps();;
    }

    public function getNameAttribute()
    {        
        return app()->getLocale() == 'en' ? $this->attributes['name_en'] : $this->attributes['name'];
    }

    public function categoryPage()
    {
        return $this->hasOne(CategoryPage::class, 'source_id')->where('type', 'Methods');
    }
}
