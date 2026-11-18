<?php

namespace App\Http\Livewire\Guide;

use App\Enums\GuideType;
use App\Services\Guide\Area\GuideOptimizationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Guide Area › To-dos (/guide/todos): the optimization score and every open check with a
 * "Fix" link (spec 6). Always computed fresh here, so the score updates after a fix.
 */
#[Layout('layouts.guide-area')]
class Todos extends Component
{
    public function render(GuideOptimizationService $optimization)
    {
        return view('livewire.guide.todos', [
            'result' => $optimization->evaluate(auth()->user(), fresh: true),
            'isCompany' => auth()->user()->guide_type === GuideType::COMPANY,
        ])->title(__('guide_area.todos.title'));
    }
}
