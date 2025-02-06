<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\JsonResponse;
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
        $credentials = $request->only(['email', 'password']);
        $remember = $request->filled('remember');

        if(Auth::attempt($credentials, $remember)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('profile.index')
                ]);
            }
            return redirect()->intended(route('profile.index'));
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'email' => [__('auth.failed')]
                ]
            ], 422);
        }

        return $this->loginFailed()->withError('Invalid Username and Password');
    }

    /**
     * @return JsonResponse|RedirectResponse
     */
    public function logout()
    {
        Auth::guard('employees')->logout();
        Auth::logout();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('login');
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
