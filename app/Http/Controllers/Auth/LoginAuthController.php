<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\LoginThrottle;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        if (LoginThrottle::lockedOut($request)) {
            $seconds = LoginThrottle::secondsUntilRetry($request);

            Log::warning('Login attempt blocked by throttle', [
                'guard' => 'web',
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'retry_after' => $seconds,
            ]);

            return LoginThrottle::response($request, $seconds);
        }

        $credentials = $request->only(['email', 'password']);
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            LoginThrottle::clear($request);
            $request->session()->regenerate();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('profile.index')
                ]);
            }
            return redirect()->intended(route('profile.index'));
        }

        LoginThrottle::recordFailure($request);

        Log::warning('Failed user login attempt', [
            'guard' => 'web',
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if (LoginThrottle::lockedOut($request)) {
            return LoginThrottle::response($request, LoginThrottle::secondsUntilRetry($request));
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'email' => [__('auth.failed')]
                ]
            ], 422);
        }

        return $this->loginFailed()->withErrors(['email' => __('auth.failed')]);
    }

    /**
     * @return JsonResponse|RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('employees')->logout();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        // Stay on current page: redirect back so the page just refreshes with session cleared
        return redirect()->back();
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
    private function loginFailed(): RedirectResponse
    {
        return redirect()->back()->withInput();
    }
}
