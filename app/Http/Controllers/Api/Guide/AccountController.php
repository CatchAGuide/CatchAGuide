<?php

namespace App\Http\Controllers\Api\Guide;

use App\Enums\AccountVerificationStatus;
use App\Http\Controllers\Controller;
use App\Services\Customer\CustomerAccountService;
use App\Services\Guide\Area\GuideAccountService;
use App\Services\Guide\Area\GuideOptimizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

/**
 * /api/guide/account* (spec 13.4) — same rules and service as the Account & security page.
 */
class AccountController extends Controller
{
    public function __construct(private readonly GuideAccountService $account) {}

    public function show(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->payload($request)]);
    }

    public function updatePersonal(Request $request): JsonResponse
    {
        $data = $request->validate($this->account->personalRules());
        $this->account->savePersonal($request->user(), $data);

        return response()->json(['data' => $this->payload($request)]);
    }

    public function updateBilling(Request $request, GuideOptimizationService $optimization): JsonResponse
    {
        $data = $request->validate($this->account->billingRules($request->all()), $this->account->billingMessages());
        $data['vat_not_registered'] = $request->boolean('vat_not_registered');

        $result = $this->account->saveBilling($request->user(), $data);
        $optimization->forget($request->user());

        return response()->json(['data' => $this->payload($request), 'meta' => $result]);
    }

    public function password(Request $request, CustomerAccountService $customerAccount): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password:sanctum'],
            'password' => ['required', 'string', 'different:current_password', 'confirmed', Password::min((int) config('guide_area.customer.password_min', 8))],
        ]);

        $customerAccount->changePassword($request->user(), $data['password']);

        return response()->json(['data' => ['password_changed_at' => $request->user()->password_changed_at?->toIso8601String()]]);
    }

    /** @return array<string, mixed> */
    private function payload(Request $request): array
    {
        $guide = $request->user()->refresh();
        $status = $guide->account_verification_status ?? AccountVerificationStatus::Pending;

        return [
            'email' => $guide->email,
            'personal' => $this->account->personal($guide),
            'billing' => $this->account->billing($guide),
            'verification' => [
                'status' => $status->value,
                'verified_at' => $guide->account_verified_at?->toIso8601String(),
                'rejection_reason' => $status === AccountVerificationStatus::Rejected ? $guide->account_rejection_reason : null,
            ],
            'password_changed_at' => $guide->password_changed_at?->toIso8601String(),
        ];
    }
}
