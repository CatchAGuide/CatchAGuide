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

    public function store(GuideVerificationSubmitRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $isFastLane = $request->boolean('is_fast_lane') && ! Auth::guard('web')->check();
        $wantsJson = $request->ajax() || $request->wantsJson();
        $loggedInExisting = false;

        if (! $isFastLane && ! Auth::guard('web')->check()) {
            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'redirect' => route('login'),
                ], 401);
            }
            return redirect()->route('login');
        }

        // Fast-lane: if an account already exists for this email, try to log
        // the user in with the password they provided instead of creating a
        // duplicate. Wrong-password attempts return a validation-style error.
        if ($isFastLane) {
            $existingUser = \App\Models\User::where('email', $request->input('email'))->first();
            if ($existingUser) {
                $credentials = [
                    'email' => $request->input('email'),
                    'password' => $request->input('password'),
                ];

                if (Auth::guard('web')->attempt($credentials)) {
                    $request->session()->regenerate();
                    $isFastLane = false;
                    $loggedInExisting = true;
                } else {
                    if ($wantsJson) {
                        return response()->json([
                            'success' => false,
                            'errors' => [
                                'password' => [__('profile.guide_existing_account_wrong_password')],
                            ],
                        ], 422);
                    }
                    return back()->withInput()->withErrors([
                        'password' => __('profile.guide_existing_account_wrong_password'),
                    ]);
                }
            }
        }

        $webUser = Auth::guard('web')->user();
        if ($webUser?->isVerifiedGuide()) {
            $message = __('profile.already_verified_guide');
            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('profile.index'),
                ]);
            }
            return redirect()->route('profile.index')->with('message', $message);
        }
        if ($webUser?->isPendingGuide()) {
            $message = __('profile.guide_application_already_pending');
            if ($wantsJson) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('profile.index'),
                ]);
            }
            return redirect()->route('profile.index')->with('message', $message);
        }

        $user = $this->onboardingService->submit(
            $request,
            $isFastLane,
            $isFastLane ? null : $webUser
        );

        if ($isFastLane) {
            Auth::login($user);
        }

        $successMessage = $loggedInExisting
            ? __('profile.guide_logged_in_existing_account')
            : __('profile.guide_application_submitted');

        if ($wantsJson) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'redirect' => route('profile.guide-profile'),
            ]);
        }

        return redirect()
            ->route('profile.guide-profile')
            ->with('message', $successMessage);
    }
}
