<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\LoginThrottle;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        if (LoginThrottle::lockedOut($request, 'employees')) {
            $seconds = LoginThrottle::secondsUntilRetry($request, 'employees');

            Log::warning('Employee login attempt blocked by throttle', [
                'guard' => 'employees',
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'retry_after' => $seconds,
            ]);

            return LoginThrottle::response($request, $seconds);
        }

        $this->validator($request);

        if (Auth::guard('employees')->attempt($request->only(['email', 'password']))) {
            LoginThrottle::clear($request, 'employees');
            $request->session()->regenerate();

            return redirect()->intended(route('admin.index'));
        }

        LoginThrottle::recordFailure($request, 'employees');

        Log::warning('Failed employee login attempt', [
            'guard' => 'employees',
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (LoginThrottle::lockedOut($request, 'employees')) {
            return LoginThrottle::response($request, LoginThrottle::secondsUntilRetry($request, 'employees'));
        }

        return redirect()->back()->withInput()->withErrors(['email' => __('auth.failed')]);
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
