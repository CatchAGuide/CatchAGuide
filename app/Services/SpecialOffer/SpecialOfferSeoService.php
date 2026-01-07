<?php

namespace App\Services\SpecialOffer;

use App\Models\SpecialOffer;
use Illuminate\Support\Str;

class SpecialOfferSeoService
{
    /**
     * Generate a unique slug for a special offer
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
        $query = SpecialOffer::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Update special offer slug if title changed
     */
    public function updateSlugIfNeeded(SpecialOffer $specialOffer, string $newTitle): ?string
    {
        if ($specialOffer->title !== $newTitle && $newTitle) {
            return $this->generateSlug($newTitle, $specialOffer->id);
        }

        return null;
    }

    /**
     * Generate meta description from special offer data
     */
    public function generateMetaDescription(SpecialOffer $specialOffer): string
    {
        $description = '';
        
        if ($specialOffer->whats_included && is_array($specialOffer->whats_included) && !empty($specialOffer->whats_included)) {
            $included = is_array($specialOffer->whats_included) 
                ? implode(', ', array_slice($specialOffer->whats_included, 0, 3))
                : Str::limit($specialOffer->whats_included, 155);
            $description = "Special offer: {$specialOffer->title} in {$specialOffer->location}. Includes: {$included}.";
        } else {
            $description = "Discover amazing special offer: {$specialOffer->title} in {$specialOffer->location}.";
        }

        return Str::limit($description, 155);
    }

    /**
     * Generate meta keywords from special offer data
     */
    public function generateMetaKeywords(SpecialOffer $specialOffer): string
    {
        $keywords = [];
        
        // Add location-based keywords
        if ($specialOffer->location) $keywords[] = $specialOffer->location;
        if ($specialOffer->city) $keywords[] = $specialOffer->city;
        if ($specialOffer->region) $keywords[] = $specialOffer->region;
        if ($specialOffer->country) $keywords[] = $specialOffer->country;
        
        // Add whats included keywords
        if ($specialOffer->whats_included && is_array($specialOffer->whats_included)) {
            foreach ($specialOffer->whats_included as $item) {
                if (is_string($item)) {
                    $keywords[] = trim($item);
                }
            }
        }
        
        // Add general keywords
        $keywords[] = 'special offer';
        $keywords[] = 'fishing package';
        $keywords[] = 'fishing deal';
        
        return implode(', ', array_unique($keywords));
    }
}

