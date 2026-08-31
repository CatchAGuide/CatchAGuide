<?php

namespace App\Services\CategoryPage;

use App\Models\CategoryPage;
use App\Models\Target;
use Illuminate\Support\Collection;

class FavoriteTargetSpeciesResolver
{
    /**
     * Favorite-first list of "Targets" category pages, topped up with the next
     * alphabetical pages if there aren't enough favorites.
     *
     * @return Collection<int, array{name: string, slug: string, thumbnail: ?string, source_id: int}>
     */
    public function resolve(int $limit): Collection
    {
        $favorites = CategoryPage::query()
            ->where('type', 'Targets')
            ->where('is_favorite', 1)
            ->orderBy('name')
            ->limit($limit)
            ->get();

        $pages = $favorites;

        if ($pages->count() < $limit) {
            $extra = CategoryPage::query()
                ->where('type', 'Targets')
                ->whereNotIn('id', $pages->pluck('id'))
                ->orderBy('name')
                ->limit($limit - $pages->count())
                ->get();

            $pages = $pages->concat($extra);
        }

        $targets = Target::query()
            ->whereIn('id', $pages->pluck('source_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        return $pages->map(function (CategoryPage $page) use ($targets) {
            $target = $targets->get($page->source_id);

            return [
                'name' => $target?->name ?? $page->name,
                'slug' => $page->slug,
                'thumbnail' => $page->getThumbnailPath(),
                'source_id' => (int) $page->source_id,
            ];
        })->values();
    }
}
