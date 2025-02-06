<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Http\Controllers\Controller;

class PasswordController extends Controller
{
    public function changePassword(Request $request)
    {
        $request->validate([
            'new_password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
        ]);

        $user = auth()->user();
        $user->password = Hash::make($request->new_password);
        $user->is_temp_password = false;
        $user->save();

        return redirect()->back()->with('success', __('Password updated successfully!'));
    }
} 