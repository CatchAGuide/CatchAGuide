<?php

namespace App\Services\Sales;

use App\Models\SalesDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Admin list of sales documents (spec §4.8): filters by status, creator and travel period,
 * search by name, email, number or listing ID.
 */
class SalesDocumentListQuery
{
    public const PER_PAGE = 25;

    /**
     * @param  array{status?: ?string, creator?: ?string, from?: ?string, to?: ?string, q?: ?string}  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)
            ->with(['creator:id,name', 'cards:id,document_id,item_type,title_snapshot,parent_item_id,sort_order'])
            // Raw: Eloquent has no expression ordering; unseen customer acceptances come first.
            ->orderByRaw('accepted_at IS NOT NULL AND acceptance_seen_at IS NULL DESC')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(array $filters): Builder
    {
        $query = SalesDocument::query();

        if (filled($filters['status'] ?? null)) {
            $query->where('status', $filters['status']);
        }

        if (filled($filters['creator'] ?? null)) {
            $query->where('created_by', (int) $filters['creator']);
        }

        // Documents whose travel period overlaps the filter range.
        if ($from = SalesFormat::parse($filters['from'] ?? null)) {
            $query->where(fn (Builder $q) => $q->where('travel_to', '>=', $from->toDateString())->orWhere(fn (Builder $q) => $q->whereNull('travel_to')->where('travel_from', '>=', $from->toDateString())));
        }
        if ($to = SalesFormat::parse($filters['to'] ?? null)) {
            $query->where('travel_from', '<=', $to->toDateString());
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $listingId = ltrim($search, '#');
            $query->where(function (Builder $q) use ($search, $listingId) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';
                $q->where('number', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhereRaw("CONCAT_WS(' ', first_name, last_name) LIKE ?", [$like]);

                if (ctype_digit($listingId)) {
                    $q->orWhereHas('items', fn (Builder $items) => $items->where('listing_id', (int) $listingId));
                }
            });
        }

        return $query;
    }
}
