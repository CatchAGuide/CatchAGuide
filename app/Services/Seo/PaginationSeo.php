<?php

namespace App\Services\Seo;

use Illuminate\Http\Request;

/**
 * Paginated listing pages (?page=N) are distinct pages: page 2+ canonicalizes to itself (Google:
 * don't point page 2+ at page 1, or the listings only reachable there go undiscovered) and gets a
 * "Page N" title suffix so the sequence doesn't share one title. ?page=1 canonicalizes to the
 * clean URL. Every other query parameter is still stripped from the canonical.
 */
class PaginationSeo
{
    public function currentPage(Request $request): int
    {
        $page = $request->query('page');

        return is_scalar($page) && ctype_digit((string) $page) && (int) $page > 1 ? (int) $page : 1;
    }

    public function canonicalUrl(Request $request, ?string $baseUrl = null): string
    {
        $baseUrl ??= $request->url();
        $page = $this->currentPage($request);

        return $page > 1 ? $baseUrl.'?page='.$page : $baseUrl;
    }

    public function titleSuffix(Request $request): string
    {
        $page = $this->currentPage($request);

        return $page > 1 ? ' – '.__('message.page_n', ['page' => $page]) : '';
    }
}
