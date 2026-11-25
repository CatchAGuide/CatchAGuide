<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Guide\Area\GuideAccountVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin › Guides › Billing verification: review a guide's account type and legal / billing data
 * after a change in Account & security (spec N-34), approve or reject it (N-35).
 */
class GuideBillingVerificationsController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = array_column(AccountVerificationStatus::cases(), 'value');
        $status = $request->query('status', AccountVerificationStatus::Pending->value);
        if (! in_array($status, $statuses, true) && $status !== 'all') {
            $status = AccountVerificationStatus::Pending->value;
        }

        $guides = User::query()
            ->whereNotNull('account_verification_status')
            ->whereNotNull('guide_status')
            ->with('information')
            ->when($status !== 'all', fn ($q) => $q->where('account_verification_status', $status))
            ->latest('updated_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.pages.guide-billing.index', [
            'guides' => $guides,
            'status' => $status,
            'pendingCount' => User::query()->whereNotNull('guide_status')->where('account_verification_status', AccountVerificationStatus::Pending->value)->count(),
        ]);
    }

    public function approve(User $guide, GuideAccountVerificationService $verification): RedirectResponse
    {
        $verification->approve($guide, (int) auth('employees')->id());

        return back()->with('message', __('guide_area.account.admin.approved_flash'));
    }

    public function reject(Request $request, User $guide, GuideAccountVerificationService $verification): RedirectResponse
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $verification->reject($guide, (int) auth('employees')->id(), $data['rejection_reason']);

        return back()->with('message', __('guide_area.account.admin.rejected_flash'));
    }
}
