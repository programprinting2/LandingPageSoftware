<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiLoginRequest;
use App\Http\Requests\StoreApiRegistrationRequest;
use App\Http\Requests\StoreGoogleDesktopExchangeRequest;
use App\Models\User;
use App\Services\ClientAuthService;
use App\Services\ClientCommerceService;
use App\Services\GoogleDesktopAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiAuthController extends Controller
{
    public function __construct(
        private ClientAuthService $authService,
        private GoogleDesktopAuthService $desktopAuth,
        private ClientCommerceService $commerceService,
    ) {}

    public function register(StoreApiRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = strtolower(trim($validated['email']));
        $name = trim($validated['name'] ?? '') ?: strstr($email, '@', true) ?: $email;

        $result = $this->authService->registerClient([
            'name' => $name,
            'email' => $email,
            'password' => $validated['password'],
            'company_name' => trim($validated['company_name'] ?? '') ?: $name,
            'whatsapp' => trim($validated['whatsapp'] ?? '') ?: '',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Periksa email Anda untuk link konfirmasi sebelum login.',
            'requires_verification' => true,
            'email' => $result['user']->email,
        ], 201);
    }

    public function exchangeGoogleDesktop(StoreGoogleDesktopExchangeRequest $request): JsonResponse
    {
        $code = (string) $request->validated('code');
        $user = $this->desktopAuth->consumeExchangeCode($code);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode login tidak valid atau sudah kadaluarsa. Coba masuk dengan Google lagi.',
            ], 401);
        }

        $user->loadMissing('client');

        if ($user->role !== 'client' || ! $user->client) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun client tidak ditemukan.',
            ], 403);
        }

        if (! $this->authService->isClientActive($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun dinonaktifkan. Hubungi admin.',
                'account_inactive' => true,
            ], 403);
        }

        return response()->json(
            $this->desktopAuthSessionPayload(
                $user,
                'Login dengan Google berhasil.',
                $request->validated('application_slug'),
            )
        );
    }

    public function login(StoreApiLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = strtolower(trim($validated['email']));
        $user = $this->authService->attemptClientLogin($email, $validated['password']);

        if (! $user) {
            $unverified = $this->authService->findUnverifiedClientByCredentials($email, $validated['password']);

            if ($unverified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email belum diverifikasi. Periksa inbox atau kirim ulang link konfirmasi.',
                    'requires_verification' => true,
                    'email' => $unverified->email,
                ], 403);
            }

            $inactive = $this->authService->findInactiveClientByCredentials($email, $validated['password']);

            if ($inactive) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun dinonaktifkan. Hubungi admin.',
                    'account_inactive' => true,
                ], 403);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah.',
            ], 401);
        }

        return response()->json(
            $this->desktopAuthSessionPayload(
                $user,
                'Login berhasil',
                $request->validated('application_slug'),
            )
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->attributes->get('apiToken');

        if (is_string($token)) {
            $this->authService->revokeApiToken($token);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->attributes->get('apiUser');

        return response()->json([
            'status' => 'success',
            'logged_in' => true,
            'email' => $user->email,
            'name' => $user->name,
            'created_at' => $user->created_at?->toIso8601String(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function desktopAuthSessionPayload(User $user, string $message, ?string $applicationSlug = null): array
    {
        $trial = $this->commerceService->attemptAutoTrialOnDesktopLogin($user, $applicationSlug);
        $payload = $this->authService->apiSessionPayload($user, $message);

        if ($trial['activated'] ?? false) {
            $payload['trial_activated'] = true;
            $payload['message'] = trim($message.' '.($trial['message'] ?? 'Trial berhasil diaktifkan.'));
        }

        return $payload;
    }
}
