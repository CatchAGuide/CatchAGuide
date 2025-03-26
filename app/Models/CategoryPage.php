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

    public function faq($languageCode = null)
    {
        $relation = $this->hasMany(Faq::class, 'source_id', 'id');
        
        if ($languageCode) {
            return $relation->where('language', $languageCode)->get();
        }
        
        return $relation;
    }   

    public function getThumbnailPath()
    {
        if (empty($this->thumbnail_path)) {
            return asset('assets/images/300x300.png');
        }

        $thumbnail_path = \Str::replace('public', 'storage', $this->thumbnail_path);

        return '/' . $thumbnail_path;
    }

    public function getSourceAttribute()
    {
        if (empty($this->type)) {
            return null;
        }
        
        // Map types to model classes
        $typeToModel = [
            'Targets' => Target::class,
            // Add other mappings as needed
        ];
        
        $modelClass = $typeToModel[$this->type] ?? null;
        
        if (!$modelClass) {
            return null;
        }
        
        // Manually fetch the related model
        return $modelClass::find($this->source_id);
    }
}
