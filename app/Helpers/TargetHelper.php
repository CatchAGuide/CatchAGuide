<?php

namespace App\Helpers;

use App\Models\Target;
use Config;

class TargetHelper
{
    /**
     * Get all targets formatted based on locale
     * 
     * @param bool $idAsKey Whether to use the target ID as the array key
     * @return array
     */
    public static function getAllTargets($idAsKey = false)
    {
        $locale = Config::get('app.locale');
        $targets = Target::all();

        return $targets->map(function ($target) use ($locale, $idAsKey) {
            $data = [
                'id' => $target->id,
                'name' => $locale == 'en' ? $target->name_en : $target->name,
                'name_en' => $target->name_en,
                'name_de' => $target->name
            ];

            return $idAsKey ? [$target->id => $data] : $data;
        })->when($idAsKey, function ($collection) {
            return $collection->collapse();
        })->toArray();
    }

    /**
     * Get target names by IDs
     * 
     * @param array $targetIds Array of target IDs
     * @return array
     */
    public static function getTargetNamesByIds(array $targetIds)
    {
        $locale = Config::get('app.locale');
        
        return Target::whereIn('id', $targetIds)
            ->get()
            ->map(function ($target) use ($locale) {
                return $locale == 'en' ? $target->name_en : $target->name;
            })
            ->toArray();
    }

    /**
     * Get a single target by ID
     * 
     * @param int $targetId
     * @return array|null
     */
    public static function getTargetById($targetId)
    {
        $locale = Config::get('app.locale');
        $target = Target::find($targetId);

        if (!$target) {
            return null;
        }

        return [
            'id' => $target->id,
            'name' => $locale == 'en' ? $target->name_en : $target->name,
            'name_en' => $target->name_en,
            'name_de' => $target->name
        ];
    }
} 