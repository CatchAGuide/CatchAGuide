<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGuideRequest;
use App\Http\Requests\VerifyStoreGuideRequest;
use App\Mail\CustomerGuidesMail;
use App\Mail\GuideEmail;
use App\Models\User;
use App\Models\UserInformation;
use App\Services\Guide\GuideOnboardingService;
use App\Services\Guide\GuideProfileService;
use App\Services\Guide\GuideVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class GuidesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $guides = User::where('is_guide', true)->whereHas('information', function ($query) {
            return $query->where('request_as_guide', false);
        })->get();

        /*
        foreach ($guides as $guide) {
            $merchants[] = trim($guide->merchant_id);
        }

        $merchants = array_filter($merchants, 'strlen');
        $merchantList = implode(',', $merchants);
        $merchantStatuses = (new OppApiService())->retrieveFilteredMerchants($merchantList);
        $statusArray = array($merchantStatuses);

        foreach ($statusArray[0]->data as $key => $status) {
            $merchantUid = $statusArray[0]->data[$key]->uid;
            $merchantLevel = $statusArray[0]->data[$key]->compliance->level;
            $merchantStatus = $statusArray[0]->data[$key]->compliance->status;
            $merchantUrl = $statusArray[0]->data[$key]->compliance->overview_url;
            $result = User::where('merchant_id', $merchantUid)->update(['merchant_status' => $merchantLevel, 'merchant_compliance_status' => $merchantStatus, 'merchant_verification_url' => $merchantUrl]);
        }
*/
        $guides = User::where('is_guide', true)->whereHas('information', function ($query) {
            return $query->where('request_as_guide', false);
        })->get();


        return view('admin.pages.guides.index', [
            'guides' => $guides
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(VerifyStoreGuideRequest $request)
    {
        if (config('guide_onboarding.new_onboarding_enabled')) {
            return redirect()->route('guide.onboarding', ['fast_lane' => 0]);
        }

        $request->merge([
            'guide_type' => $request->input('guide_type', 'private'),
            'lawcard_nature' => $request->input('lawcard_nature', $request->lawcard),
            'lawcard_truthful' => $request->input('lawcard_truthful', $request->lawcard),
        ]);

        $user = app(GuideOnboardingService::class)->submit($request, false, auth()->user());

        if ($request->has('information.languages') || $request->has('bar_allowed')) {
            app(GuideProfileService::class)->update($user, $request);
        }

        return redirect()
            ->route('profile.guide-profile')
            ->with('message', __('profile.guide_application_submitted'));
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show(User $guide)
    {
        return view('admin.pages.guides.show',[
            'guides' => $guide,
            'users' => User::all()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(User $guide)
    {
        return view ('admin.pages.guides.edit', [
            'guide' => $guide
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateGuideRequest $request, User $guide)
    {
        $guide->tax_id = $request->taxId;
        $guide->update();
        $findGuide = UserInformation::where('id', '=', $guide->user_information_id)->first();
        $findGuide->birthday = $request->information['birthday'];
        $findGuide->address = $request->information['address'];
        $findGuide->address_number = $request->information['address_number'];
        $findGuide->postal = $request->information['postal'];
        $findGuide->city = $request->information['city'];
        $findGuide->phone = $request->information['phone'];
        $findGuide->update();
        return redirect()->route('admin.guides.edit', $findGuide)->with(['message' => 'Erfolgreich gespeichert!']);
    }

    public function changeGuideStatus(User $guide)
    {
        $verificationService = app(GuideVerificationService::class);

        if ($guide->isVerifiedGuide()) {
            app(\App\Services\Guide\GuideStatusService::class)->markPending(
                $guide,
                auth()->id(),
                'Admin deactivated guide status'
            );

            return redirect()->back()->with('message', 'Der Status wurde erfolgreich geändert!');
        }

        try {
            $verificationService->approveUserLegacy($guide, (int) auth()->id());
        } catch (\RuntimeException $e) {
            return redirect()->back()->withErrors(['general' => $e->getMessage()]);
        }

        return redirect()->back()->with('message', 'Der Status wurde erfolgreich geändert!');
    }

    /*
    public function getMerchantStatus (User $guide)
    {
        if ($guide->merchant_status) {
            $status = $guide->merchant_status;
        } else {
            $merchant = (new OppApiService())->retrieveMerchant($guide->merchant_id);
            $guide->update(['is_guide' => !$guide->is_guide, 'merchant_status' => (string)$merchant->compliance->level, 'merchant_compliance_status' => $merchant->compliance->status]);
            $status = $merchant->compliance->level;
        }

        return $status;
    }
    */
}
