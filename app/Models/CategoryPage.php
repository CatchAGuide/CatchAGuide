<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id', 
        'type', 
        'name', 
        'slug', 
        'thumbnail_path'
    ];

    public function language($languageCode = null)
    {
        $relation = $this->hasMany(Language::class, 'source_id', 'id');
        
        if ($languageCode) {
            return $relation->where('language', $languageCode)->first();
        }
        
        return $relation;
    }

    public function languageEntries($languageCode)
    {
        return $this->hasMany(Language::class, 'source_id', 'id')
                    ->where('language', $languageCode)
                    ->get();
    }

    public function source()
    {
        $type = $this->type;
        $modelClass = 'App\\Models\\' . ucfirst(str_replace('-', '', $type));
        return $this->belongsTo($modelClass, 'source_id');
    }

    public function getThumbnailPath()
    {
        if (empty($this->thumbnail_path)) {
            return asset('assets/images/300x300.png');
        }

        $thumbnail_path = \Str::replace('public', 'storage', $this->thumbnail_path);

        return '/' . $thumbnail_path;
    }
}
