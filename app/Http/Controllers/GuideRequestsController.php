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

        $legacyPending = User::query()
            ->where(function ($q) {
                $q->where('guide_status', GuideStatus::PENDING)
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('guide_status')->where('is_guide', 0);
                    });
            })
            ->whereDoesntHave('guideRequests', fn ($q) => $q->where('decision', 'pending'))
            ->with('information')
            ->get();

        return view('admin.pages.guide-requests.index', compact('pendingRequests', 'legacyPending'));
    }
}
