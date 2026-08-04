<?php

namespace App\Http\Controllers;

use App\Enums\GuideStatus;
use App\Models\GuideRequest;
use App\Models\User;

class GuideRequestsController extends Controller
{
    public function index()
    {
        $pendingRequests = GuideRequest::with(['user.information'])
            ->where('decision', 'pending')
            ->orderBy('submitted_at')
            ->get();

        // Pending is guide_status only; is_guide 0/null both mean normal user.
        $legacyPending = User::query()
            ->where('guide_status', GuideStatus::PENDING)
            ->whereDoesntHave('guideRequests', fn ($q) => $q->where('decision', 'pending'))
            ->with('information')
            ->get();

        return view('admin.pages.guide-requests.index', compact('pendingRequests', 'legacyPending'));
    }
}
