<?php

namespace App\Http\Livewire\Customer;

use App\Services\Customer\CustomerFavouriteService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Customer area › Favourites (/profile/favoriteguides): the tours the user saved with the
 * heart on a tour page, with a Remove button.
 */
class Favourites extends Component
{
    use WithPagination;

    public function remove(int $guidingId, CustomerFavouriteService $favourites): void
    {
        if ($favourites->remove(auth()->user(), $guidingId)) {
            $this->dispatch('guide-area-toast', message: __('guide_area.customer.favourites.removed'));
        }
    }

    public function render(CustomerFavouriteService $favourites)
    {
        return view('livewire.customer.favourites', [
            'items' => $favourites->paginate(auth()->user()),
        ]);
    }
}
