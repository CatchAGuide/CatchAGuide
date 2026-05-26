<?php

namespace App\Services\Guide;

use App\Enums\GuideType;
use App\Mail\Guide\GuideAdminNewRequestMail;
use App\Mail\Guide\GuideApplicationReceivedMail;
use Illuminate\Mail\Mailable;
use App\Models\GuideRequest;
use App\Models\User;
use App\Models\UserInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GuideOnboardingService
{
    public function __construct(
        protected GuideStatusService $guideStatusService,
    ) {}

    public function submit(Request $request, bool $isFastLane, ?User $existingUser = null): User
    {
        return DB::transaction(function () use ($request, $isFastLane, $existingUser) {
            $user = $existingUser ?? $this->createUserFromFastLane($request);

            $this->persistVerificationData($user, $request);

            $user->guide_type = $request->input('guide_type', GuideType::PRIVATE);
            $user->save();

            $this->guideStatusService->markPending($user, $user->id, 'Verification wizard submitted');

            GuideRequest::create([
                'user_id' => $user->id,
                'submitted_at' => now(),
                'decision' => 'pending',
            ]);

            if ($user->information) {
                $user->information->update(['request_as_guide' => true]);
            }

            $this->sendSubmissionEmails($user, $request);

            return $user->fresh(['information']);
        });
    }

    protected function createUserFromFastLane(Request $request): User
    {
        $userInformation = UserInformation::create([
            'request_as_guide' => true,
            'country' => $request->input('information.country', 'DE'),
        ]);

        return User::create([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'language' => $request->input('language', app()->getLocale()),
            'user_information_id' => $userInformation->id,
            'is_guide' => 0,
        ]);
    }

    protected function persistVerificationData(User $user, Request $request): void
    {
        if ($request->filled('firstname')) {
            $user->firstname = $request->input('firstname');
        }
        if ($request->filled('lastname')) {
            $user->lastname = $request->input('lastname');
        }
        $user->phone = $request->input('information.phone', $user->phone);
        $user->phone_country_code = $request->input('phone_country_code', $user->phone_country_code);
        $user->tax_id = $request->input('information.taxId', $request->input('taxId', $user->tax_id));
        $user->save();

        $infoData = array_filter($request->input('information', []), fn ($v) => $v !== null && $v !== '');

        if ($user->information) {
            $user->information->update($infoData);
        } else {
            $info = UserInformation::create(array_merge($infoData, ['request_as_guide' => true]));
            $user->user_information_id = $info->id;
            $user->save();
        }
    }

    protected function sendSubmissionEmails(User $user, Request $request): void
    {
        $user->loadMissing('information');

        $this->sendMailSafely(
            new GuideApplicationReceivedMail($user),
            'guide_application_received',
            $user->email
        );

        $this->sendMailSafely(
            new GuideAdminNewRequestMail($user),
            'guide_admin_new_request',
            config('guide_onboarding.admin_notification_email')
        );
    }

    protected function sendMailSafely(Mailable $mailable, string $label, ?string $recipient = null): void
    {
        try {
            Mail::send($mailable);
        } catch (\Throwable $e) {
            Log::error("Guide onboarding email [{$label}] failed", [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
