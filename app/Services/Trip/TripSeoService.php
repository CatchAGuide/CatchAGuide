<?php

namespace App\Services\Trip;

use App\Models\Trip;
use Illuminate\Support\Str;

class TripSeoService
{
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

    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Trip::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function updateSlugIfNeeded(Trip $trip, string $newTitle): ?string
    {
        if ($trip->title !== $newTitle && $newTitle) {
            return $this->generateSlug($newTitle, $trip->id);
        }

        return null;
    }

    public function generateMetaDescription(Trip $trip): string
    {
        if ($trip->description) {
            return Str::limit(strip_tags($trip->description), 155);
        }

        if ($trip->location) {
            return "Discover an all-inclusive fishing trip at {$trip->title} in {$trip->location}.";
        }

        return "Discover an all-inclusive fishing trip at {$trip->title}.";
    }
}

