<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuideRequest;
use App\Services\Guide\GuideVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuideRequestReviewController extends Controller
{
    public function approve(GuideRequest $guideRequest): RedirectResponse
    {
        app(GuideVerificationService::class)->approve($guideRequest, (int) auth()->id());

        return redirect()
            ->route('admin.guide-requests.index')
            ->with('message', 'Guide application approved.');
    }

    public function reject(Request $request, GuideRequest $guideRequest): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:2000',
            'internal_notes' => 'nullable|string|max:2000',
        ]);

        try {
            app(GuideVerificationService::class)->reject(
                $guideRequest,
                (int) auth()->id(),
                $request->input('rejection_reason'),
                $request->input('internal_notes')
            );
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('admin.guide-requests.index')
                ->withErrors(['general' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.guide-requests.index')
            ->with('message', 'Guide application rejected. The applicant has been notified by email.');
    }
}
