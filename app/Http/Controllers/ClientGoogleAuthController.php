<?php

namespace App\Http\Controllers;

use App\Services\ClientAuthService;
use App\Services\GoogleDesktopAuthService;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class ClientGoogleAuthController extends Controller
{
    public function __construct(
        private ClientAuthService $authService,
        private GoogleDesktopAuthService $desktopAuth,
    ) {}

    public function desktopRedirect(Request $request): SymfonyRedirectResponse|View
    {
        if (! $this->googleOAuthConfigured()) {
            return view('auth.google-desktop-complete', [
                'message' => 'Login Google belum dikonfigurasi di server.',
                'deepLink' => null,
            ]);
        }

        $request->session()->put('oauth_flow', 'desktop');

        return $this->googleDriver()
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function redirect(Request $request): SymfonyRedirectResponse
    {
        if (! $this->googleOAuthConfigured()) {
            return redirect()
                ->to($this->safeReturnUrl($request->query('return')))
                ->with('auth_error', 'Login Google belum dikonfigurasi. Hubungi admin.');
        }

        $intentType = (string) $request->query('intent', '');
        $packageId = (int) $request->query('package_id', 0);
        $intent = null;
        if (in_array($intentType, ['checkout', 'trial'], true)) {
            $intent = [
                'type' => $intentType,
                'package_id' => $packageId,
            ];
        }

        $request->session()->put([
            'oauth_flow' => 'web',
            'oauth_return_url' => $this->safeReturnUrl($request->query('return')),
            'oauth_intent' => $intent,
        ]);

        return $this->googleDriver()
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse|View
    {
        $flow = (string) $request->session()->pull('oauth_flow', 'web');
        $returnUrl = $this->safeReturnUrl($request->session()->pull('oauth_return_url'));
        $intent = $request->session()->pull('oauth_intent');

        if (! $this->googleOAuthConfigured()) {
            if ($flow === 'desktop') {
                return view('auth.google-desktop-complete', [
                    'message' => 'Login Google belum dikonfigurasi di server.',
                    'deepLink' => null,
                ]);
            }

            return redirect($returnUrl)->with('auth_error', 'Login Google belum dikonfigurasi.');
        }

        try {
            $googleUser = $this->googleDriver()->user();
        } catch (\Throwable $e) {
            report($e);

            $message = 'Login Google dibatalkan atau gagal. Silakan coba lagi.';
            if (app()->environment('local') && str_contains($e->getMessage(), 'cURL error 60')) {
                $message = 'Login Google gagal (SSL/cURL di Windows). Set GOOGLE_VERIFY_SSL=false di .env lalu php artisan config:clear.';
            }

            if ($flow === 'desktop') {
                return view('auth.google-desktop-complete', [
                    'message' => $message,
                    'deepLink' => null,
                ]);
            }

            return redirect($returnUrl)->with('auth_error', $message);
        }

        $result = $this->authService->loginOrRegisterWithGoogle($googleUser);

        if (($result['status'] ?? '') !== 'success' || ! isset($result['user'])) {
            if ($flow === 'desktop') {
                return view('auth.google-desktop-complete', [
                    'message' => $result['message'] ?? 'Login Google gagal.',
                    'deepLink' => null,
                ]);
            }

            return redirect($returnUrl)->with('auth_error', $result['message'] ?? 'Login Google gagal.');
        }

        if ($flow === 'desktop') {
            $exchangeCode = $this->desktopAuth->issueExchangeCode($result['user']);
            $deepLink = $this->desktopDeepLink($exchangeCode);
            $successMessage = $result['created']
                ? 'Akun berhasil dibuat dengan Google.'
                : 'Login dengan Google berhasil.';

            return view('auth.google-desktop-complete', [
                'message' => $successMessage,
                'deepLink' => $deepLink,
            ]);
        }

        Auth::login($result['user']);
        $request->session()->regenerate();

        if (is_array($intent) && ! empty($intent['type'])) {
            $request->session()->put('landing_oauth_intent', $intent);
        }

        $message = $result['created']
            ? 'Akun berhasil dibuat dengan Google.'
            : 'Login dengan Google berhasil.';

        return redirect($returnUrl)->with([
            'auth_success' => $message,
            'google_auth' => true,
        ]);
    }

    private function googleDriver(): AbstractProvider
    {
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        if (! config('services.google.verify_ssl', true)) {
            $driver->setHttpClient(new GuzzleClient(['verify' => false]));
        }

        return $driver;
    }

    private function googleOAuthConfigured(): bool
    {
        return (string) config('services.google.client_id') !== ''
            && (string) config('services.google.client_secret') !== '';
    }

    private function safeReturnUrl(mixed $return): string
    {
        $fallback = route('landing');
        if (! is_string($return) || trim($return) === '') {
            return $fallback;
        }

        $return = trim($return);
        if (! str_starts_with($return, '/')) {
            return $fallback;
        }

        if (str_starts_with($return, '//')) {
            return $fallback;
        }

        return $return;
    }

    private function desktopDeepLink(string $exchangeCode): string
    {
        $scheme = (string) config('spotcolor.desktop_oauth_scheme', 'spotcolor');

        return $scheme.'://auth/callback?code='.urlencode($exchangeCode);
    }
}
