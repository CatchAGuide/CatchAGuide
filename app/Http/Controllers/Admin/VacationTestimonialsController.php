<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVacationTestimonialRequest;
use App\Http\Requests\Admin\UpdateVacationTestimonialRequest;
use App\Models\VacationTestimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class VacationTestimonialsController extends Controller
{
    public function index(): View
    {
        $testimonials = VacationTestimonial::query()
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        return view('admin.pages.vacation-testimonials.index', compact('testimonials'));
    }

    public function create(): View
    {
        return view('admin.pages.vacation-testimonials.form', [
            'testimonial' => null,
            'route' => route('admin.vacation-testimonials.store'),
            'method' => 'POST',
            'pageTitle' => __('admin.vacation_testimonials.create_title'),
        ]);
    }

    public function store(StoreVacationTestimonialRequest $request): RedirectResponse
    {
        VacationTestimonial::query()->create($request->validated());
        $this->forgetTestimonialCache();

        return redirect()
            ->route('admin.vacation-testimonials.index')
            ->with('success', __('admin.vacation_testimonials.created'));
    }

    public function edit(VacationTestimonial $vacationTestimonial): View
    {
        return view('admin.pages.vacation-testimonials.form', [
            'testimonial' => $vacationTestimonial,
            'route' => route('admin.vacation-testimonials.update', $vacationTestimonial),
            'method' => 'PUT',
            'pageTitle' => __('admin.vacation_testimonials.edit_title'),
        ]);
    }

    public function update(UpdateVacationTestimonialRequest $request, VacationTestimonial $vacationTestimonial): RedirectResponse
    {
        $vacationTestimonial->update($request->validated());
        $this->forgetTestimonialCache();

        return redirect()
            ->route('admin.vacation-testimonials.index')
            ->with('success', __('admin.vacation_testimonials.updated'));
    }

    public function destroy(VacationTestimonial $vacationTestimonial): RedirectResponse
    {
        $vacationTestimonial->delete();
        $this->forgetTestimonialCache();

        return redirect()
            ->route('admin.vacation-testimonials.index')
            ->with('success', __('admin.vacation_testimonials.deleted'));
    }

    private function forgetTestimonialCache(): void
    {
        $limit = (int) config('vacations.hub_testimonials_limit', 6);

        foreach (['en', 'de'] as $locale) {
            Cache::forget("vacation_testimonials_latest_v1_{$limit}_{$locale}");
        }
    }
}
