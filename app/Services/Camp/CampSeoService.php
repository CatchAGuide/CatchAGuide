<?php

namespace App\Services\Camp;

use App\Models\Camp;
use Illuminate\Support\Str;

class CampSeoService
{
    /**
     * Generate a unique slug for a camp
     */
    public function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists
     */
    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Camp::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Update camp slug if title changed
     */
    public function updateSlugIfNeeded(Camp $camp, string $newTitle): ?string
    {
        if ($camp->title !== $newTitle && $newTitle) {
            return $this->generateSlug($newTitle, $camp->id);
        }

        return null;
    }

    /**
     * Generate meta description from camp data
     */
    public function generateMetaDescription(Camp $camp): string
    {
        $description = '';
        
        if ($camp->description_camp) {
            $description = Str::limit(strip_tags($camp->description_camp), 155);
        } elseif ($camp->description_area) {
            $description = Str::limit(strip_tags($camp->description_area), 155);
        } else {
            $description = "Discover amazing fishing opportunities at {$camp->title} in {$camp->location}.";
        }

        return $description;
    }

    /**
     * Generate meta keywords from camp data
     */
    public function generateMetaKeywords(Camp $camp): string
    {
        $keywords = [];
        
        // Add location-based keywords
        if ($camp->location) $keywords[] = $camp->location;
        if ($camp->city) $keywords[] = $camp->city;
        if ($camp->region) $keywords[] = $camp->region;
        if ($camp->country) $keywords[] = $camp->country;
        
        // Add fishing-related keywords
        if ($camp->target_fish) {
            $fishTypes = explode(',', $camp->target_fish);
            foreach ($fishTypes as $fish) {
                $keywords[] = trim($fish);
            }
        }
        
        // Add general fishing keywords
        $keywords[] = 'fishing camp';
        $keywords[] = 'fishing accommodation';
        $keywords[] = 'fishing trip';
        
        return implode(', ', array_unique($keywords));
    }
}
