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
    /**
     * Entry point for /login — never render a full page; open the login modal
     * on a public page that already includes #loginModal.
     */
    public function index(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            $previous = url()->previous();
            if ($previous && $this->isSameOrigin($previous, route('welcome')) && ! $this->urlsMatch($previous, route('login'))) {
                return redirect()->to($previous);
            }

            return redirect()->route('welcome');
        }

        $request->session()->reflash();

        return redirect()
            ->to($this->resolveLoginModalLanding($request))
            ->with('show_login_modal', true);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|JsonResponse
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
            // Prevent session fixation; drop any stale "intended" profile deep-link.
            $request->session()->regenerate();
            $request->session()->forget('url.intended');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    // Stay on the page where the modal was opened.
                    'redirect' => null,
                ]);
            }

            return redirect()->back();
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

        if ($request->ajax() || $request->wantsJson()) {
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
     * Fully clear auth state: both guards, session data, CSRF token, remember cookie.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('employees')->logout();
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirect = $this->resolveLogoutLanding($request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => $redirect,
            ]);
        }

        return redirect()
            ->to($redirect)
            ->with('show_login_modal', true);
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

    /**
     * Prefer the page the user came from so /login keeps them in context
     * (e.g. /guidings → /guidings?login=1). Fall back to home when there is
     * no usable same-origin referer, or when coming from an auth-only URL.
     */
    private function resolveLoginModalLanding(Request $request): string
    {
        $home = route('welcome');
        $loginUrl = route('login');
        $previous = url()->previous();
        $intended = $request->session()->get('url.intended');

        if (! $previous || $this->urlsMatch($previous, $loginUrl)) {
            return $this->appendLoginQuery($home);
        }

        // Auth middleware guest redirect: previous === intended protected URL — avoid loops.
        if ($intended && $this->urlsMatch($previous, $intended)) {
            return $this->appendLoginQuery($home);
        }

        if (! $this->isSameOrigin($previous, $home)) {
            return $this->appendLoginQuery($home);
        }

        $path = parse_url($previous, PHP_URL_PATH) ?: '/';
        if (str_starts_with($path, '/profile')) {
            return $this->appendLoginQuery($home);
        }

        return $this->appendLoginQuery($previous);
    }

    /**
     * After logout, stay on the current public page when possible; otherwise home.
     */
    private function resolveLogoutLanding(Request $request): string
    {
        $home = route('welcome');
        $referer = $request->headers->get('referer') ?: url()->previous();

        if (! $referer || ! $this->isSameOrigin($referer, $home)) {
            return $this->appendLoginQuery($home);
        }

        // Profile and other auth-only areas would immediately re-trigger guest redirect.
        $path = parse_url($referer, PHP_URL_PATH) ?: '/';
        if (str_starts_with($path, '/profile') || str_starts_with($path, '/login')) {
            return $this->appendLoginQuery($home);
        }

        return $this->appendLoginQuery($referer);
    }

    private function appendLoginQuery(string $url): string
    {
        $parts = parse_url($url);
        $query = [];

        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query['login'] = '1';

        $scheme = $parts['scheme'] ?? parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';
        $host = $parts['host'] ?? parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '/';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . '://' . $host . $port . $path . '?' . http_build_query($query) . $fragment;
    }

    private function urlsMatch(string $a, string $b): bool
    {
        $normalize = static function (string $url): string {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';

            return rtrim($path, '/') ?: '/';
        };

        return $normalize($a) === $normalize($b);
    }

    private function isSameOrigin(string $url, string $reference): bool
    {
        $urlHost = parse_url($url, PHP_URL_HOST);
        $refHost = parse_url($reference, PHP_URL_HOST);

        return $urlHost && $refHost && strcasecmp($urlHost, $refHost) === 0;
    }
}
