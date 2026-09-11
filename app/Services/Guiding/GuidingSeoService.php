<?php

namespace App\Services\Guiding;

use App\Models\Guiding;

class GuidingSeoService
{
    /**
     * Build a unique public slug from title + location (title-in-location).
     */
    public function generateSlug(string $title, string $location = '', ?int $excludeId = null): string
    {
        $base = $this->baseSlug($title, $location);
        $slug = $base;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Keep the current slug when it is present and unique; otherwise generate a new one.
     */
    public function ensureUniqueSlug(Guiding $guiding, ?string $title = null, ?string $location = null): string
    {
        $title = $title ?? (string) ($guiding->title ?: 'temp');
        $location = $location ?? (string) ($guiding->location ?: 'location');

        if (filled($guiding->slug) && ! $this->slugExists((string) $guiding->slug, $guiding->id)) {
            return (string) $guiding->slug;
        }

        return $this->generateSlug($title, $location, $guiding->id);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Guiding::query()->where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function baseSlug(string $title, string $location = ''): string
    {
        $location = trim($location) !== '' ? trim($location) : 'location';
        $title = trim($title) !== '' ? trim($title) : 'temp';

        return slugify($title . '-in-' . $location);
    }
}
