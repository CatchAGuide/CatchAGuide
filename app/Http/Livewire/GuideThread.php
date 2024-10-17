<?php

namespace App\Http\Livewire;

use App\Models\Guiding;
use Livewire\Component;
use Livewire\WithPagination;

class GuideThread extends Component
{
    use WithPagination;

    public $filt;
    public $perPage = 5;

    protected $listeners = ['load_more'];

    public function render()
    {
        $filter = json_decode($this->filt);
        $query = Guiding::query();

        if (isset($filter->country)) {
            $query->where('country', $filter->country);
        }

        if (isset($filter->target_fish)) {
            $query->whereHas('guidingTargets', function ($query) use ($filter) {
                $query->whereIn('target_id', $filter->target_fish);
            });
        }

        if (isset($filter->methods)) {
            $query->whereHas('guidingMethods', function ($query) use ($filter) {
                $query->whereIn('method_id', $filter->target_fish);
            });
        }

        $guidings = $query->paginate($this->perPage);



        return view('livewire.guide-thread', [
            'guidings' => $guidings,
        ]);
    }

    public function loadMore()
    {
        $this->perPage = $this->perPage + 5;
    }
}
