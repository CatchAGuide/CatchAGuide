<?php

namespace App\Services\Customer;

use App\Models\Guiding;
use App\Models\User;
use App\Models\WishlistItem;
use App\Presenters\Booking\CustomerBookingPresenter;
use App\Presenters\Offers\TourCardPresenter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * The user's saved tours (wishlist_items) for Customer area › Favourites and the Overview.
 * Only tours that still exist and are live are shown; the rows of deleted tours stay untouched.
 */
class CustomerFavouriteService
{
    public const PER_PAGE = 12;

    public function __construct(
        private readonly TourCardPresenter $cards,
        private readonly CustomerBookingPresenter $bookings,
    ) {}

    public function paginate(User $user, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $page = $this->query($user)->paginate($perPage);
        $page->setCollection($page->getCollection()->map(fn (WishlistItem $item) => $this->present($item->guiding)));

        return $page;
    }

    /** @return Collection<int, array<string, mixed>> */
    public function latest(User $user, int $limit): Collection
    {
        return $this->query($user)->limit($limit)->get()->map(fn (WishlistItem $item) => $this->present($item->guiding));
    }

    public function count(User $user): int
    {
        return $this->query($user)->count();
    }

    /** Removes a saved tour; true when there was one. */
    public function remove(User $user, int $guidingId): bool
    {
        return $user->wishlist_items()->where('guiding_id', $guidingId)->delete() > 0;
    }

    /** @return array<string, mixed> */
    private function present(Guiding $guiding): array
    {
        $price = (float) $guiding->getLowestPrice();

        return ['id' => (int) $guiding->id] + $this->cards->presentCheckoutSummary($guiding) + [
            'price_label' => $price > 0 ? $this->bookings->money($price) : null,
        ];
    }

    private function query(User $user): Builder
    {
        return WishlistItem::query()
            ->where('user_id', $user->id)
            ->whereHas('guiding', fn (Builder $q) => $q->where('status', 1))
            ->with('guiding')
            ->orderByDesc('id');
    }
}
