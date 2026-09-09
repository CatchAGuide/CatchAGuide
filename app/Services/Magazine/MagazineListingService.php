<?php

namespace App\Services\Magazine;

use App\Models\Category;
use App\Models\Thread;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MagazineListingService
{
    public const PER_PAGE = 12;

    /**
     * @return array{
     *     featured: ?Thread,
     *     threads: LengthAwarePaginator,
     *     categories: Collection,
     *     search: string,
     *     activeCategory: ?Category,
     *     totalCount: int
     * }
     */
    public function build(string $locale, ?string $search = null, ?Category $category = null): array
    {
        $search = trim((string) $search);
        $page = max(1, (int) request()->input('page', 1));
        $showFeatured = $search === '' && $category === null && $page === 1;

        $baseQuery = $this->baseQuery($locale, $search, $category);
        $totalCount = (clone $baseQuery)->count();

        $featured = null;
        if ($showFeatured) {
            $featured = (clone $baseQuery)->first();
        }

        $listQuery = $this->baseQuery($locale, $search, $category);
        if ($featured !== null) {
            $listQuery->where('id', '!=', $featured->id);
        }

        $threads = $listQuery
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return [
            'featured' => $featured,
            'threads' => $threads,
            'categories' => $this->categoriesWithCounts($locale),
            'search' => $search,
            'activeCategory' => $category,
            'totalCount' => $totalCount,
        ];
    }

    public function categoriesWithCounts(string $locale): Collection
    {
        return Category::query()
            ->withCount([
                'threads' => fn (Builder $query) => $query->where('language', $locale),
            ])
            ->having('threads_count', '>', 0)
            ->orderBy('name')
            ->get();
    }

    public function relatedThreads(Thread $thread, string $locale, int $limit = 6): Collection
    {
        $sameCategory = Thread::query()
            ->with('category')
            ->where('language', $locale)
            ->where('id', '!=', $thread->id)
            ->when($thread->category_id, fn (Builder $q) => $q->where('category_id', $thread->category_id))
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($sameCategory->count() >= $limit) {
            return $sameCategory;
        }

        $excludeIds = $sameCategory->pluck('id')->push($thread->id)->all();

        $fillers = Thread::query()
            ->with('category')
            ->where('language', $locale)
            ->whereNotIn('id', $excludeIds)
            ->orderByDesc('id')
            ->limit($limit - $sameCategory->count())
            ->get();

        return $sameCategory->concat($fillers)->values();
    }

    private function baseQuery(string $locale, string $search, ?Category $category): Builder
    {
        return Thread::query()
            ->with('category')
            ->where('language', $locale)
            ->when($category !== null, fn (Builder $q) => $q->where('category_id', $category->id))
            ->when($search !== '', function (Builder $q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function (Builder $inner) use ($like) {
                    $inner->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like)
                        ->orWhere('author', 'like', $like);
                });
            })
            ->orderByDesc('id');
    }
}
