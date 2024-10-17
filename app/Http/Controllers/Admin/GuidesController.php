<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGuideRequest;
use App\Http\Requests\VerifyStoreGuideRequest;
use App\Mail\CustomerGuidesMail;
use App\Mail\GuideEmail;
use App\Models\User;
use App\Models\UserInformation;
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
        $user = auth()->user();
        $user->bar_allowed = $request->bar_allowed;
        $user->banktransfer_allowed = $request->banktransfer_allowed;
        $user->paypal_allowed = $request->paypal_allowed;
        $user->banktransferdetails = $request->banktransferdetails;
        $user->paypaldetails = $request->paypaldetails;
        $user->update();

        if($user->information()) {
            $user->information->update($request['information']);
        } else {
            $user->information->create($request['information']);
        }

        $fullname = $request->firstname . ' ' . $request->lastname;

        Mail::send(new CustomerGuidesMail($fullname, $request->email));
        Mail::send(new GuideEmail(
            $request->firstname,
            $request->lastname,
            $request->information['birthday'],
            $request->information['address'],
            $request->information['address_number'],
            $request->information['postal'],
            $request->information['city'],
            $request->information['phone'],
            $request->information['languages'],
            $request->information['about_me'],
            $request->information['favorite_fish'],
            $request->information['fishing_start_year'],
            $request->information['taxId']
        ));

        $user = User::find(auth()->user()->id);
        $user->is_guide = "0";
        $user->phone = $request->information['phone'];
        $user->tax_id = $request->information['taxId'];
        $user->save();
        return redirect()->route('profile.index')->with('message', 'Danke für Deine Anfrage. Wir melden uns innerhalb von 24 Stunden bei Dir');
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
        if ($guide->is_guide) {
            $guide->update(['is_guide' => 0]);
            return redirect()->back()->with('message', 'Der Status wurde erfolgreich geändert!');
        }


        if (!$guide->is_guide) {
            $guide->update(['is_guide' => 1]);
            return redirect()->back()->with('message', 'Der Status wurde erfolgreich geändert!');
        }

        /*
        $appUrl = env('APP_URL');
        $bankAcountPostFields = [
            'notify_url' => $appUrl,
            'return_url' => $appUrl
        ];
        if ($guide->merchant_id) {
            $guide->information()->update(['request_as_guide' => false]);
            if ($guide->merchant_status) {
                $merchantStatus = $guide->merchant_status;
                $guide->update(['is_guide' => !$guide->is_guide]);
            } else {
                $merchantStatus = $this->getMerchantStatus($guide);
            }
            if ((int)$merchantStatus < 200) {
                $bankAccount = (new OppApiService())->createEmptyBankAccountForMerchant($guide->merchant_id, $bankAcountPostFields);
                $guide->update(['merchant_bank' => $bankAccount->uid, 'merchant_verification_url' => $bankAccount->verification_url]);
            }
            return redirect()->back()->with('message', 'Der Status wurde erfolgreich geändert!');
        } else {
            $userInfo = UserInformation::find($guide->user_information_id);
            $fullAddress = $userInfo->address . ' ' . $userInfo->address_number;
            $country = 'deu';

            $postFields = [
                'type' => 'consumer',
                'country' => $country,
                'emailaddress' => $guide->email,
                'notify_url' => $appUrl,
                'return_url' => $appUrl,
                'phone' => $userInfo->phone,
                'addresses[0][address_line_1]' => $fullAddress,
                'addresses[0][zipcode]' => $userInfo->postal,
                'addresses[0][city]' => $userInfo->city,
                'addresses[0][country]' => $country
            ];

            $merchant = (new OppApiService())->createMerchant($postFields);

            if ($merchant) {
                $guide->information()->update(['request_as_guide' => false]);
                $bankAccount = (new OppApiService())->createEmptyBankAccountForMerchant($merchant->uid, $bankAcountPostFields);
                $guide->update(['is_guide' => !$guide->is_guide, 'merchant_id' => $merchant->uid, 'merchant_status' => $merchant->compliance->level, 'merchant_compliance_status' => $merchant->compliance->status, 'merchant_bank' => $bankAccount->uid, 'merchant_verification_url' => $bankAccount->verification_url]);
                return redirect()->back()->with('message', 'Der Status wurde erfolgreich geändert!');
            }
        }
        return redirect()->back()->with('message', 'Fehler beim Anlegen eines neuen Merchants in OPP!');
             */
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
