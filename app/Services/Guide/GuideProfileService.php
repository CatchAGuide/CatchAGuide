<?php

namespace App\Services\Guide;

use App\Models\User;
use Illuminate\Http\Request;

class GuideProfileService
{
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

        $user->save();

        $information = $request->input('information', []);
        if ($user->information) {
            $user->information->update($information);
        } else {
            $user->information()->create($information);
        }

        return $user->fresh(['information']);
    }
}
