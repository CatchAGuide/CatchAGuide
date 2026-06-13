<?php

namespace App\Http\Controllers\Vacation;

use App\Http\Controllers\Controller;
use App\Models\VacationInterestSignup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VacationInterestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'country' => 'required|string|max:100',
            'pillar' => 'required|in:camp,trip',
        ]);

        VacationInterestSignup::query()->create([
            'email' => $validated['email'],
            'country' => $validated['country'],
            'pillar' => $validated['pillar'],
            'locale' => app()->getLocale(),
        ]);

        return back()->with('vacation_interest_success', __('vacations.interest_success'));
    }
}
