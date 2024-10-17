<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function index()
    {
        return view('admin.auth.login');
    }
    /**
     * @param Request $request
     * @return RedirectResponse|void
     */
    public function login(Request $request)
    {
        $this->validator($request);

        if(Auth::guard('employees')->attempt($request->only(['email', 'password']))) {
            return redirect()->intended(route('admin.index'));
        }

        return redirect()->back()->withInput()->with('error', 'Login failed. Please try again.');
    }

    /**
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        Auth::guard('employees')->logout();

        return redirect()->route('admin.auth.logins'); // Login
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
        return redirect()->back()->withInput()->with('error', 'Login failed. Please try again.');
    }
}
