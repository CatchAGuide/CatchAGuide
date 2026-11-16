<?php

namespace App\Services\Guide;

use App\Models\User;
use App\Models\UserInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GuideProfileService
{
    /** user_information columns a guide may edit on the guide profile form. */
    public const EDITABLE_INFORMATION_FIELDS = [
        'languages',
        'about_me',
        'favorite_fish',
        'fishing_start_year',
    ];

    public function update(User $user, Request $request): User
    {
        if (! $user->canViewGuideTools()) {
            throw new \RuntimeException('User cannot access guide profile.');
        }

        $user->bar_allowed = $request->boolean('bar_allowed');
        $user->banktransfer_allowed = $request->boolean('banktransfer_allowed');
        $user->paypal_allowed = $request->boolean('paypal_allowed');
        $user->banktransferdetails = $request->input('banktransferdetails');
        $user->paypaldetails = $request->input('paypaldetails');

        if ($request->hasFile('profil_image')) {
            $image = $request->file('profil_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images'), $imageName);
            $user->profil_image = $imageName;
        }

        // Allowlist: UserInformation's $fillable also covers request_as_guide, contact and the
        // company/verification fields, which must never be writable from this form.
        $information = Arr::only((array) $request->input('information', []), self::EDITABLE_INFORMATION_FIELDS);
        if ($user->information) {
            $user->information->update($information);
        } else {
            // information() is a belongsTo (users.user_information_id): create the row, then link it.
            $user->information()->associate(UserInformation::create($information));
        }

        $user->save();

        return $user->fresh(['information']);
    }
}
