<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginAuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }
    /**
     * @param Request $request
     * @return RedirectResponse|void
     */
    public function login(Request $request)
    {
        if(Auth::attempt($request->only(['email', 'password']))) {
            return redirect()->intended(route('profile.index'));
        }

        return $this->loginFailed()->withError('Invalid Username and Password');
    }

    /**
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        Auth::guard('employees')->logout();
        Auth::logout();

        return redirect()->route('login'); // Login
    }

    /**
     * @param Request $request
     */
    public function validator(Request $request): void
    {
        $rules = [
            'email'    => 'required|email|exists:employees|min:5|max:191',
            'password' => 'required|string|min:4|max:255',
        ];

        $request->validate($rules);
    }

    /**
     * @return RedirectResponse
     */
    public function loginFailed(): RedirectResponse
    {
        return redirect()->back()->withInput();
    }
}
