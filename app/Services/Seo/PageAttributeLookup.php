<?php

namespace App\Services\Seo;

use App\Models\PageAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-managed per-URL meta (page_attributes: title/description/keywords rows keyed by domain +
 * path). Cached per host+path — never in a process-wide static, which served one URL's rows for
 * every later request in a long-running process.
 */
class PageAttributeLookup
{
    private const CACHE_HOURS = 24;

    /** @var array<string, Collection<int, PageAttribute>> */
    private array $memo = [];

    /**
     * @return Collection<int, PageAttribute>
     */
    public function forRequest(Request $request): Collection
    {
        $host = $request->getHost();
        $path = $request->path();
        $key = "page_attributes_{$host}_{$path}";

        return $this->memo[$key] ??= Cache::remember($key, now()->addHours(self::CACHE_HOURS), fn () => PageAttribute::query()
            ->where('domain', $host)
            ->where('uri', $path)
            ->get());
    }
}
