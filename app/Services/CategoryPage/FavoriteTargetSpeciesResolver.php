<?php

namespace App\Services\CategoryPage;

use App\Models\CategoryPage;
use App\Models\Target;
use Illuminate\Support\Collection;

class FavoriteTargetSpeciesResolver
{
    public function __construct(
        private CategoryListingThumbnailFallback $thumbnails,
    ) {}

    /**
     * Favorite-first list of "Targets" category pages, topped up with the next
     * alphabetical pages if there aren't enough favorites.
     *
     * @param  list<int>|null  $allowedSourceIds  When set, only pages whose source
     *                                            is in this list are considered.
     * @return Collection<int, array{name: string, slug: string, thumbnail: ?string, source_id: int}>
     */
    public function resolve(int $limit, ?array $allowedSourceIds = null): Collection
    {
        if ($allowedSourceIds !== null && $allowedSourceIds === []) {
            return collect();
        }

        $sourceIds = $allowedSourceIds === null ? null : array_map('strval', $allowedSourceIds);

        $favorites = CategoryPage::query()
            ->where('type', 'Targets')
            ->where('is_favorite', 1)
            ->when($sourceIds !== null, fn ($query) => $query->whereIn('source_id', $sourceIds))
            ->orderBy('name')
            ->limit($limit)
            ->get();

        $pages = $favorites;

        if ($pages->count() < $limit) {
            $extra = CategoryPage::query()
                ->where('type', 'Targets')
                ->when($sourceIds !== null, fn ($query) => $query->whereIn('source_id', $sourceIds))
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
                    'thumbnail' => $this->thumbnails->url(
                        $page->thumbnail_path,
                        CategoryListingThumbnailFallback::KIND_TARGET,
                        (int) $page->source_id,
                    ),
                'source_id' => (int) $page->source_id,
            ];
        })->values();
    }
}
