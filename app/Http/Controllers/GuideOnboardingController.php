<?php

namespace App\Http\Controllers;

use App\Enums\GuideType;
use App\Http\Requests\Guide\GuideVerificationSubmitRequest;
use App\Services\Guide\GuideOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuideOnboardingController extends Controller
{
    public function __construct(
        protected GuideOnboardingService $onboardingService,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        // Logged-in users always use the "old path" (no account-creation step).
        $isFastLane = ! Auth::guard('web')->check();

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user->isVerifiedGuide()) {
                return redirect()->route('profile.index')
                    ->with('message', __('profile.already_verified_guide'));
            }
            if ($user->isPendingGuide()) {
                return redirect()->route('profile.index')
                    ->with('message', __('profile.guide_application_already_pending'));
            }
        }

        $user = Auth::guard('web')->user();

        return view('pages.guide.onboarding', [
            'isFastLane' => $isFastLane,
            'inProfile' => $user !== null,
            'user' => $user,
            'companyEnabled' => config('guide_onboarding.company_onboarding_enabled'),
            'guideTypes' => GuideType::all(),
            'wizardSteps' => $isFastLane
                ? [
                    ['id' => 'account', 'label' => __('profile.onboarding_step_account')],
                    ['id' => 'type', 'label' => __('profile.onboarding_step_type')],
                    ['id' => 'details', 'label' => __('profile.onboarding_step_details')],
                    ['id' => 'legal', 'label' => __('profile.onboarding_step_legal')],
                ]
                : [
                    ['id' => 'type', 'label' => __('profile.onboarding_step_type')],
                    ['id' => 'details', 'label' => __('profile.onboarding_step_details')],
                    ['id' => 'legal', 'label' => __('profile.onboarding_step_legal')],
                ],
        ]);
    }

    public function store(GuideVerificationSubmitRequest $request): RedirectResponse
    {
        $isFastLane = $request->boolean('is_fast_lane') && ! Auth::guard('web')->check();

        if (! $isFastLane && ! Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $webUser = Auth::guard('web')->user();
        if ($webUser?->isVerifiedGuide()) {
            return redirect()->route('profile.index')
                ->with('message', __('profile.already_verified_guide'));
        }
        if ($webUser?->isPendingGuide()) {
            return redirect()->route('profile.index')
                ->with('message', __('profile.guide_application_already_pending'));
        }

        if ($isFastLane && \App\Models\User::where('email', $request->email)->exists()) {
            return back()->withInput()->withErrors([
                'email' => __('profile.guide_email_already_registered'),
            ]);
        }

        $user = $this->onboardingService->submit(
            $request,
            $isFastLane,
            $isFastLane ? null : $webUser
        );

        if ($isFastLane) {
            Auth::login($user);
        }

        return redirect()
            ->route('profile.guide-profile')
            ->with('message', __('profile.guide_application_submitted'));
    }
}
